<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasmota_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('ip_address')->unique();
            $table->text('description')->nullable();
            $table->string('username')->nullable();
            $table->text('password_encrypted')->nullable();
            $table->enum('status', ['ONLINE', 'OFFLINE', 'UNKNOWN'])->default('UNKNOWN');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasmota_devices');
    }
};
