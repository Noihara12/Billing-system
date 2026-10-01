<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'booking_code', 'user_id', 'unit_id', 'price_list_id', 'created_by',
    'customer_name', 'whatsapp_number', 'booking_date', 'start_time', 'end_time',
    'duration_minutes', 'total_price', 'notes', 'status',
])]
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_CONFIRMED = 'CONFIRMED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_COMPLETED = 'COMPLETED';

    /**
     * Aturan nomor WhatsApp Indonesia setelah dinormalkan: 08 diikuti 7–12 angka.
     */
    public const WHATSAPP_RULE = 'regex:/^08[0-9]{7,12}$/';

    public const WHATSAPP_MESSAGE = 'Nomor WhatsApp harus berupa angka dan diawali 08, contoh 081234567890.';

    /**
     * Samakan format nomor WhatsApp untuk pencocokan: hanya angka, diawali 0.
     * Contoh: "+62 812-3456", "62812 3456", "0812-3456" -> "08123456".
     */
    public static function normalizeWhatsapp(?string $number): string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        return '0'.ltrim($digits, '0');
    }

    /**
     * Versi untuk input form: kosong menjadi null, dan isian tanpa angka dibiarkan apa adanya
     * supaya ditolak validasi dengan pesan yang jelas, bukan diam-diam menjadi kosong.
     */
    public static function normalizeWhatsappInput(?string $number): ?string
    {
        if (blank($number)) {
            return null;
        }

        return static::normalizeWhatsapp($number) ?: $number;
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'duration_minutes' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rentalSession(): HasOne
    {
        return $this->hasOne(RentalSession::class);
    }
}
