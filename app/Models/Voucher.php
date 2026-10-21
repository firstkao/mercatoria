<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'value', 'min_purchase', 'max_discount',
        'valid_from', 'valid_until', 'per_user_limit', 'scope', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'scope' => 'array',
        'is_active' => 'boolean',
    ];

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function isPercent(): bool
    {
        return $this->type === 'percent';
    }

    public function isNominal(): bool
    {
        return $this->type === 'nominal';
    }
}
