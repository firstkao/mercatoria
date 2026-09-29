<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CartReminder;
use App\Models\CoinLot;
use App\Models\CoinSpend;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->with('variant.product.shippingTier')->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['Keranjang Anda kosong.']);
        }

        $request->validate([
            'payment_scheme' => 'required|in:DP,FP',
            'marketplace_id' => 'required|exists:marketplaces,id',
            'discount_type' => 'required|in:none,coin,voucher',
            'voucher_code' => 'nullable|string',
            'customer_note' => 'required|string|min:3|max:1000',
        ], [
            'customer_note.required' => 'Mohon isi catatan pembelian. Contoh: "bubble wrap extra" atau "no gift card".',
            'customer_note.min' => 'Catatan minimal 3 karakter.',
            'customer_note.max' => 'Catatan maksimal 1000 karakter.',
        ], [
            'customer_note' => 'catatan pembelian',
        ]);

        $mp = Marketplace::findOrFail($request->input('marketplace_id'));
        $scheme = $request->input('payment_scheme');
        $calculator = PriceCalculator::fromSettings();

        if (! $calculator->isConfigured()) {
            return back()->withErrors(['Sistem sedang tidak dapat menghitung harga.']);
        }

        $subtotal = 0;
        $orderItemsData = [];

        foreach ($cartItems as $item) {
            $variant = $item->variant;
            if (! $variant->isAvailable()) {
                return back()->withErrors(["Varian {$variant->name} sudah habis."]);
            }

            $priceIdr = $variant->sellingPrice($calculator);
            $subtotal += $priceIdr * $item->quantity;
            $tier = $variant->product->shippingTier;
            $cnShipping = $calculator->chinaShippingYuan((float) $variant->price_yuan, (float) $tier->fee_yuan, (float) $tier->min_purchase_yuan);

            $orderItemsData[] = [
                'product_variant_id' => $variant->id,
                'product_name_snapshot' => $variant->product->name,
                'variant_name_snapshot' => $variant->name,
                'price_yuan_snapshot' => $variant->price_yuan,
                'weight_grams_snapshot' => $variant->weight_grams,
                'cn_shipping_yuan_snapshot' => $cnShipping,
                'unit_price_idr' => $priceIdr,
                'quantity' => $item->quantity,
                'line_total_idr' => $priceIdr * $item->quantity,
            ];
        }

        $discountType = $request->input('discount_type');
        $discountIdr = 0;
        $usedCoinAmount = 0;
        $voucher = null;

        if ($discountType === 'coin') {
            $maxCoin = floor($subtotal * (Setting::integer('coin_max_use_percent', 5) / 100));
            $availableCoin = CoinLot::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->sum('remaining');
            $discountIdr = (int) min($maxCoin, $availableCoin);
            $usedCoinAmount = $discountIdr;
        } elseif ($discountType === 'voucher') {
            $code = trim((string) $request->input('voucher_code'));
            if ($code === '') {
                return back()->withErrors(['Masukkan kode voucher.']);
            }

            $voucher = Voucher::where('code', $code)->first();

            if (! $voucher || ! $voucher->isUsableBy($user)) {
                return back()->withErrors(['Voucher tidak valid, sudah kedaluwarsa, atau sudah dipakai.']);
            }

            if ($subtotal < $voucher->min_purchase_idr) {
                return back()->withErrors([
                    'Minimal belanja Rp' . number_format($voucher->min_purchase_idr, 0, ',', '.') . ' untuk pakai voucher ini.',
                ]);
            }

            $discountIdr = $voucher->discountFor($subtotal);
        }

        $netTotal = $subtotal - $discountIdr;

        if ($scheme === 'FP') {
            $payNow = $netTotal;
            $remaining = 0;
            $mpFee = $mp->fp_fee_idr;
        } else {
            $payNow = (int) floor($netTotal / 2);
            $remaining = $netTotal - $payNow;
            $mpFee = (int) floor($remaining * $mp->dp_fee_percent / 100);
        }

        $coinEstimate = (int) floor($netTotal * (Setting::integer('coin_earn_percent', 1) / 100));

        $order = DB::transaction(function () use (
            $user, $mp, $scheme, $subtotal, $discountType, $discountIdr,
            $netTotal, $payNow, $remaining, $mpFee, $coinEstimate,
            $calculator, $orderItemsData, $usedCoinAmount, $voucher, $request
        ) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'marketplace_id' => $mp->id,
                'status' => 'menunggu_pembayaran',
                'payment_scheme' => $scheme,
                'subtotal_idr' => $subtotal,
                'discount_type' => $discountType,
                'discount_idr' => $discountIdr,
                'total_idr' => $netTotal,
                'pay_now_idr' => $payNow,
                'remaining_idr' => $remaining,
                'marketplace_fee_idr' => $mpFee,
                'coin_estimate' => $coinEstimate,
                'pricing_snapshot' => $calculator->toArray(),
                'payment_deadline_at' => now()->addHours(Setting::integer('payment_deadline_hours', 24)),
                'customer_note' => $request->input('customer_note'),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Pakai koin
            if ($usedCoinAmount > 0) {
                $lots = CoinLot::where('user_id', $user->id)
                    ->where('remaining', '>', 0)
                    ->where('expires_at', '>', now())
                    ->orderBy('expires_at')
                    ->get();

                $needed = $usedCoinAmount;
                foreach ($lots as $lot) {
                    if ($needed <= 0) break;
                    $take = min($lot->remaining, $needed);
                    $lot->decrement('remaining', $take);
                    CoinSpend::create([
                        'coin_lot_id' => $lot->id,
                        'order_id' => $order->id,
                        'amount' => $take,
                    ]);
                    $needed -= $take;
                }
            }

            // Catat redemption voucher
            if ($voucher) {
                VoucherRedemption::create([
                    'voucher_id' => $voucher->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_idr' => $discountIdr,
                ]);
            }

            $user->cartItems()->delete();
            ActivityLog::record($user, 'checkout', request(), ['order_number' => $order->order_number]);

            // Hapus reminder cart (biar session baru reset)
            try {
                CartReminder::where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {}

            return $order;
        });

        return redirect()
            ->route('account.orders.show', $order->order_number)
            ->with('status', "Pesanan {$order->order_number} berhasil dibuat! Silakan upload bukti pembayaran.");
    }
}