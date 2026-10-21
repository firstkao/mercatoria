<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = ['name', 'min_belanja_rmb', 'ongkir_rmb'];

    protected $casts = [
        'min_belanja_rmb' => 'decimal:2',
        'ongkir_rmb' => 'decimal:2',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'default_store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isFreeShipping(): bool
    {
        return (float) $this->ongkir_rmb === 0.0;
    }

    public function hasMinBelanja(): bool
    {
        return (float) $this->min_belanja_rmb > 0;
    }
}
