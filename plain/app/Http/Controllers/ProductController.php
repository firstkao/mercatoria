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

        // Guard guest: halaman produk kini GLOBAL (guest boleh lihat), jadi
        // cek spammer/kuota hanya kalau ada user login. Dulu tanpa guard ini,
        // tamu yang buka /produk/... langsung kena 500 "isSpammer() on null".
        if ($user !== null && $user->isSpammer() && ! $user->consumeViewQuota($product)) {
            return view('products.locked', [
                'hasCartItems' => $user->hasCartItems(),
                'viewQuota' => Setting::integer('view_quota', 10),
            ]);
        }

        if ($user !== null) {
            ActivityLog::record($user, 'view_product', $request, [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ]);
        }

        $product->load(['images', 'variants', 'shippingTier', 'game', 'developer']);
        $calculator = PriceCalculator::fromSettings();

        // SEO data
        $description = $product->description
            ? \Illuminate\Support\Str::limit(strip_tags($product->description), 155)
            : 'Beli ' . $product->name . ' original dari Tmall. Kirim ke seluruh Indonesia.';

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
            // SEO
            'title' => $product->name,
            'metaDescription' => $description,
            'metaImage' => $product->images->first()?->url(),
            'ogType' => 'product',
        ]);
    }
}