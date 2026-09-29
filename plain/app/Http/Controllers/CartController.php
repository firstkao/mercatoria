<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\CartItem;
use App\Models\CoinLot;
use App\Models\Marketplace;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Eager loading untuk menghindari N+1
        $cartItems = $user->cartItems()->with('variant.product.shippingTier')->get();
        $marketplaces = Marketplace::where('is_active', true)->orderBy('name')->get();
        $calculator = PriceCalculator::fromSettings();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->variant->isAvailable()) {
                $subtotal += $item->variant->sellingPrice($calculator) * $item->quantity;
            }
        }

        // Koin
        $availableCoins = CoinLot::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->sum('remaining');
        $maxCoinDiscount = floor($subtotal * (Setting::integer('coin_max_use_percent', 5) / 100));

        return view('cart.index', [
            'cartItems' => $cartItems,
            'marketplaces' => $marketplaces,
            'calculator' => $calculator,
            'subtotal' => $subtotal,
            'availableCoins' => $availableCoins,
            'maxCoinDiscount' => $maxCoinDiscount,
        ]);
    }

    public function store(AddToCartRequest $request)
    {
        $data = $request->validated();

        $variant = ProductVariant::with('product')->findOrFail($data['variant']);

        abort_unless(
            $variant->product->is_published && $variant->isAvailable(),
            422,
            'Varian produk tidak tersedia.'
        );

        $quantity = (int) $data['quantity'];

        // Anti-spam: lock row supaya klik bersamaan tidak bikin duplikat
        DB::transaction(function () use ($request, $variant, $quantity) {
            $existing = CartItem::where('user_id', $request->user()->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'quantity' => min(99, $existing->quantity + $quantity),
                ]);
            } else {
                CartItem::create([
                    'user_id' => $request->user()->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }
        });

        return redirect()->route('cart.index')->with('status', 'Berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== $request->user()->id, 403);

        $quantity = max(0, min(99, (int) $request->integer('quantity')));

        if ($quantity === 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== auth()->id(), 403);
        $cartItem->delete();

        return back()->with('status', 'Item dihapus dari keranjang.');
    }
}