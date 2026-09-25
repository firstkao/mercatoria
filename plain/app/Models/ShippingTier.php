<?php

namespace App\Models;

use Database\Factories\ShippingTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'fee_yuan', 'min_purchase_yuan'])]
class ShippingTier extends Model
{
    /** @use HasFactory<ShippingTierFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee_yuan' => 'decimal:2',
            'min_purchase_yuan' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}