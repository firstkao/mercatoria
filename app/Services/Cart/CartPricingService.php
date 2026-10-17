<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Order\ShippingCalculatorService;

class CartPricingService
{
    public function __construct(private readonly ShippingCalculatorService $shippingCalculator) {}

    /**
     * @return array{base_price: float, discount_amount: float, final_price: float, shipping: array<string, int|float|string|null>}
     */
    public function calculate(Product $product, ?ProductVariant $variant = null): array
    {
        $shipping = $this->shippingCalculator->calculateItemShipping($product, $variant);
        $basePrice = $variant && (float) $variant->price > 0
            ? (float) $variant->price
            : (float) $product->price_rmb * (float) setting('rate_rmb_idr', 2200);
        $discountAmount = $product->hasActiveDiscount() ? (float) $product->discount_amount : 0;
        $finalPrice = max(0, $basePrice + $shipping['ongkir_total_idr'] - $discountAmount);

        return [
            'base_price' => round($basePrice, 2),
            'discount_amount' => round(min($discountAmount, $basePrice + $shipping['ongkir_total_idr']), 2),
            'final_price' => round($finalPrice, 2),
            'shipping' => $shipping,
        ];
    }
}
