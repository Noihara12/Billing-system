<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            $table->dateTime('rental_start_time');
            $table->dateTime('rental_end_time');
            $table->dateTime('actual_end_time')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->decimal('total_price', 12, 2);
            $table->boolean('power_on_sent')->default(false);
            $table->boolean('power_off_sent')->default(false);
            $table->enum('status', ['ACTIVE', 'COMPLETED', 'CANCELLED'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_sessions');
    }
};
