<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'booking_id', 'unit_id', 'user_id', 'admin_id', 'price_list_id',
    'customer_name', 'whatsapp_number',
    'rental_start_time', 'rental_end_time', 'actual_end_time', 'duration_minutes',
    'total_price', 'power_on_sent', 'power_off_sent', 'status',
])]
class RentalSession extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected function casts(): array
    {
        return [
            'rental_start_time' => 'datetime',
            'rental_end_time' => 'datetime',
            'actual_end_time' => 'datetime',
            'duration_minutes' => 'integer',
            'total_price' => 'decimal:2',
            'power_on_sent' => 'boolean',
            'power_off_sent' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    /**
     * Remaining seconds based on server time, never negative.
     */
    public function remainingSeconds(): int
    {
        return max(0, now()->diffInSeconds($this->rental_end_time, false));
    }
}
