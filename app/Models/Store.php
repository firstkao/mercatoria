<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_belanja_rmb',
        // [FIX #1] Rename — sesuai migration & blade product form.
        'ongkir_lokal_cn_percent',
    ];

    protected function casts(): array
    {
        return [
            'min_belanja_rmb' => 'decimal:2',
            'ongkir_lokal_cn_percent' => 'decimal:2',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'default_store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * [FIX #1] Ongkir lokal = 0% → free shipping.
     */
    public function isFreeShipping(): bool
    {
        return (float) $this->ongkir_lokal_cn_percent === 0.0;
    }

    public function hasMinBelanja(): bool
    {
        return (float) $this->min_belanja_rmb > 0;
    }
}