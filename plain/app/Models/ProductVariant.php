<?php

namespace App\Models;

use App\Support\PriceCalculator;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'sku', 'image_path', 'price_yuan', 'compare_price_yuan', 'weight_grams', 'status', 'sort_order'])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_yuan' => 'decimal:2',
            'compare_price_yuan' => 'decimal:2',
            'weight_grams' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * Get the rupiah selling price using the product's shipping tier.
     */
    public function sellingPrice(PriceCalculator $calculator): ?int
    {
        return $this->priceFor((float) $this->price_yuan, $calculator);
    }

    /**
     * Get the strike-through price, only while the product's sale period is active.
     */
    public function comparePrice(PriceCalculator $calculator): ?int
    {
        if ($this->compare_price_yuan === null || ! $this->product->isOnSale()) {
            return null;
        }

        $comparePrice = $this->priceFor((float) $this->compare_price_yuan, $calculator);
        $sellingPrice = $this->sellingPrice($calculator);

        return $comparePrice !== null && $sellingPrice !== null && $comparePrice > $sellingPrice ? $comparePrice : null;
    }

    private function priceFor(float $priceYuan, PriceCalculator $calculator): ?int
    {
        $tier = $this->product->shippingTier;

        return $calculator->sellingPrice($priceYuan, $this->weight_grams, (float) $tier->fee_yuan, (float) $tier->min_purchase_yuan);
    }
}