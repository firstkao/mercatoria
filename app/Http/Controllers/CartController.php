<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartPricingService;
use App\Services\Cart\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartPricingService $pricing,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): View
    {
        $carts = $request->user()->carts()
            ->with(['product.store', 'product.games.defaultStore', 'variant.combinations'])
            ->latest()
            ->get();
        $lines = $carts->map(fn (Cart $cart): array => [
            'cart' => $cart,
            'pricing' => $cart->product
                ? $this->pricing->calculate($cart->product, $cart->variant)
                : null,
        ]);

        return view('cart.index', compact('lines'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::with('variants')->findOrFail($validated['product_id']);
        $variant = isset($validated['variant_id'])
            ? ProductVariant::findOrFail($validated['variant_id'])
            : null;

        if (! $product->isPurchasable) {
            throw ValidationException::withMessages(['product_id' => 'Produk ini tidak tersedia untuk dibeli.']);
        }
        if ($product->type === 'variant' && ! $variant) {
            throw ValidationException::withMessages(['variant_id' => 'Pilih varian produk terlebih dahulu.']);
        }
        if ($variant && (
            $product->type !== 'variant'
            || $variant->product_id !== $product->id
            || $variant->availability !== 'tersedia'
        )) {
            throw ValidationException::withMessages(['variant_id' => 'Varian yang dipilih tidak tersedia.']);
        }

        DB::transaction(function () use ($request, $product, $variant, $validated): void {
            DB::table('users')->where('id', $request->user()->id)->lockForUpdate()->first();
            $cart = $request->user()->carts()
                ->forProductVariant($product->id, $variant?->id)
                ->lockForUpdate()
                ->first();

            if ($cart) {
                $cart->qty = min(99, $cart->qty + $validated['qty']);
                $cart->save();

                return;
            }

            $request->user()->carts()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'qty' => $validated['qty'],
            ]);
        });

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $cart): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $item = $request->user()->carts()->findOrFail($cart);
        $item->update($validated);

        return redirect()->route('cart.index')->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function remove(Request $request, int $cart): RedirectResponse
    {
        $request->user()->carts()->findOrFail($cart)->delete();

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): View
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $cartIds = $this->checkout->validatedCartIds($validated);
        $lines = $this->checkout->linesFor($request->user(), $cartIds);
        $subtotal = round($lines->sum('subtotal'), 2);
        $profitPercent = (float) setting('profit_percent', 11);
        $total = (int) (ceil(($subtotal * (1 + $profitPercent / 100)) / 5000) * 5000);

        return view('cart.checkout', compact('lines', 'cartIds', 'subtotal', 'profitPercent', 'total'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['required', 'integer', 'distinct'],
            'platform' => ['required', 'in:shopee,toco'],
            'payment_scheme' => ['required', 'in:fp,dp'],
            'payment_method' => ['required', 'in:bca,qris,shopeepay'],
        ]);
        $cartIds = $this->checkout->validatedCartIds($validated);
        $order = $this->checkout->placeOrder($request->user(), $cartIds, $validated);

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }

    public function showOrder(Request $request, int $order): View
    {
        $order = $request->user()->orders()->findOrFail($order);

        return view('cart.order-created', compact('order'));
    }
}
