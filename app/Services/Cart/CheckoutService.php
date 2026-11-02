<?php

namespace App\Services\Cart;

use App\Mail\OrderCreatedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSnapshot;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\SplitBoxSlot;
use App\Models\User;
use App\Models\UserVoucher;
use App\Services\Admin\AdminNotificationService;
use App\Services\Order\ShippingCalculatorService;
use App\Services\Order\SnapshotImageService;
use App\Services\SplitBox\SplitBoxService;
use App\Services\User\CoinWalletService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private ShippingCalculatorService $shipping,
        private CoinWalletService $coins,
    ) {}

    /**
     * Buat pesanan dari item keranjang terpilih (PRD 16.1 / 21).
     *
     * Satu transaksi DB: order + order_items + order_item_snapshots (freeze harga,
     * ongkir 3 komponen, berat, nama store) + potong koin / pakai voucher + bersihkan cart.
     * Gagal di tengah = semua batal (tidak ada order setengah jadi).
     *
     * @param  array<int, int>  $cartItemIds
     * @param  array{platform: string, scheme: string, payment_method: string, discount_type?: ?string, coin_amount?: ?int, user_voucher_id?: ?int, customer_note?: ?string}  $data
     */
    public function placeOrder(User $user, array $cartItemIds, array $data): Order
    {
        return DB::transaction(function () use ($user, $cartItemIds, $data) {
            $carts = Cart::query()
                ->with(['product.game.defaultStore', 'product.store', 'variant'])
                ->where('user_id', $user->id)
                ->whereIn('id', $cartItemIds)
                ->lockForUpdate()
                ->get();

            if ($carts->count() !== count($cartItemIds)) {
                throw ValidationException::withMessages([
                    'cart_item_ids' => 'Item keranjang tidak valid.',
                ]);
            }

            if ($carts->contains(fn (Cart $cart) => ! $cart->product?->is_active)) {
                throw ValidationException::withMessages([
                    'cart_item_ids' => 'Ada produk di keranjang yang sudah tidak tersedia. Hapus dari keranjang lalu coba lagi.',
                ]);
            }

            if (PaymentMethod::where('code', $data['payment_method'])->where('is_active', false)->exists()) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Metode pembayaran tidak tersedia.',
                ]);
            }

            $lines = $carts->map(fn (Cart $cart) => [
                'cart' => $cart,
                'pricing' => $this->shipping->calculateItemShipping($cart->product, $cart->variant),
            ]);

            $slotsByVariant = SplitBoxSlot::whereIn('variant_id', $carts->pluck('variant_id')->filter()->unique())
                ->get()
                ->unique('variant_id')
                ->keyBy('variant_id');

            $this->validateSplitBoxPairs($carts, $slotsByVariant);

            $subtotal = (float) $lines->sum(fn ($line) => $line['pricing']['final_price_bersih'] * $line['cart']->qty);

            [$discountType, $discountAmount, $coinSpent, $userVoucher] = $this->resolveDiscount($user, $data, $subtotal);

            $totals = $this->shipping->calculateCheckoutTotal(
                $lines->map(fn ($line) => [
                    'final_price_bersih' => $line['pricing']['final_price_bersih'],
                    'qty' => $line['cart']->qty,
                ])->all(),
                $discountAmount,
                $data['scheme'],
                $data['platform'],
                $data['payment_method'],
            );

            $order = Order::create([
                'user_id' => $user->id,
                'no_order' => generate_order_number(),
                'status' => OrderStatus::MenungguPembayaran->value,
                'scheme' => $data['scheme'],
                'platform' => $data['platform'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $totals['subtotal'],
                'discount_type' => $discountType,
                'discount_amount' => $totals['discount'],
                'coin_spent' => $coinSpent,
                'user_voucher_id' => $userVoucher?->id,
                'total' => $totals['total_barang'],
                'fee_qris' => $totals['fee_qris'],
                'tagihan_web' => $totals['tagihan_web'],
                'estimasi_co_marketplace' => $totals['estimasi_co_marketplace'] ?? null,
                'fee_co_marketplace' => $totals['fee_co_marketplace'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'payment_deadline_at' => now()->addHours(24),
            ]);

            foreach ($lines as $line) {
                $cart = $line['cart'];
                $splitSlot = $cart->variant_id ? $slotsByVariant->get($cart->variant_id) : null;

                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'variant_id' => $cart->variant_id,
                    'qty' => $cart->qty,
                    'bundle_group_id' => $cart->bundle_group_id,
                    'split_box_slot_id' => $splitSlot?->id,
                ]);

                OrderItemSnapshot::create([
                    'order_item_id' => $item->id,
                    'product_id_audit' => $cart->product_id,
                    'variant_id_audit' => $cart->variant_id,
                    'item_type' => $cart->variant_id ? 'variant' : 'single',
                    'bundle_group_id' => $cart->bundle_group_id,
                    'split_role' => $splitSlot?->split_role,
                    'product_name' => $cart->product->name,
                    'product_sku' => $cart->product->sku,
                    'variant_name' => $cart->variant?->name,
                    'variant_sku' => $cart->variant?->sku,
                    'base_price' => $line['pricing']['base_price_bersih'],
                    'discount_amount' => $line['pricing']['discount_amount'],
                    'final_price' => $line['pricing']['final_price_bersih'],
                    'qty' => $cart->qty,
                    'subtotal' => $line['pricing']['final_price_bersih'] * $cart->qty,
                    'product_image_path' => $this->copySnapshotImage($cart->product->image_path, $order->no_order),
                    'variant_image_path' => $this->copySnapshotImage($cart->variant?->image_path, $order->no_order),
                    'weight_gram' => $line['pricing']['weight_gram'],
                    'store_name' => $line['pricing']['store_name'],
                    'ongkir_lokal_cn_idr' => $line['pricing']['ongkir_lokal_cn_idr'],
                    'ongkir_cn_btm_idr' => $line['pricing']['ongkir_cn_btm_idr'],
                    'ongkir_btm_jkt_idr' => $line['pricing']['ongkir_btm_jkt_idr'],
                    'ongkir_total_idr' => $line['pricing']['ongkir_total_idr'],
                    'snapshot_at' => now(),
                ]);
            }

            if ($coinSpent > 0) {
                $this->coins->debitForOrder($user, $coinSpent, $order);
            }

            if ($userVoucher) {
                $this->markVoucherUsed($userVoucher, $order);
            }

            Cart::whereIn('id', $carts->pluck('id'))->delete();

            // Email konfirmasi order (PRD 37 — 3 mailer). Gate pause_customer_emails (AMANDEMEN).
            if (! (bool) setting('pause_customer_emails', false)) {
                Mail::to($user->email)->send(new OrderCreatedMail($order));
            }

            // PRD 41.1: bell admin — order baru (user checkout).
            app(AdminNotificationService::class)->notify(
                'order_baru',
                'Order baru #'.$order->no_order,
                '/office/orders/'.$order->no_order
            );

            return $order;
        });
    }

    /**
     * Validasi pair Split Box per bundle (PRD 49.2 / 49.9 — dijalankan ulang di checkout).
     *
     * @param  Collection<int, Cart>  $carts
     */
    private function validateSplitBoxPairs($carts, $slotsByVariant): void
    {
        $bundles = $carts->filter(fn (Cart $cart) => $cart->bundle_group_id)->groupBy('bundle_group_id');

        $splitBoxService = app(SplitBoxService::class);

        foreach ($bundles as $bundleId => $bundleCarts) {
            $bundleSize = Cart::where('user_id', $bundleCarts->first()->user_id)
                ->where('bundle_group_id', $bundleId)
                ->count();

            if ($bundleSize !== $bundleCarts->count()) {
                throw ValidationException::withMessages([
                    'cart_item_ids' => 'Item paket Split Box harus di-checkout bersama.',
                ]);
            }

            $lines = [];

            foreach ($bundleCarts as $cart) {
                $slot = $slotsByVariant->get($cart->variant_id);

                if ($slot) {
                    $lines[] = ['slot' => $slot, 'qty' => $cart->qty];
                }
            }

            if ($lines !== [] && ! $splitBoxService->validatePair($lines)) {
                throw ValidationException::withMessages([
                    'cart_item_ids' => 'Syarat pair Split Box belum terpenuhi (Hot butuh pasangan Normal).',
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: ?string, 1: float, 2: int, 3: ?UserVoucher}
     */
    private function resolveDiscount(User $user, array $data, float $subtotal): array
    {
        $type = $data['discount_type'] ?? null;

        if ($type === Order::DISCOUNT_COIN) {
            $amount = (int) ($data['coin_amount'] ?? 0);
            $max = (int) $this->shipping->maxCoinDiscount($subtotal);

            if ($amount <= 0 || $amount > $max) {
                throw ValidationException::withMessages([
                    'coin_amount' => "Maksimal koin yang bisa dipakai {$max} (5% dari subtotal).",
                ]);
            }

            return [Order::DISCOUNT_COIN, (float) $amount, $amount, null];
        }

        if ($type === Order::DISCOUNT_VOUCHER) {
            $userVoucher = UserVoucher::query()
                ->where('user_id', $user->id)
                ->where('id', $data['user_voucher_id'] ?? 0)
                ->lockForUpdate()
                ->first();

            if (! $userVoucher || ! $userVoucher->isUsable()) {
                throw ValidationException::withMessages([
                    'user_voucher_id' => 'Voucher tidak dapat digunakan.',
                ]);
            }

            if ($subtotal < (float) $userVoucher->voucher->min_belanja) {
                throw ValidationException::withMessages([
                    'user_voucher_id' => 'Subtotal belum memenuhi minimum belanja voucher.',
                ]);
            }

            return [Order::DISCOUNT_VOUCHER, (float) $userVoucher->voucher->nominal, 0, $userVoucher];
        }

        return [null, 0.0, 0, null];
    }

    private function markVoucherUsed(UserVoucher $userVoucher, Order $order): void
    {
        // WHERE status='active' + lockForUpdate: percobaan kedua tidak mengubah apa pun.
        $updated = UserVoucher::query()
            ->where('id', $userVoucher->id)
            ->where('status', UserVoucher::STATUS_ACTIVE)
            ->lockForUpdate()
            ->update([
                'status' => UserVoucher::STATUS_USED_AT_CHECKOUT,
                'order_id' => $order->id,
                'used_at' => now(),
            ]);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'user_voucher_id' => 'Voucher sudah dipakai.',
            ]);
        }
    }

    /**
     * PRD 46/13.6: snapshot gambar = COPY fisik ke order-snapshots/ (lihat SnapshotImageService).
     */
    private function copySnapshotImage(?string $imagePath, string $noOrder): ?string
    {
        return app(SnapshotImageService::class)->copy($imagePath, $noOrder);
    }
}
