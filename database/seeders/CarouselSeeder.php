<?php

namespace Database\Seeders;

use App\Models\Carousel;
use Illuminate\Database\Seeder;

class CarouselSeeder extends Seeder
{
    public function run(): void
    {
        $carousels = [
            [
                'title' => 'Promo Special Weekend',
                'description' => 'Nikmati diskon hingga 30% untuk semua paket sewa PS5 di akhir pekan!',
                'image_path' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Game Terbaru Tersedia',
                'description' => 'Mainkan game terbaru dan paling trending bersama teman-teman Anda.',
                'image_path' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Member Baru Gratis 1 Jam',
                'description' => 'Daftar sekarang dan dapatkan voucher gratis 1 jam pertama!',
                'image_path' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($carousels as $carousel) {
            Carousel::query()->updateOrCreate(
                ['title' => $carousel['title']],
                $carousel
            );
        }
    }
}
