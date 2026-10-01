<?php

namespace App\Http\Requests\User;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class LookupBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'booking_code' => strtoupper(trim((string) $this->booking_code)),
            'whatsapp_number' => Booking::normalizeWhatsappInput($this->whatsapp_number),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'booking_code' => ['required', 'string', 'max:50'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'booking_code' => 'kode booking',
            'whatsapp_number' => 'nomor WhatsApp',
        ];
    }
}
