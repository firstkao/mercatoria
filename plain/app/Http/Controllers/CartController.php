<?php

namespace App\Http\Controllers;

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
        
        // Menggunakan gaya V1 (Eager Loading) karena terbukti lebih cepat 
        // dan tidak rawan masalah N+1 Query dibanding V2
        $cartItems = $user->cartItems()->with('variant.product.shippingTier')->get();
        $marketplaces = Marketplace::where('is_active', true)->orderBy('name')->get();
        $calculator = PriceCalculator::fromSettings();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->variant->isAvailable()) {
                $subtotal += $item->variant->sellingPrice($calculator) * $item->quantity;
            }
        }

        // Mempertahankan fitur Koin dari V1
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'variant' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        $variant = ProductVariant::with('product')->findOrFail($data['variant']);

        abort_unless($variant->product->is_published && $variant->isAvailable(), 422, 'Varian produk tidak tersedia.');

        $quantity = (int) $data['quantity'];

        // Mengambil fitur Anti-Spam Klik (Database Locking) dari V2
        DB::transaction(function () use ($request, $variant, $quantity) {
            $existing = CartItem::where('user_id', $request->user()->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate() // Menggembok row ini sepersekian detik agar aman dari spam
                ->first();

            if ($existing) {
                $existing->update([
                    'quantity' => min(99, $existing->quantity + $quantity)
                ]);
            } else {
                CartItem::create([
                    'user_id' => $request->user()->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity
                ]);
            }
        });

        return redirect()->route('cart.index')->with('status', 'Berhasil ditambahkan ke keranjang.');
    }

    // Mengambil fitur Update Kuantitas langsung dari V2
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
