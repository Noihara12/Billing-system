<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'admin';

    public const CUSTOMER = 'customer';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
