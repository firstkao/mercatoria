<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_published, 404);

        $user = $request->user();

        if ($user->isSpammer() && ! $user->consumeViewQuota($product)) {
            return view('products.locked', [
                'hasCartItems' => $user->hasCartItems(),
                'viewQuota' => Setting::integer('view_quota', 10),
            ]);
        }

        ActivityLog::record($user, 'view_product', $request, ['product_id' => $product->id]);

        $product->load(['images', 'variants', 'shippingTier', 'game', 'developer']);
        $calculator = PriceCalculator::fromSettings();

        return view('products.show', [
            'user' => $user,
            'product' => $product,
            'variants' => $product->variants->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'name' => $variant->name,
                'available' => $variant->isAvailable(),
                'price' => $variant->sellingPrice($calculator),
                'comparePrice' => $variant->comparePrice($calculator),
                'imageUrl' => $variant->imageUrl(),
            ]),
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }
}