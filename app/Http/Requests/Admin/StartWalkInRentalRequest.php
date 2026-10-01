<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StartWalkInRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user_id ?: null,
            'whatsapp_number' => Booking::normalizeWhatsappInput($this->whatsapp_number),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'price_list_id' => ['required', 'exists:price_lists,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', Booking::WHATSAPP_RULE],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['whatsapp_number.regex' => Booking::WHATSAPP_MESSAGE];
    }
}
