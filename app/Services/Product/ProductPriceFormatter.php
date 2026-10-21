<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Services\Order\ShippingCalculatorService;

/**
 * Harga bersih untuk kartu produk: satu harga, atau rentang min–max dari seluruh varian.
 */
class ProductPriceFormatter
{
    public function __construct(private readonly ShippingCalculatorService $shipping) {}

    /**
     * @return array{min: float, max: float, original_min: float, original_max: float}
     */
    public function range(Product $product): array
    {
        $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
        $finals = [];
        $originals = [];

        foreach ($variants as $variant) {
            $item = $this->shipping->calculateItemShipping($product, $variant);
            $finals[] = $item['final_price_bersih'];
            $originals[] = $item['base_price_bersih'];
        }

        if ($finals === []) {
            $item = $this->shipping->calculateItemShipping($product);
            $finals = [$item['final_price_bersih']];
            $originals = [$item['base_price_bersih']];
        }

        return [
            'min' => min($finals),
            'max' => max($finals),
            'original_min' => min($originals),
            'original_max' => max($originals),
        ];
    }

    public function label(Product $product): string
    {
        $range = $this->range($product);

        return $this->format($range['min'], $range['max']);
    }

    public function originalLabel(Product $product): string
    {
        $range = $this->range($product);

        return $this->format($range['original_min'], $range['original_max']);
    }

    private function format(float $min, float $max): string
    {
        if ($min === $max) {
            return $this->rupiah($min);
        }

        return $this->rupiah($min).' – '.$this->rupiah($max);
    }

    private function rupiah(float $value): string
    {
        return 'Rp'.number_format($value, 0, ',', '.');
    }
}
