<?php

namespace Database\Seeders;

use App\Models\TasmotaDevice;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class TasmotaDeviceSeeder extends Seeder
{
    /**
     * Seed contoh perangkat Tasmota untuk 4 unit PlayStation.
     *
     * IP Address ini adalah contoh — sesuaikan dengan jaringan aktual Anda.
     * Dalam environment development/testing, perangkat ini tidak perlu benar-benar ada.
     * Sistem akan menampilkan status OFFLINE jika perangkat tidak dapat dijangkau.
     */
    public function run(): void
    {
        $devices = [
            [
                'device_name' => 'Sonoff PS 01',
                'ip_address'  => '192.168.1.101',
                'description' => 'Perangkat Sonoff Basic untuk unit PlayStation 01',
                'status'      => 'UNKNOWN',
            ],
            [
                'device_name' => 'Sonoff PS 02',
                'ip_address'  => '192.168.1.102',
                'description' => 'Perangkat Sonoff Basic untuk unit PlayStation 02',
                'status'      => 'UNKNOWN',
            ],
            [
                'device_name' => 'Sonoff PS 03',
                'ip_address'  => '192.168.1.103',
                'description' => 'Perangkat Sonoff Basic untuk unit PlayStation 03',
                'status'      => 'UNKNOWN',
            ],
            [
                'device_name' => 'Sonoff PS 04',
                'ip_address'  => '192.168.1.104',
                'description' => 'Perangkat Sonoff Basic untuk unit PlayStation 04',
                'status'      => 'UNKNOWN',
            ],
        ];

        foreach ($devices as $index => $deviceData) {
            $device = TasmotaDevice::firstOrCreate(
                ['ip_address' => $deviceData['ip_address']],
                $deviceData
            );

            // Hubungkan ke unit yang sesuai (PS 01 → index 0, dst.)
            $unit = Unit::orderBy('name')->skip($index)->first();
            if ($unit && ! $unit->tasmota_device_id) {
                $unit->update([
                    'tasmota_device_id' => $device->id,
                    'auto_power_on'     => false, // Default: manual (lebih aman)
                    'auto_power_off'    => false,
                ]);
            }
        }

        $this->command->info('✅ TasmotaDevice seeder selesai: ' . count($devices) . ' perangkat dibuat.');
    }
}

