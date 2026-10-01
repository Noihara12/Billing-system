<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalSession;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public const SLOT_MINUTES = 30;

    /**
     * Jeda persiapan: unit tampil BOOKED dan walk-in harus selesai sekian menit sebelum booking dimulai.
     */
    public const BOOKED_LEAD_MINUTES = 15;

    /**
     * Bentrok dengan booking PENDING/CONFIRMED atau rental yang sedang berjalan di unit yang sama.
     */
    public function hasConflict(int $unitId, Carbon $start, Carbon $end, ?int $ignoreBookingId = null): bool
    {
        $bookingConflict = Booking::query()
            ->where('unit_id', $unitId)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        return $bookingConflict || RentalSession::query()
            ->where('unit_id', $unitId)
            ->where('status', RentalSession::STATUS_ACTIVE)
            ->when($ignoreBookingId, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('booking_id')->orWhere('booking_id', '!=', $ignoreBookingId)))
            ->where('rental_start_time', '<', $end)
            ->where('rental_end_time', '>', $start)
            ->exists();
    }

    /**
     * Jam buka & tutup untuk tanggal tertentu. Jam tutup "24:00" (atau lebih awal dari jam buka) berarti hari berikutnya.
     *
     * @return array{open: Carbon, close: Carbon}
     */
    public function operatingHours(Carbon $date): array
    {
        $day = $date->copy()->startOfDay();
        $open = $day->copy()->setTimeFromTimeString((string) Setting::get('booking_open_time', '09:00'));
        $closeSetting = (string) Setting::get('booking_close_time', '24:00');
        $close = $closeSetting === '24:00'
            ? $day->copy()->addDay()
            : $day->copy()->setTimeFromTimeString($closeSetting);

        if ($close->lte($open)) {
            $close->addDay();
        }

        return ['open' => $open, 'close' => $close];
    }

    /**
     * Gabungkan tanggal booking & jam mulai. Jam setelah tengah malam pada jam operasional yang
     * melewati hari (mis. 00:30 saat tutup 02:00) dianggap milik hari berikutnya.
     */
    public function resolveStartTime(string $date, string $time): Carbon
    {
        $start = Carbon::parse("{$date} {$time}");
        ['open' => $open, 'close' => $close] = $this->operatingHours($start);

        if ($start->lt($open) && $start->copy()->addDay()->lt($close)) {
            return $start->addDay();
        }

        return $start;
    }

    /**
     * Ringkasan jam operasional untuk ditampilkan, mis. 09:00–24:00 (15 jam).
     *
     * @return array{open: string, close: string, minutes: int, overnight: bool}
     */
    public function describeOperatingHours(): array
    {
        ['open' => $open, 'close' => $close] = $this->operatingHours(today());

        return [
            'open' => $open->format('H:i'),
            'close' => $this->formatClock($close, $open),
            'minutes' => (int) $open->diffInMinutes($close),
            'overnight' => ! $close->isSameDay($open) && $this->formatClock($close, $open) !== '24:00',
        ];
    }

    /**
     * Pesan error bila rentang waktu tidak bisa dibooking, atau null bila tersedia.
     */
    public function slotUnavailableReason(int $unitId, Carbon $start, Carbon $end): ?string
    {
        $hours = $this->operatingHours($start);

        // Jam dini hari bisa milik jam operasional hari sebelumnya (mis. buka 10:00 tutup 02:00).
        if ($start->lt($hours['open'])) {
            $hours = $this->operatingHours($start->copy()->subDay());
        }

        ['open' => $open, 'close' => $close] = $hours;

        if ($start->lt(now())) {
            return 'Jam mulai sudah lewat. Silakan pilih jam lain.';
        }

        if ($start->lt($open) || $end->gt($close)) {
            return "Booking hanya bisa pada jam operasional {$open->format('H:i')}–{$this->formatClock($close, $open)}.";
        }

        if ($this->hasConflict($unitId, $start, $end)) {
            return 'Unit sudah dibooking pada rentang waktu tersebut. Silakan pilih waktu lain.';
        }

        return null;
    }

    /**
     * Slot per 30 menit untuk satu unit & tanggal. Slot "insufficient" berarti jamnya kosong
     * tetapi durasi yang dipilih akan bentrok dengan jadwal berikutnya atau melewati jam tutup.
     *
     * @return array{open: string, close: string, duration_minutes: int, slots: list<array{time: string, status: string}>, busy: list<array{start: string, end: string}>}
     */
    public function availability(Unit $unit, Carbon $date, int $durationMinutes): array
    {
        ['open' => $open, 'close' => $close] = $this->operatingHours($date);
        $busy = $this->busyRanges($unit->id, $open, $close);
        $overlapsBusy = fn (Carbon $from, Carbon $to) => $busy->contains(fn ($range) => $range['start']->lt($to) && $range['end']->gt($from));

        $slots = [];

        for ($time = $open->copy(); $time->lt($close); $time->addMinutes(self::SLOT_MINUTES)) {
            $slotEnd = $time->copy()->addMinutes(self::SLOT_MINUTES);
            $bookingEnd = $time->copy()->addMinutes($durationMinutes);

            $status = match (true) {
                $time->lt(now()) => 'past',
                $overlapsBusy($time, $slotEnd) => 'booked',
                $bookingEnd->gt($close) || $overlapsBusy($time, $bookingEnd) => 'insufficient',
                default => 'available',
            };

            $slots[] = ['time' => $time->format('H:i'), 'status' => $status];
        }

        return [
            'open' => $open->format('H:i'),
            'close' => $this->formatClock($close, $open),
            'duration_minutes' => $durationMinutes,
            'slots' => $slots,
            'busy' => $busy->map(fn ($range) => [
                'start' => $range['start']->max($open)->format('H:i'),
                'end' => $this->formatClock($range['end']->min($close), $open),
            ])->values()->all(),
        ];
    }

    /**
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    private function busyRanges(int $unitId, Carbon $from, Carbon $to): Collection
    {
        $bookings = Booking::query()
            ->where('unit_id', $unitId)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->where('start_time', '<', $to)
            ->where('end_time', '>', $from)
            ->get(['start_time', 'end_time'])
            ->map(fn (Booking $booking) => ['start' => $booking->start_time, 'end' => $booking->end_time]);

        $rentals = RentalSession::query()
            ->where('unit_id', $unitId)
            ->where('status', RentalSession::STATUS_ACTIVE)
            ->where('rental_start_time', '<', $to)
            ->where('rental_end_time', '>', $from)
            ->get(['rental_start_time', 'rental_end_time'])
            ->map(fn (RentalSession $rental) => ['start' => $rental->rental_start_time, 'end' => $rental->rental_end_time]);

        return $bookings->toBase()->merge($rentals)->sortBy(fn ($range) => $range['start'])->values();
    }

    /**
     * Tampilkan tengah malam hari berikutnya sebagai "24:00".
     */
    private function formatClock(Carbon $time, Carbon $dayReference): string
    {
        return $time->eq($dayReference->copy()->startOfDay()->addDay()) ? '24:00' : $time->format('H:i');
    }

    /**
     * @param  array{unit_id: int, price_list_id: int, booking_date: string, start_time: string, customer_name: string, whatsapp_number: string, notes?: ?string}  $data
     */
    public function createBooking(array $data, ?User $user = null): Booking
    {
        $unit = Unit::findOrFail($data['unit_id']);
        $priceList = $unit->findActivePriceList((int) $data['price_list_id']);

        if (! $priceList) {
            throw ValidationException::withMessages([
                'price_list_id' => 'Durasi & harga yang dipilih tidak berlaku untuk kategori unit ini.',
            ]);
        }

        $start = $this->resolveStartTime($data['booking_date'], $data['start_time']);
        $end = $start->copy()->addMinutes($priceList->duration_minutes);

        if ($reason = $this->slotUnavailableReason($unit->id, $start, $end)) {
            throw ValidationException::withMessages(['start_time' => $reason]);
        }

        return DB::transaction(function () use ($data, $user, $unit, $priceList, $start, $end) {
            return Booking::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => $user?->id,
                'unit_id' => $unit->id,
                'price_list_id' => $priceList->id,
                'customer_name' => $data['customer_name'],
                'whatsapp_number' => $data['whatsapp_number'],
                'booking_date' => $data['booking_date'],
                'start_time' => $start,
                'end_time' => $end,
                'duration_minutes' => $priceList->duration_minutes,
                'total_price' => $priceList->price,
                'notes' => $data['notes'] ?? null,
                'status' => Booking::STATUS_PENDING,
            ]);
        });
    }

    public function confirm(Booking $booking): void
    {
        $this->assertNotTerminal($booking);

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

        $this->syncBookedUnits($booking->unit_id);
    }

    public function cancel(Booking $booking): void
    {
        $this->assertNotTerminal($booking);

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        $this->syncBookedUnits($booking->unit_id);
    }

    public function complete(Booking $booking): void
    {
        $this->assertNotTerminal($booking);

        $booking->update(['status' => Booking::STATUS_COMPLETED]);

        $this->syncBookedUnits($booking->unit_id);
    }

    /**
     * Unit hanya berstatus BOOKED mulai BOOKED_LEAD_MINUTES sebelum booking dimulai hingga booking berakhir,
     * sehingga unit tetap bisa dipakai walk-in selama jam booking masih jauh.
     * Hanya mengubah unit AVAILABLE <-> BOOKED; unit PLAYING/MAINTENANCE/OFFLINE tidak disentuh.
     */
    public function syncBookedUnits(?int $unitId = null): void
    {
        $unitIdsWithImminentBooking = Booking::query()
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->where('start_time', '<=', now()->addMinutes(self::BOOKED_LEAD_MINUTES))
            ->where('end_time', '>', now())
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->distinct()
            ->pluck('unit_id');

        Unit::query()
            ->when($unitId, fn ($q) => $q->whereKey($unitId))
            ->where('status', Unit::STATUS_AVAILABLE)
            ->whereIn('id', $unitIdsWithImminentBooking)
            ->update(['status' => Unit::STATUS_BOOKED]);

        Unit::query()
            ->when($unitId, fn ($q) => $q->whereKey($unitId))
            ->where('status', Unit::STATUS_BOOKED)
            ->whereNotIn('id', $unitIdsWithImminentBooking)
            ->update(['status' => Unit::STATUS_AVAILABLE]);
    }

    /**
     * Booking PENDING/CONFIRMED berikutnya yang belum berakhir pada unit ini.
     */
    public function nextBooking(int $unitId): ?Booking
    {
        return Booking::query()
            ->where('unit_id', $unitId)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->where('end_time', '>', now())
            ->orderBy('start_time')
            ->first();
    }

    /**
     * Sisa menit yang bisa dipakai walk-in sebelum booking berikutnya (dikurangi jeda persiapan),
     * atau null bila tidak ada booking berikutnya.
     */
    public function walkInMinutesAvailable(?Booking $nextBooking): ?int
    {
        if (! $nextBooking) {
            return null;
        }

        return max(0, (int) floor(now()->diffInMinutes($nextBooking->start_time->copy()->subMinutes(self::BOOKED_LEAD_MINUTES), false)));
    }

    private function assertNotTerminal(Booking $booking): void
    {
        if (in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED], true)) {
            throw ValidationException::withMessages([
                'status' => 'Booking ini sudah dalam status akhir dan tidak dapat diubah.',
            ]);
        }
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK'.now()->format('ymd').Str::upper(Str::random(4));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
