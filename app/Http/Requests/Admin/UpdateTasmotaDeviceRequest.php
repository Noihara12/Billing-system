<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTasmotaDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Unique>>
     */
    public function rules(): array
    {
        $deviceId = $this->route('tasmota_device')?->id;

        return [
            'device_name' => ['required', 'string', 'max:100'],
            'ip_address'  => [
                'required',
                'string',
                'max:45',
                'regex:/^(\d{1,3}\.){3}\d{1,3}$|^[a-zA-Z0-9\-\.]+$/',
                Rule::unique('tasmota_devices', 'ip_address')->ignore($deviceId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'username'    => ['nullable', 'string', 'max:100'],
            'password'    => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'in:ONLINE,OFFLINE,UNKNOWN'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_name.required' => 'Nama perangkat wajib diisi.',
            'ip_address.required'  => 'IP Address wajib diisi.',
            'ip_address.unique'    => 'IP Address ini sudah digunakan oleh perangkat lain.',
            'ip_address.regex'     => 'Format IP Address tidak valid.',
            'status.in'            => 'Status harus salah satu dari: ONLINE, OFFLINE, UNKNOWN.',
        ];
    }
}

