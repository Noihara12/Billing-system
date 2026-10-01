<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'name', 'unit_code', 'status', 'tasmota_device_id',
    'auto_power_on', 'auto_power_off', 'is_active', 'description',
])]
class Unit extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_AVAILABLE = 'AVAILABLE';

    public const STATUS_BOOKED = 'BOOKED';

    public const STATUS_PLAYING = 'PLAYING';

    public const STATUS_MAINTENANCE = 'MAINTENANCE';

    public const STATUS_OFFLINE = 'OFFLINE';

    protected function casts(): array
    {
        return [
            'auto_power_on' => 'boolean',
            'auto_power_off' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Price list yang berlaku untuk kategori unit ini.
     */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class, 'category_id', 'category_id');
    }

    /**
     * Cari price list aktif milik kategori unit ini; null jika price list berasal dari kategori lain.
     */
    public function findActivePriceList(int $priceListId): ?PriceList
    {
        return $this->priceLists()->where('is_active', true)->find($priceListId);
    }

    public function tasmotaDevice(): BelongsTo
    {
        return $this->belongsTo(TasmotaDevice::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_unit');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function rentalSessions(): HasMany
    {
        return $this->hasMany(RentalSession::class);
    }

    public function activeRentalSession(): HasOne
    {
        return $this->hasOne(RentalSession::class)->where('status', RentalSession::STATUS_ACTIVE)->latestOfMany();
    }

    public function upcomingBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->where('start_time', '>=', now())
            ->oldestOfMany('start_time');
    }
}
