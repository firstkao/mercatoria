<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserVoucher;
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

        $customerNote = (string) $request->session()->get('cart_customer_note', '');

        return view('cart.index', compact('lines', 'customerNote'));
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

        $pricing = $this->pricing->calculate($product, $variant);

        return back()
            ->with('success', 'Produk berhasil ditambahkan ke keranjang.')
            ->with('ga4_events', [[
                'name' => 'add_to_cart',
                'params' => [
                    'currency' => 'IDR',
                    'value' => $pricing['final_price'] * $validated['qty'],
                    'items' => [[
                        'item_id' => $product->sku,
                        'item_name' => $product->name,
                        'item_variant' => $variant?->name,
                        'price' => $pricing['final_price'],
                        'quantity' => $validated['qty'],
                    ]],
                ],
            ]]);
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

    public function removeSelected(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $deleted = $request->user()->carts()->whereIn('id', $validated['cart_ids'])->delete();

        return redirect()->route('cart.index')->with('success', $deleted.' item dihapus dari keranjang.');
    }

    public function updateNote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $request->session()->put('cart_customer_note', trim((string) ($validated['customer_note'] ?? '')));

        return redirect()->route('cart.index')->with('success', 'Catatan pembelian berhasil disimpan.');
    }

    public function checkout(Request $request): View
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['required', 'integer', 'distinct'],
            'platform' => ['nullable', 'in:shopee,toco'],
            'payment_scheme' => ['nullable', 'in:fp,dp'],
            'payment_method' => ['nullable', 'in:bca,qris,shopeepay'],
            'use_coins' => ['nullable', 'boolean'],
            'user_voucher_id' => ['nullable', 'integer'],
        ]);
        $cartIds = $this->checkout->validatedCartIds($validated);
        $lines = $this->checkout->linesFor($request->user(), $cartIds);
        $useCoins = $request->boolean('use_coins');
        $userVoucher = null;
        if ($validated['user_voucher_id'] ?? null) {
            $userVoucher = $request->user()->userVouchers()
                ->where('status', 'active')
                ->with('voucher')
                ->find($validated['user_voucher_id']);
            if (! $userVoucher) {
                throw ValidationException::withMessages([
                    'user_voucher_id' => 'Voucher tidak tersedia untuk akun ini.',
                ]);
            }
        }
        $coinBalance = (int) Coin::query()->where('user_id', $request->user()->id)->value('balance');
        $quote = $this->checkout->calculateTotals(
            $lines,
            $request->string('platform', 'toco')->toString(),
            $request->string('payment_scheme', 'fp')->toString(),
            $request->string('payment_method', 'bca')->toString(),
            $coinBalance,
            $useCoins,
            $userVoucher,
        );
        $userVouchers = $request->user()->userVouchers()
            ->where('status', 'active')
            ->with('voucher')
            ->get()
            ->filter(fn (UserVoucher $item): bool => $item->voucher
                && $item->voucher->isValid()
                && empty($item->voucher->scope));
        $profitPercent = (float) setting('profit_percent', 11);

        $customerNote = (string) $request->session()->get('cart_customer_note', '');

        return view('cart.checkout', compact('lines', 'cartIds', 'quote', 'profitPercent', 'userVouchers', 'coinBalance', 'customerNote'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['required', 'integer', 'distinct'],
            'platform' => ['required', 'in:shopee,toco'],
            'payment_scheme' => ['required', 'in:fp,dp'],
            'payment_method' => ['required', 'in:bca,qris,shopeepay'],
            'use_coins' => ['nullable', 'boolean'],
            'user_voucher_id' => ['nullable', 'integer'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $cartIds = $this->checkout->validatedCartIds($validated);
        $validated['customer_note'] = $validated['customer_note'] ?? $request->session()->get('cart_customer_note');
        $order = $this->checkout->placeOrder($request->user(), $cartIds, $validated);
        $request->session()->forget('cart_customer_note');
        $order->load('items.snapshot');
        $items = $order->items->map(fn ($item): array => [
            'item_id' => $item->snapshot->product_sku,
            'item_name' => $item->snapshot->product_name,
            'item_variant' => $item->snapshot->variant_name,
            'price' => (float) $item->snapshot->final_price,
            'quantity' => $item->snapshot->qty,
        ])->values()->all();
        $ecommerce = [
            'currency' => 'IDR',
            'value' => (float) $order->total,
            'items' => $items,
        ];
        $ga4Events = [[
            'name' => 'add_payment_info',
            'params' => [
                ...$ecommerce,
                'payment_type' => $order->payment_method,
            ],
        ]];
        if (filled($order->customer_note)) {
            $ga4Events[] = [
                'name' => 'checkout_with_note',
                'params' => [...$ecommerce, 'note_added' => true],
            ];
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat.')
            ->with('ga4_events', $ga4Events);
    }

    public function showOrder(Request $request, int $order): View
    {
        $order = $request->user()->orders()->findOrFail($order);

        return view('cart.order-created', compact('order'));
    }
}
