<?php

namespace App\Services\Cart;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserVoucher;
use App\Services\Notification\ScheduledEmailService;
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
     * @return array{subtotal: float, discount_amount: float, coin_discount_amount: float, voucher_discount_amount: float, shipping_cost: float, fee_marketplace: float, fee_qris: float, total: int, dp_amount: int}
     */
    public function calculateTotals(
        Collection $lines,
        string $platform,
        string $paymentScheme,
        string $paymentMethod,
        int $coinBalance = 0,
        bool $useCoins = false,
        ?UserVoucher $userVoucher = null,
    ): array {
        $subtotal = round($lines->sum(
            fn (array $line): float => $line['pricing']['base_price'] * $line['cart']->qty,
        ), 2);
        $discount = round($lines->sum(
            fn (array $line): float => $line['pricing']['discount_amount'] * $line['cart']->qty,
        ), 2);
        $shipping = round($lines->sum(
            fn (array $line): float => $line['pricing']['shipping']['ongkir_total_idr'] * $line['cart']->qty,
        ), 2);
        $netSubtotal = max(0, $subtotal - $discount);
        if ($useCoins && $userVoucher) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Koin dan voucher tidak dapat digunakan bersamaan.',
            ]);
        }

        $voucherDiscount = $userVoucher ? $this->voucherDiscount($userVoucher, $netSubtotal) : 0;
        $coinDiscount = $useCoins
            ? min(max(0, (int) floor($netSubtotal * 0.05)), max(0, $coinBalance))
            : 0;
        $netSubtotal = max(0, $netSubtotal - $voucherDiscount - $coinDiscount);
        $baseTotal = $netSubtotal;
        $feeMarketplace = match ([$platform, $paymentScheme]) {
            ['toco', 'fp'] => (float) setting('fee_toco_fp', 1),
            ['toco', 'dp'] => (float) setting('fee_toco_dp', 0),
            ['shopee', 'fp'] => (float) setting('fee_shopee_fp', 109),
            ['shopee', 'dp'] => round(max(0, $baseTotal * 0.5) * ((float) setting('fee_shopee_dp_percent', 25) / 100), 2),
            default => 0,
        };
        $qrisBase = $netSubtotal + $feeMarketplace;
        $feeQris = $paymentMethod === 'qris' && $qrisBase >= (float) setting('qris_threshold', 500000)
            ? round($qrisBase * ((float) setting('fee_qris_percent', 0.03) / 100), 2)
            : 0;
        $total = (int) (ceil(($netSubtotal + $feeMarketplace + $feeQris) / 5000) * 5000);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => round($discount + $voucherDiscount + $coinDiscount, 2),
            'coin_discount_amount' => round($coinDiscount, 2),
            'voucher_discount_amount' => round($voucherDiscount, 2),
            'shipping_cost' => $shipping,
            'fee_marketplace' => $feeMarketplace,
            'fee_qris' => $feeQris,
            'total' => $total,
            'dp_amount' => $paymentScheme === 'dp' ? $total / 2 : (float) $total,
        ];
    }

    /**
     * @param  list<int>  $cartIds
     * @param  array{platform: string, payment_scheme: string, payment_method: string, customer_note?: ?string, use_coins?: bool, user_voucher_id?: ?int}  $checkout
     */
    public function placeOrder(User $user, array $cartIds, array $checkout): Order
    {
        if (! $user->no_hp || ! $user->alamat_lengkap || ! $user->kecamatan) {
            throw ValidationException::withMessages([
                'profile' => 'Lengkapi nomor HP dan alamat pengiriman sebelum membuat pesanan.',
            ]);
        }

        $order = DB::transaction(function () use ($user, $cartIds, $checkout): Order {
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
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
            $useCoins = (bool) ($checkout['use_coins'] ?? false);
            $coin = $useCoins
                ? Coin::query()->where('user_id', $user->id)->lockForUpdate()->first()
                : null;
            $userVoucher = null;
            if (! empty($checkout['user_voucher_id'])) {
                $userVoucher = $user->userVouchers()
                    ->where('status', 'active')
                    ->with('voucher')
                    ->lockForUpdate()
                    ->find($checkout['user_voucher_id']);
                if (! $userVoucher) {
                    throw ValidationException::withMessages([
                        'user_voucher_id' => 'Voucher tidak tersedia untuk akun ini.',
                    ]);
                }
            }
            $totals = $this->calculateTotals(
                $lines,
                $checkout['platform'],
                $checkout['payment_scheme'],
                $checkout['payment_method'],
                $coin?->balance ?? 0,
                $useCoins,
                $userVoucher,
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
                'customer_note' => filled($checkout['customer_note'] ?? null)
                    ? trim($checkout['customer_note'])
                    : $carts
                        ->filter(fn (Cart $cart): bool => filled($cart->customer_note))
                        ->map(fn (Cart $cart): string => $cart->product->name.': '.$cart->customer_note)
                        ->join(PHP_EOL),
                'subtotal' => $subtotal,
                'discount_amount' => $totals['discount_amount'],
                'coin_discount_amount' => $totals['coin_discount_amount'],
                'voucher_discount_amount' => $totals['voucher_discount_amount'],
                'user_voucher_id' => $userVoucher?->id,
                'fee_marketplace' => $totals['fee_marketplace'],
                'fee_qris' => $totals['fee_qris'],
                'shipping_cost' => $totals['shipping_cost'],
                'total' => $totals['total'],
                'frozen_rate_rmb_idr' => setting('rate_rmb_idr', 2200),
                'frozen_profit_percent' => $profitPercent,
            ]);

            if ($totals['coin_discount_amount'] > 0 && $coin) {
                $coin->decrement('balance', (int) $totals['coin_discount_amount']);
                $coin->refresh();
                CoinTransaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => -(int) $totals['coin_discount_amount'],
                    'source' => CoinSource::USED_AT_CHECKOUT->value,
                    'description' => "Koin digunakan untuk {$order->order_no}.",
                    'balance_after' => $coin->balance,
                    'status' => 'used',
                ]);
            }
            if ($userVoucher) {
                $userVoucher->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'order_id' => $order->id,
                ]);
            }

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

        app(ScheduledEmailService::class)->sendOrder($order, 'order_created', 'Pesanan berhasil dibuat');

        return $order;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    public function validatedCartIds(array $validated): array
    {
        return array_values(array_unique(array_map('intval', $validated['cart_ids'])));
    }

    private function voucherDiscount(UserVoucher $userVoucher, float $netSubtotal): float
    {
        $voucher = $userVoucher->voucher;
        if ($userVoucher->status !== 'active' || ! $voucher || ! $voucher->isValid()) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Voucher ini tidak aktif atau sudah kedaluwarsa.',
            ]);
        }
        if ($voucher->scope) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Voucher dengan scope khusus belum dapat digunakan pada checkout ini.',
            ]);
        }
        if ($netSubtotal < (float) $voucher->min_purchase) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Minimum belanja untuk voucher belum terpenuhi.',
            ]);
        }

        $usedCount = UserVoucher::query()
            ->where('user_id', $userVoucher->user_id)
            ->where('voucher_id', $voucher->id)
            ->where('status', 'used')
            ->count();
        if ($usedCount >= $voucher->per_user_limit) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Batas penggunaan voucher untuk akun ini sudah tercapai.',
            ]);
        }

        $amount = $voucher->type === 'percent'
            ? $netSubtotal * (float) $voucher->value / 100
            : (float) $voucher->value;

        if ($voucher->max_discount !== null) {
            $amount = min($amount, (float) $voucher->max_discount);
        }

        return round(min($amount, $netSubtotal), 2);
    }
}
