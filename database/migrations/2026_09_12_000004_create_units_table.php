<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit_code')->unique();
            $table->enum('status', ['AVAILABLE', 'BOOKED', 'PLAYING', 'MAINTENANCE', 'OFFLINE'])->default('AVAILABLE');
            $table->foreignId('tasmota_device_id')->nullable()->unique()->constrained('tasmota_devices')->nullOnDelete();
            $table->boolean('auto_power_on')->default(false);
            $table->boolean('auto_power_off')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
