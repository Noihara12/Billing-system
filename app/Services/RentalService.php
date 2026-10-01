<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalSession;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalService
{
    public function __construct(
        private TasmotaService $tasmotaService,
        private BookingService $bookingService,
    ) {}

    public function startFromBooking(Booking $booking, User $admin): RentalSession
    {
        if (! in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Booking harus berstatus PENDING atau CONFIRMED untuk memulai rental.',
            ]);
        }

        if ($booking->rentalSession()->exists()) {
            throw ValidationException::withMessages([
                'booking' => 'Booking ini sudah memiliki sesi rental.',
            ]);
        }

        $this->assertUnitFree($booking->unit);

        $start = now();
        $end = $start->copy()->addMinutes($booking->duration_minutes);

        return DB::transaction(function () use ($booking, $admin, $start, $end) {
            $session = RentalSession::create([
                'booking_id' => $booking->id,
                'unit_id' => $booking->unit_id,
                'user_id' => $booking->user_id,
                'admin_id' => $admin->id,
                'price_list_id' => $booking->price_list_id,
                'customer_name' => $booking->customer_name,
                'whatsapp_number' => $booking->whatsapp_number,
                'rental_start_time' => $start,
                'rental_end_time' => $end,
                'duration_minutes' => $booking->duration_minutes,
                'total_price' => $booking->total_price,
                'status' => RentalSession::STATUS_ACTIVE,
            ]);

            if ($booking->status === Booking::STATUS_PENDING) {
                $booking->update(['status' => Booking::STATUS_CONFIRMED]);
            }

            $booking->unit->update(['status' => Unit::STATUS_PLAYING]);

            // Auto Power ON jika dikonfigurasi
            $this->triggerPowerOn($session);

            return $session;
        });
    }

    /**
     * @param  array{unit_id: int, price_list_id: int, user_id?: ?int, customer_name: string, whatsapp_number?: ?string}  $data
     */
    public function startWalkIn(array $data, User $admin): RentalSession
    {
        $unit = Unit::findOrFail($data['unit_id']);
        $priceList = $unit->findActivePriceList((int) $data['price_list_id']);

        if (! $priceList) {
            throw ValidationException::withMessages([
                'price_list_id' => 'Durasi & harga yang dipilih tidak berlaku untuk kategori unit ini.',
            ]);
        }

        $this->assertUnitFree($unit);
        $this->assertWalkInFitsBeforeNextBooking($unit, $priceList->duration_minutes);

        $start = now();
        $end = $start->copy()->addMinutes($priceList->duration_minutes);

        return DB::transaction(function () use ($data, $admin, $unit, $priceList, $start, $end) {
            $session = RentalSession::create([
                'unit_id' => $unit->id,
                'user_id' => $data['user_id'] ?? null,
                'admin_id' => $admin->id,
                'price_list_id' => $priceList->id,
                'customer_name' => $data['customer_name'],
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                'rental_start_time' => $start,
                'rental_end_time' => $end,
                'duration_minutes' => $priceList->duration_minutes,
                'total_price' => $priceList->price,
                'status' => RentalSession::STATUS_ACTIVE,
            ]);

            $unit->update(['status' => Unit::STATUS_PLAYING]);

            // Auto Power ON jika dikonfigurasi
            $this->triggerPowerOn($session);

            return $session;
        });
    }

    public function complete(RentalSession $session): void
    {
        $this->assertActive($session);

        $session->update([
            'status' => RentalSession::STATUS_COMPLETED,
            'actual_end_time' => now(),
        ]);

        $this->releaseUnit($session->unit);

        if ($session->booking && ! in_array($session->booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED], true)) {
            $session->booking->update(['status' => Booking::STATUS_COMPLETED]);
        }

        // Auto Power OFF jika dikonfigurasi
        $this->triggerPowerOff($session);
    }

    public function cancel(RentalSession $session): void
    {
        $this->assertActive($session);

        $session->update([
            'status' => RentalSession::STATUS_CANCELLED,
            'actual_end_time' => now(),
        ]);

        $this->releaseUnit($session->unit);

        // Auto Power OFF jika dikonfigurasi
        $this->triggerPowerOff($session);
    }

    /**
     * Complete any rental sessions whose time has already run out. Called opportunistically
     * from dashboard/rental endpoints and via the scheduled `rentals:complete-expired` command,
     * so units are freed up even without a page open. Also refreshes BOOKED/AVAILABLE unit status
     * based on how close the next booking is.
     */
    public function completeExpired(): int
    {
        $expired = RentalSession::query()
            ->with(['unit.tasmotaDevice'])
            ->where('status', RentalSession::STATUS_ACTIVE)
            ->where('rental_end_time', '<=', now())
            ->get();

        foreach ($expired as $session) {
            $this->complete($session);
        }

        $this->bookingService->syncBookedUnits();

        return $expired->count();
    }

    /**
     * Kirim Power ON ke Tasmota jika unit dikonfigurasi auto_power_on.
     */
    private function triggerPowerOn(RentalSession $session): void
    {
        $session->loadMissing('unit.tasmotaDevice');
        $unit = $session->unit;

        if (! $unit->auto_power_on || ! $unit->tasmotaDevice) {
            return;
        }

        $success = $this->tasmotaService->turnOn($unit->tasmotaDevice);

        // Catat apakah perintah berhasil dikirim
        $session->update(['power_on_sent' => $success]);
    }

    /**
     * Kirim Power OFF ke Tasmota jika unit dikonfigurasi auto_power_off.
     */
    private function triggerPowerOff(RentalSession $session): void
    {
        $session->loadMissing('unit.tasmotaDevice');
        $unit = $session->unit;

        if (! $unit->auto_power_off || ! $unit->tasmotaDevice) {
            return;
        }

        $success = $this->tasmotaService->turnOff($unit->tasmotaDevice);

        // Catat apakah perintah berhasil dikirim
        $session->update(['power_off_sent' => $success]);
    }

    private function assertUnitFree(Unit $unit): void
    {
        if ($unit->activeRentalSession()->exists()) {
            throw ValidationException::withMessages([
                'unit_id' => 'Unit ini sedang digunakan (rental aktif).',
            ]);
        }
    }

    private function assertWalkInFitsBeforeNextBooking(Unit $unit, int $durationMinutes): void
    {
        $nextBooking = $this->bookingService->nextBooking($unit->id);
        $availableMinutes = $this->bookingService->walkInMinutesAvailable($nextBooking);

        if ($availableMinutes === null || $durationMinutes <= $availableMinutes) {
            return;
        }

        $message = $availableMinutes > 0
            ? "Unit ini ada booking {$nextBooking->booking_code} jam {$nextBooking->start_time->format('H:i')}. Durasi walk-in maksimal {$availableMinutes} menit."
            : "Unit ini ada booking {$nextBooking->booking_code} jam {$nextBooking->start_time->format('H:i')}, tidak bisa dipakai walk-in sekarang.";

        throw ValidationException::withMessages(['price_list_id' => $message]);
    }

    private function assertActive(RentalSession $session): void
    {
        if ($session->status !== RentalSession::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => 'Sesi rental ini sudah tidak aktif.',
            ]);
        }
    }

    private function releaseUnit(Unit $unit): void
    {
        $unit->update(['status' => Unit::STATUS_AVAILABLE]);

        $this->bookingService->syncBookedUnits($unit->id);
    }
}
