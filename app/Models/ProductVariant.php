<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_rmb',
        'weight_gram',
        'availability',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price_rmb' => 'decimal:2',
            'weight_gram' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Harga RMB efektif: varian, fallback produk induk.
     */
    public function effectivePriceRmb(): float
    {
        return (float) ($this->price_rmb ?? $this->product?->price_rmb ?? 0);
    }

    /**
     * Berat efektif (gram): varian, fallback produk induk.
     */
    public function effectiveWeight(): int
    {
        return $this->weight_gram ?? $this->product?->effectiveWeight() ?? 0;
    }
}
