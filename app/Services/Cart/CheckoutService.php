<?php

namespace App\Services\Cart;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Order\OrderItemSnapshotService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartPricingService $pricing,
        private readonly OrderItemSnapshotService $snapshots,
    ) {}

    /**
     * @param  list<int>  $cartIds
     * @return Collection<int, array{cart: Cart, pricing: array<string, mixed>, subtotal: float}>
     */
    public function linesFor(User $user, array $cartIds): Collection
    {
        $carts = $user->carts()
            ->with(['product.store', 'product.games.defaultStore', 'variant.combinations'])
            ->whereIn('id', $cartIds)
            ->get();

        if ($carts->count() !== count(array_unique($cartIds))) {
            throw ValidationException::withMessages([
                'cart_ids' => 'Satu atau beberapa item keranjang tidak lagi tersedia.',
            ]);
        }

        return $carts->map(function (Cart $cart): array {
            if (! $cart->product?->isPurchasable) {
                throw ValidationException::withMessages([
                    'cart' => "Produk {$cart->product?->name} tidak lagi tersedia.",
                ]);
            }

            if ($cart->variant_id && (
                ! $cart->variant
                || $cart->product->type !== 'variant'
                || $cart->variant->availability !== 'tersedia'
                || $cart->variant->product_id !== $cart->product_id
            )) {
                throw ValidationException::withMessages([
                    'cart' => 'Salah satu varian yang dipilih tidak lagi tersedia.',
                ]);
            }

            if ($cart->product->type === 'variant' && ! $cart->variant_id) {
                throw ValidationException::withMessages([
                    'cart' => "Pilih varian untuk produk {$cart->product->name}.",
                ]);
            }

            $pricing = $this->pricing->calculate($cart->product, $cart->variant);

            return [
                'cart' => $cart,
                'pricing' => $pricing,
                'subtotal' => round($pricing['final_price'] * $cart->qty, 2),
            ];
        });
    }

    /**
     * @param  list<int>  $cartIds
     * @param  array{platform: string, payment_scheme: string, payment_method: string, customer_note: ?string}  $checkout
     */
    public function placeOrder(User $user, array $cartIds, array $checkout): Order
    {
        if (! $user->no_hp || ! $user->alamat_lengkap || ! $user->kecamatan) {
            throw ValidationException::withMessages([
                'profile' => 'Lengkapi nomor HP dan alamat pengiriman sebelum membuat pesanan.',
            ]);
        }

        return DB::transaction(function () use ($user, $cartIds, $checkout): Order {
            $carts = $user->carts()
                ->with(['product.store', 'product.games.defaultStore', 'product.tags', 'variant.combinations'])
                ->whereIn('id', $cartIds)
                ->lockForUpdate()
                ->get();

            if ($carts->count() !== count(array_unique($cartIds))) {
                throw ValidationException::withMessages([
                    'cart_ids' => 'Satu atau beberapa item keranjang tidak lagi tersedia.',
                ]);
            }

            $productIds = $carts->pluck('product_id')->unique()->all();
            $variantIds = $carts->pluck('variant_id')->filter()->unique()->all();
            Product::query()->whereIn('id', $productIds)->lockForUpdate()->get();
            if ($variantIds !== []) {
                ProductVariant::query()->whereIn('id', $variantIds)->lockForUpdate()->get();
            }

            $lines = $this->linesFor($user, $cartIds);
            $discount = round($lines->sum(
                fn (array $line): float => $line['pricing']['discount_amount'] * $line['cart']->qty,
            ), 2);
            $subtotal = round($lines->sum(
                fn (array $line): float => (
                    $line['pricing']['base_price'] + $line['pricing']['shipping']['ongkir_total_idr']
                ) * $line['cart']->qty,
            ), 2);
            $profitPercent = (float) setting('profit_percent', 11);
            $totalBeforeRounding = ($subtotal - $discount) * (1 + $profitPercent / 100);
            $total = (int) (ceil($totalBeforeRounding / 5000) * 5000);
            $address = collect([
                $user->alamat_lengkap,
                $user->kecamatan,
                $user->kota,
                $user->provinsi,
                $user->kode_pos,
            ])->filter()->join(', ');

            $order = Order::create([
                'order_no' => 'MC-'.Str::upper(Str::random(16)),
                'user_id' => $user->id,
                'status' => OrderStatus::MENUNGGU_PEMBAYARAN->value,
                'payment_scheme' => $checkout['payment_scheme'],
                'platform' => $checkout['platform'],
                'payment_method' => $checkout['payment_method'],
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->no_hp,
                'customer_address' => $address,
                'customer_note' => $carts
                    ->filter(fn (Cart $cart): bool => filled($cart->customer_note))
                    ->map(fn (Cart $cart): string => "{$cart->product->name}: {$cart->customer_note}")
                    ->join("\n"),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_cost' => 0,
                'total' => $total,
                'frozen_rate_rmb_idr' => setting('rate_rmb_idr', 2200),
                'frozen_profit_percent' => $profitPercent,
            ]);

            foreach ($carts as $cart) {
                $item = $order->items()->create([
                    'product_id' => $cart->product_id,
                    'variant_id' => $cart->variant_id,
                    'qty' => $cart->qty,
                ]);
                $this->snapshots->createForOrderItem($item, $order->order_no);
                $cart->delete();
            }

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    public function validatedCartIds(array $validated): array
    {
        return array_values(array_unique(array_map('intval', $validated['cart_ids'])));
    }
}
