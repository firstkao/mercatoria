<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = DB::table('cart_items')
            ->join('product_variants', 'product_variants.id', '=', 'cart_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('cart_items.user_id', auth()->id())
            ->orderBy('cart_items.id')
            ->select('cart_items.*', 'products.name as product_name', 'products.slug', 'products.is_published',
                'product_variants.name as variant_name', 'product_variants.price_yuan', 'product_variants.status')
            ->get();

        return view('cart.index', ['items' => $items]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $variant = ProductVariant::query()->with('product.shippingTier')->findOrFail($request->integer('product_variant_id'));
        abort_unless($variant->product->is_published && $variant->isAvailable(), 422);

        DB::table('cart_items')->updateOrInsert(
            ['user_id' => $request->user()->id, 'product_variant_id' => $variant->id],
            ['quantity' => DB::raw('LEAST(quantity + '.(int) $request->integer('quantity').', 99)'), 'updated_at' => now(), 'created_at' => now()]
        );

        return redirect()->route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(int $item): RedirectResponse
    {
        $quantity = max(0, min(99, (int) request()->integer('quantity')));
        $query = DB::table('cart_items')->where('id', $item)->where('user_id', auth()->id());
        $quantity === 0 ? $query->delete() : $query->update(['quantity' => $quantity, 'updated_at' => now()]);

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(int $item): RedirectResponse
    {
        DB::table('cart_items')->where('id', $item)->where('user_id', auth()->id())->delete();
        return back()->with('status', 'Produk dihapus dari keranjang.');
    }
}
