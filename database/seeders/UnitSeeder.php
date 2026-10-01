<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Game;
use App\Models\TasmotaDevice;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar game contoh
        $gameNames = [
            'Tekken 8',
            'EA Sports FC 26',
            'Street Fighter 6',
            'Mortal Kombat 1',
            'Gran Turismo 7',
            'GTA V',
            'Resident Evil 4',
            'God of War: Ragnarok',
            'Spider-Man 2',
            'Elden Ring',
        ];

        // Buat game jika belum ada
        $games = [];
        foreach ($gameNames as $name) {
            $games[] = Game::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // Ambil semua perangkat Tasmota yang tersedia
        $tasmotaDevices = TasmotaDevice::orderBy('id')->get()->keyBy(fn ($d) => $d->device_name);

        // Data unit
        $units = [
            [
                'name'            => 'PS 01',
                'unit_code'       => 'PS-01',
                'category'        => 'PS 4',
                'status'          => Unit::STATUS_AVAILABLE,
                'is_active'       => true,
                'auto_power_on'   => false,
                'auto_power_off'  => false,
                'description'     => 'Unit PlayStation 1',
                'tasmota'         => 'Sonoff PS 01',
                'games'           => ['Tekken 8', 'EA Sports FC 26', 'Street Fighter 6', 'Mortal Kombat 1'],
            ],
            [
                'name'            => 'PS 02',
                'unit_code'       => 'PS-02',
                'category'        => 'PS 4',
                'status'          => Unit::STATUS_AVAILABLE,
                'is_active'       => true,
                'auto_power_on'   => false,
                'auto_power_off'  => false,
                'description'     => 'Unit PlayStation 2',
                'tasmota'         => 'Sonoff PS 02',
                'games'           => ['Tekken 8', 'Gran Turismo 7', 'GTA V', 'Resident Evil 4'],
            ],
            [
                'name'            => 'PS 03',
                'unit_code'       => 'PS-03',
                'category'        => 'PS 5',
                'status'          => Unit::STATUS_AVAILABLE,
                'is_active'       => true,
                'auto_power_on'   => false,
                'auto_power_off'  => false,
                'description'     => 'Unit PlayStation 3',
                'tasmota'         => 'Sonoff PS 03',
                'games'           => ['God of War: Ragnarok', 'Spider-Man 2', 'Elden Ring', 'GTA V'],
            ],
            [
                'name'            => 'PS 04',
                'unit_code'       => 'PS-04',
                'category'        => 'PS 5',
                'status'          => Unit::STATUS_AVAILABLE,
                'is_active'       => true,
                'auto_power_on'   => false,
                'auto_power_off'  => false,
                'description'     => 'Unit PlayStation 4',
                'tasmota'         => 'Sonoff PS 04',
                'games'           => ['Tekken 8', 'Street Fighter 6', 'Elden Ring', 'EA Sports FC 26'],
            ],
        ];

        foreach ($units as $unitData) {
            $tasmotaName = $unitData['tasmota'];
            $gameNames   = $unitData['games'];
            $categoryName = $unitData['category'];

            unset($unitData['tasmota'], $unitData['games'], $unitData['category']);

            $unitData['category_id'] = Category::query()->firstOrCreate(['name' => $categoryName])->id;

            // Tambahkan tasmota_device_id jika ada
            if (isset($tasmotaDevices[$tasmotaName])) {
                $unitData['tasmota_device_id'] = $tasmotaDevices[$tasmotaName]->id;
            }

            $unit = Unit::firstOrCreate(
                ['unit_code' => $unitData['unit_code']],
                $unitData
            );

            // Sync games
            $gameIds = collect($games)
                ->filter(fn ($g) => in_array($g->name, $gameNames))
                ->pluck('id')
                ->toArray();

            $unit->games()->sync($gameIds);
        }

        $this->command->info('✅ Unit seeder selesai: ' . count($units) . ' unit dibuat dengan game dan Tasmota terhubung.');
    }
}

