<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'name', 'price',
        'availability', 'weight_gram', 'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight_gram' => 'integer',
    ];

    public function product(): BelongsTo
    { return $this->belongsTo(Product::class); }

    public function combinations(): HasMany
    { return $this->hasMany(VariantCombination::class, 'variant_id'); }

    public function images(): HasMany
    { return $this->hasMany(VariantImage::class, 'variant_id'); }

    public function displayImage(): ?string
    {
        return $this->image_path ?? $this->product?->main_image_path;
    }

    public function attributesArray(): array
    {
        return $this->combinations
            ->pluck('attribute_value', 'attribute_key')
            ->toArray();
    }

    public function effectiveWeight(): int
    {
        return $this->weight_gram ?? $this->product?->weight_gram ?? 0;
    }
}
