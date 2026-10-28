<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class CartService
{
    public function add(User $user, Product $product, ?ProductVariant $variant, int $qty): Cart
    {
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($cart) {
            $cart->increment('qty', $qty);

            return $cart->fresh();
        }

        return Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'qty' => $qty,
        ]);
    }
}
