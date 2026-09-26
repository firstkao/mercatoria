<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CoinLot;
use App\Models\CoinSpend;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Voucher;
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
            'voucher_code' => 'nullable|string'
        ]);

        $mp = Marketplace::findOrFail($request->input('marketplace_id'));
        $scheme = $request->input('payment_scheme');
        $calculator = PriceCalculator::fromSettings();

        if (!$calculator->isConfigured()) {
            return back()->withErrors(['Sistem sedang tidak dapat menghitung harga.']);
        }

        $subtotal = 0;
        $orderItemsData = [];
        
        foreach ($cartItems as $item) {
            $variant = $item->variant;
            if (!$variant->isAvailable()) {
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

        if ($discountType === 'coin') {
            $maxCoin = floor($subtotal * (Setting::integer('coin_max_use_percent', 5) / 100));
            $availableCoin = CoinLot::where('user_id', $user->id)->where('expires_at', '>', now())->sum('remaining');
            $discountIdr = min($maxCoin, $availableCoin);
            $usedCoinAmount = $discountIdr;
        } elseif ($discountType === 'voucher' && $request->filled('voucher_code')) {
            $voucher = Voucher::where('code', $request->input('voucher_code'))
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->first();

            if (!$voucher || $subtotal < $voucher->min_purchase_idr) {
                return back()->withErrors(['Voucher tidak valid atau minimal belanja tidak tercapai.']);
            }
            
            if ($voucher->discount_type === 'nominal') {
                $discountIdr = min($voucher->value, $subtotal);
            } else {
                $pctDiscount = floor($subtotal * $voucher->value / 100);
                $discountIdr = $voucher->max_discount_idr ? min($pctDiscount, $voucher->max_discount_idr) : $pctDiscount;
            }
        }

        $netTotal = $subtotal - $discountIdr;

        if ($scheme === 'FP') {
            $payNow = $netTotal;
            $remaining = 0;
            $mpFee = $mp->fp_fee_idr;
        } else {
            $payNow = floor($netTotal / 2);
            $remaining = $netTotal - $payNow;
            $mpFee = floor($remaining * $mp->dp_fee_percent / 100);
        }

        $coinEstimate = floor($netTotal * (Setting::integer('coin_earn_percent', 1) / 100));

        $order = DB::transaction(function() use ($user, $mp, $scheme, $subtotal, $discountType, $discountIdr, $netTotal, $payNow, $remaining, $mpFee, $coinEstimate, $calculator, $orderItemsData, $usedCoinAmount) {
            
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
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            if ($usedCoinAmount > 0) {
                $lots = CoinLot::where('user_id', $user->id)->where('remaining', '>', 0)->where('expires_at', '>', now())->orderBy('expires_at', 'asc')->get();
                $needed = $usedCoinAmount;
                foreach ($lots as $lot) {
                    if ($needed <= 0) break;
                    $take = min($lot->remaining, $needed);
                    $lot->decrement('remaining', $take);
                    CoinSpend::create(['coin_lot_id' => $lot->id, 'order_id' => $order->id, 'amount' => $take]);
                    $needed -= $take;
                }
            }

            $user->cartItems()->delete();
            ActivityLog::record($user, 'checkout', request(), ['order_number' => $order->order_number]);

            return $order;
        });

        // Nanti kita arahkan ke halaman Upload Bukti Transfer. Untuk sementara ke Akun Saya.
        return redirect()->route('account.show')->with('status', "Pesanan {$order->order_number} berhasil dibuat! Silakan upload bukti pembayaran.");
    }
}
