<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['device_name', 'ip_address', 'description', 'username', 'password_encrypted', 'status', 'last_checked_at'])]
#[Hidden(['password_encrypted'])]
class TasmotaDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'password_encrypted' => 'encrypted',
            'last_checked_at' => 'datetime',
        ];
    }

    public function unit(): HasOne
    {
        return $this->hasOne(Unit::class);
    }
}
