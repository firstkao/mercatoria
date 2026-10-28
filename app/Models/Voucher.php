<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'nominal',
        'min_belanja',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'min_belanja' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }
}
