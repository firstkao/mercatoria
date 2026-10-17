<?php

namespace App\Services\Cart;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Notification\UserNotificationService;
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
     * @param  Collection<int, array{cart: Cart, pricing: array<string, mixed>, subtotal: float}>  $lines
     * @return array{subtotal: float, discount_amount: float, shipping_cost: float, fee_marketplace: float, fee_qris: float, total: int, dp_amount: int}
     */
    public function calculateTotals(Collection $lines, string $platform, string $paymentScheme, string $paymentMethod): array
    {
        $subtotal = round($lines->sum(
            fn (array $line): float => (
                $line['pricing']['base_price'] + $line['pricing']['shipping']['ongkir_total_idr']
            ) * $line['cart']->qty,
        ), 2);
        $discount = round($lines->sum(
            fn (array $line): float => $line['pricing']['discount_amount'] * $line['cart']->qty,
        ), 2);
        $shipping = round($lines->sum(
            fn (array $line): float => $line['pricing']['shipping']['ongkir_total_idr'] * $line['cart']->qty,
        ), 2);
        $netSubtotal = max(0, $subtotal - $discount);
        $profitPercent = (float) setting('profit_percent', 11);
        $baseTotal = $netSubtotal * (1 + $profitPercent / 100);
        $feeMarketplace = match ([$platform, $paymentScheme]) {
            ['toco', 'fp'] => 1,
            ['toco', 'dp'] => 0,
            ['shopee', 'fp'] => 109,
            ['shopee', 'dp'] => round(max(0, $baseTotal * 0.5) * 0.25, 2),
            default => 0,
        };
        $feeQris = $paymentMethod === 'qris' && $baseTotal >= 500000
            ? round($baseTotal * 0.0003, 2)
            : 0;
        $total = (int) (ceil((($netSubtotal + $feeMarketplace + $feeQris) * (1 + $profitPercent / 100)) / 5000) * 5000);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'fee_marketplace' => $feeMarketplace,
            'fee_qris' => $feeQris,
            'total' => $total,
            'dp_amount' => $paymentScheme === 'dp' ? $total / 2 : (float) $total,
        ];
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
            $totals = $this->calculateTotals(
                $lines,
                $checkout['platform'],
                $checkout['payment_scheme'],
                $checkout['payment_method'],
            );
            $subtotal = $totals['subtotal'];
            $profitPercent = (float) setting('profit_percent', 11);
            $address = collect([
                $user->alamat_lengkap,
                $user->kecamatan,
                $user->kota,
                $user->provinsi,
                $user->kode_pos,
            ])->filter()->join(', ');

            $order = Order::create([
                'order_no' => 'MER-'.Str::upper(Str::random(5)).'-'.str_pad((string) (Order::max('id') + 1), 4, '0', STR_PAD_LEFT),
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
                'discount_amount' => $totals['discount_amount'],
                'fee_marketplace' => $totals['fee_marketplace'],
                'fee_qris' => $totals['fee_qris'],
                'shipping_cost' => $totals['shipping_cost'],
                'total' => $totals['total'],
                'frozen_rate_rmb_idr' => setting('rate_rmb_idr', 2200),
                'frozen_profit_percent' => $profitPercent,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => 'Pesanan dibuat oleh customer.',
                'changed_by' => $user->id,
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

            UserNotificationService::push(
                $user->id,
                NotificationType::ORDER,
                'Pesanan berhasil dibuat',
                "Pesanan {$order->order_no} menunggu pembayaran.",
                route('orders.show', $order),
                meta: ['order_id' => $order->id],
            );
            AdminNotification::create([
                'type' => 'new_order',
                'title' => 'Pesanan baru',
                'message' => "{$order->order_no} dari {$user->name}.",
                'link_url' => route('office.orders.show', $order),
                'meta' => ['order_id' => $order->id],
            ]);

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
