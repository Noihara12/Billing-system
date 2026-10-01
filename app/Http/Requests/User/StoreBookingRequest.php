<?php

namespace App\Http\Requests\User;

use App\Models\Booking;
use App\Models\PriceList;
use App\Services\BookingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Batas booking PENDING yang masih berjalan untuk satu nomor WhatsApp tanpa login.
     */
    public const MAX_ACTIVE_GUEST_BOOKINGS = 2;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['whatsapp_number' => Booking::normalizeWhatsappInput($this->whatsapp_number)]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['whatsapp_number.regex' => Booking::WHATSAPP_MESSAGE];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'price_list_id' => ['required', 'exists:price_lists,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', Booking::WHATSAPP_RULE],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Booking lama bisa tersimpan dengan format lain, jadi pencocokan dilakukan setelah dinormalkan.
     */
    private function activeGuestBookings(): int
    {
        $whatsapp = Booking::normalizeWhatsapp($this->input('whatsapp_number'));

        return Booking::query()
            ->where('status', Booking::STATUS_PENDING)
            ->where('end_time', '>', now())
            ->pluck('whatsapp_number')
            ->filter(fn (string $number) => Booking::normalizeWhatsapp($number) === $whatsapp)
            ->count();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->user() && $this->activeGuestBookings() >= self::MAX_ACTIVE_GUEST_BOOKINGS) {
                $validator->errors()->add('whatsapp_number', 'Nomor WhatsApp ini masih punya '.self::MAX_ACTIVE_GUEST_BOOKINGS.' booking yang menunggu konfirmasi. Hubungi admin atau batalkan lewat halaman Cek Booking.');

                return;
            }

            $priceList = PriceList::find($this->input('price_list_id'));

            if (! $priceList) {
                return;
            }

            $start = app(BookingService::class)->resolveStartTime($this->input('booking_date'), $this->input('start_time'));
            $end = $start->copy()->addMinutes($priceList->duration_minutes);

            if ($reason = app(BookingService::class)->slotUnavailableReason((int) $this->input('unit_id'), $start, $end)) {
                $validator->errors()->add('start_time', $reason);
            }
        });
    }
}
