<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_rmb',
        'discount_amount',
        'discount_start',
        'discount_end',
        'weight_gram',
        'availability',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price_rmb' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_start' => 'date',
            'discount_end' => 'date',
            'weight_gram' => 'integer',
        ];
    }

    /**
     * SKU otomatis bila dikosongkan: {SKU produk}-V{n}, dijamin unik.
     */
    public static function generateSku(Product $product): string
    {
        $n = $product->variants()->count() + 1;

        do {
            $sku = mb_substr($product->sku, 0, 56).'-V'.$n++;
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_attribute_values'
        );
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

    /**
     * Apakah ada diskon yang sedang aktif di varian ini.
     */
    public function hasActiveDiscount(): bool
    {
        if (! $this->discount_amount || (float) $this->discount_amount <= 0) {
            return false;
        }

        $now = now();

        if ($this->discount_start && $now->lt($this->discount_start)) {
            return false;
        }

        if ($this->discount_end && $now->gt($this->discount_end->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Diskon aktif dalam Rupiah (0 kalau tidak ada diskon aktif).
     */
    public function activeDiscountAmount(): float
    {
        return $this->hasActiveDiscount() ? (float) $this->discount_amount : 0.0;
    }
}
