<?php

namespace App\Http\Requests\Admin;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['tasmota_device_id' => $this->tasmota_device_id ?: null]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit_code' => ['required', 'string', 'max:50', 'unique:units,unit_code'],
            'status' => ['required', Rule::in([
                Unit::STATUS_AVAILABLE, Unit::STATUS_BOOKED, Unit::STATUS_PLAYING,
                Unit::STATUS_MAINTENANCE, Unit::STATUS_OFFLINE,
            ])],
            'tasmota_device_id' => ['nullable', 'exists:tasmota_devices,id', Rule::unique('units', 'tasmota_device_id')],
            'description' => ['nullable', 'string', 'max:2000'],
            'game_ids' => ['nullable', 'array'],
            'game_ids.*' => ['integer', 'exists:games,id'],
        ];
    }
}
