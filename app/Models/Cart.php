<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'variant_id', 'qty', 'customer_note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function scopeForProductVariant(Builder $query, int $productId, ?int $variantId): Builder
    {
        return $query->where('product_id', $productId)
            ->when(
                $variantId === null,
                fn (Builder $query) => $query->whereNull('variant_id'),
                fn (Builder $query) => $query->where('variant_id', $variantId),
            );
    }
}
