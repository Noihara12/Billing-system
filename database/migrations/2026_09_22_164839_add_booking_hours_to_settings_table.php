<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore([
            ['key' => 'booking_open_time', 'value' => '09:00', 'description' => 'Jam buka booking (HH:MM)', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'booking_close_time', 'value' => '24:00', 'description' => 'Jam tutup booking (HH:MM, 24:00 = tengah malam)', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['booking_open_time', 'booking_close_time'])->delete();
    }
};
