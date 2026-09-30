<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'code',
    'name',
    'discount_type',
    'value',
    'max_discount_idr',
    'min_purchase_idr',
    'is_birthday',
    'is_personal',
    'auto_type',
    'auto_year',
    'starts_at',
    'ends_at',
    'usage_limit',
    'per_user_limit',
    'is_active',
])]
class Voucher extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_birthday' => 'boolean',
            'is_personal' => 'boolean',
            'auto_year' => 'integer',
            'usage_limit' => 'integer',
            'per_user_limit' => 'integer',
            'value' => 'integer',
            'max_discount_idr' => 'integer',
            'min_purchase_idr' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    /**
     * Cek apakah voucher masih berlaku.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->redemptions()->count() >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah user tertentu boleh pakai voucher ini.
     */
    public function isUsableBy(User $user): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        // Voucher personal hanya bisa dipakai pemiliknya
        if ($this->is_personal && $this->user_id !== $user->id) {
            return false;
        }

        // Cek per-user limit
        if ($this->per_user_limit !== null) {
            $used = $this->redemptions()->where('user_id', $user->id)->count();
            if ($used >= $this->per_user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Hitung diskon berdasarkan subtotal.
     */
    public function discountFor(int $subtotal): int
    {
        if ($this->discount_type === 'nominal') {
            return min($this->value, $subtotal);
        }

        // percent
        $discount = (int) floor($subtotal * $this->value / 100);

        if ($this->max_discount_idr) {
            $discount = min($discount, $this->max_discount_idr);
        }

        // Diskon tidak boleh melebihi subtotal (voucher persen > 100 membuat total order negatif).
        return max(0, min($discount, $subtotal));
    }
}
