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
        $basePrice = $shipping['base_price_bersih'];
        $discountAmount = $shipping['discount_amount'];
        $finalPrice = $shipping['final_price_bersih'];

        return [
            'base_price' => round($basePrice, 2),
            'discount_amount' => round($discountAmount, 2),
            'final_price' => round($finalPrice, 2),
            'shipping' => $shipping,
        ];
    }
}
