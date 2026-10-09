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
use App\Support\OrderNumberGenerator;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'marketplace_id' => ['required', Rule::exists('marketplaces', 'id')->where('is_active', true)],
            'discount_type' => 'required|in:none,coin,voucher',
            'voucher_code' => 'nullable|string',
            'customer_note' => 'nullable|string|max:1000',
        ], [
            'customer_note.max' => 'Catatan maksimal 1000 karakter.',
        ], [
            'customer_note' => 'catatan pembelian',
        ]);

        // Snapshot isi keranjang; dicek ulang di dalam transaksi (setelah lock) untuk
        // mencegah double-submit / keranjang yang berubah di tengah jalan.
        $cartSignature = $cartItems->map(fn ($i) => $i->id . ':' . $i->quantity)->sort()->values()->implode(',');

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

            // ✅ BUG FIX: sellingPrice() mengembalikan null kalau price_yuan kosong
            // (mis. baris varian hasil SQL dump lama). Dulu `null * quantity`
            // di-PHP jadi 0 → subtotal order bisa berakhir 0 rupiah.
            if ($priceIdr === null) {
                return back()->withErrors(["Varian {$variant->name} belum punya harga. Hubungi admin sebelum checkout."]);
            }

            // ✅ BUG FIX: shippingTier bisa null kalau tier-nya sudah dihapus admin.
            // Dulu `(float) $tier->fee_yuan` langsung dipanggil → 500
            // "Attempting to read property on null" di tengah checkout.
            $tier = $variant->product->shippingTier;
            $tierFeeYuan = $tier !== null ? (float) $tier->fee_yuan : 0.0;
            $tierMinPurchaseYuan = $tier !== null ? (float) $tier->min_purchase_yuan : 0.0;

            $subtotal += $priceIdr * $item->quantity;
            $cnShipping = $calculator->chinaShippingYuan(
                (float) ($variant->price_yuan ?? 0),
                $tierFeeYuan,
                $tierMinPurchaseYuan,
            );

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
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
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
            $mpFee = (int) $mp->fp_fee_idr;
            // BUG FIX: fee marketplace (flat) sekarang ikut ditagih ke pembeli,
            // bukan sekadar disimpan ke marketplace_fee_idr.
            $payNow = $netTotal + $mpFee;
            $remaining = 0;
        } else {
            $payNow = (int) floor($netTotal / 2);
            $remainingBase = $netTotal - $payNow;
            // Fee persentase DP dihitung dari sisa pelunasan, lalu DITAMBAHKAN ke
            // sisa pelunasan (konsisten dengan label frontend "Sisa Pelunasan
            // (Termasuk Biaya Admin)").
            $mpFee = (int) floor($remainingBase * $mp->dp_fee_percent / 100);
            $remaining = $remainingBase + $mpFee;
        }

        $coinEstimate = (int) floor($netTotal * (Setting::integer('coin_earn_percent', 1) / 100));

        $order = DB::transaction(function () use (
            $user, $mp, $scheme, $subtotal, $discountType, $discountIdr,
            $netTotal, $payNow, $remaining, $mpFee, $coinEstimate,
            $calculator, $orderItemsData, $usedCoinAmount, $voucher, $request, $cartSignature
        ) {
            // 1) Serialisasi semua checkout milik user ini. Request kedua (double-click / paralel)
            //    akan menunggu di sini sampai request pertama commit.
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            // 2) Keranjang harus masih persis sama. Kalau request pertama sudah commit,
            //    keranjang sudah kosong -> request kedua ditolak, bukan bikin order ganda.
            $currentSignature = $user->cartItems()->get(['id', 'quantity'])
                ->map(fn ($i) => $i->id . ':' . $i->quantity)->sort()->values()->implode(',');

            if ($currentSignature !== $cartSignature) {
                throw ValidationException::withMessages([
                    'checkout' => 'Keranjang berubah atau pesanan sudah dibuat. Silakan cek daftar pesanan atau keranjang Anda.',
                ]);
            }

            // 3) Voucher: kunci barisnya lalu validasi ulang, supaya usage_limit /
            //    per_user_limit tidak bisa ditembus request paralel.
            if ($voucher) {
                $lockedVoucher = Voucher::whereKey($voucher->id)->lockForUpdate()->first();

                if (! $lockedVoucher || ! $lockedVoucher->isUsableBy($user)) {
                    throw ValidationException::withMessages([
                        'voucher_code' => 'Voucher tidak valid, sudah kedaluwarsa, atau sudah dipakai.',
                    ]);
                }
            }

            // =========================================================
            // Order number: format MER-YYMM-NNNN-XXXXX.
            // Contoh: MER-2610-0047-K3A7X
            //
            // - YYMM  : bulan order (untuk sorting & tracking marketplace)
            // - NNNN  : urutan per bulan, di-lock via tabel order_sequences
            // - XXXXX : 5 char random (A-Z minus I/O + 0-9 minus 0/1)
            //           untuk mencegah enumerasi kalau ada IDOR di masa depan
            //
            // Wajib dipanggil DI DALAM transaksi supaya:
            //   1. lockForUpdate di order_sequences efektif
            //   2. rollback outer → increment counter ikut rollback
            //      (nomor berikutnya tidak lompat sia-sia)
            // =========================================================
            $orderNumber = OrderNumberGenerator::generate();

            $order = Order::create([
                // ===== field existing =====
                'user_id'             => $user->id,
                'order_number'        => $orderNumber,
                'status'              => 'menunggu_pembayaran',
                'subtotal_idr'        => $subtotal,
                'discount_idr'        => $discountIdr,      // ✅ FIX
                'total_idr'           => $netTotal,         // ✅ FIX
                'pay_now_idr'         => $payNow,
                'remaining_idr'       => $remaining,
                'coin_estimate'       => $coinEstimate,
                'payment_scheme'      => $scheme,
                'marketplace_id'      => $mp->id,           // ✅ FIX
                'marketplace_fee_idr' => $mpFee,            // ✅ FIX
                'customer_note'       => $request->input('customer_note'),

                // ✅ TAMBAH INI
                'pricing_snapshot'    => json_encode([
                    'yuan_rate'            => Setting::get('yuan_rate'),
                    'markup_percent'       => Setting::get('markup_percent'),
                    'coin_earn_percent'    => Setting::get('coin_earn_percent'),
                    'coin_max_use_percent' => Setting::get('coin_max_use_percent'),
                    'calculated_at'        => now()->toIso8601String(),
                ]),

                // ===== (2) PENERIMA & PENGIRIMAN =====
                'recipient_name'       => $request->input('recipient_name', $user->full_name),
                'recipient_phone'      => $request->input('recipient_phone', $user->phone),
                'recipient_email'      => $request->input('recipient_email', $user->email),
                'shipping_address'     => $request->input('shipping_address', $user->address),
                'shipping_city'        => $request->input('shipping_city'),
                'shipping_province'    => $request->input('shipping_province'),
                'shipping_postal_code' => $request->input('shipping_postal_code'),
                'shipping_note'        => $request->input('shipping_note'),

                // ===== (4) TRACKING KUNJUNGAN =====
                'source'             => $this->detectSource($request),
                'device_type'        => $this->detectDevice($request->userAgent()),
                'landing_page'       => session('landing_page') ?? url()->previous(),
                'referrer'           => $request->header('referer'),
                'session_page_views' => (int) session('page_views', 0),
                'user_agent'         => mb_substr((string) $request->userAgent(), 0, 500),
                'ip_address'         => $request->ip(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Pakai koin
            if ($usedCoinAmount > 0) {
                $lots = CoinLot::where('user_id', $user->id)
                    ->where('remaining', '>', 0)
                    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                    ->orderByRaw('expires_at IS NULL ASC, expires_at ASC')
                    ->lockForUpdate()
                    ->get();

                if ($lots->sum('remaining') < $usedCoinAmount) {
                    throw ValidationException::withMessages([
                        'coin' => 'Saldo koin Anda berubah. Silakan ulangi checkout.',
                    ]);
                }

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
            } catch (\Throwable $e) {
                Log::warning(
                    'Gagal menghapus reminder cart setelah checkout: ' . $e->getMessage(),
                    ['user_id' => $user->id, 'order_id' => $order->id ?? null]
                );
            }

            return $order;
        }, 3);

        return redirect()
            ->route('account.orders.show', $order->order_number)
            ->with('status', "Pesanan {$order->order_number} berhasil dibuat! Silakan upload bukti pembayaran.");
    }

    // ============================================================
    // HELPER METHODS — di luar store(), sejajar dengan store()
    // ============================================================

    /**
     * Deteksi asal kunjungan dari UTM / session / referer.
     */
    private function detectSource(Request $request): string
    {
        // 1. UTM parameter
        if ($utm = $request->input('utm_source') ?? session('utm_source')) {
            return strtolower(substr($utm, 0, 50));
        }

        // 2. HTTP referer
        $referer = strtolower((string) $request->header('referer'));

        if ($referer === '') {
            return 'direct';
        }

        $map = [
            'google'    => ['google.', 'googleapis'],
            'instagram' => ['instagram.', 'ig.me'],
            'tiktok'    => ['tiktok.', 'vt.tiktok'],
            'facebook'  => ['facebook.', 'fb.me', 'fb.com'],
            'twitter'   => ['twitter.', 't.co', 'x.com'],
            'shopee'    => ['shopee.'],
            'tokopedia' => ['tokopedia.'],
            'whatsapp'  => ['wa.me', 'whatsapp.'],
            'youtube'   => ['youtube.', 'youtu.be'],
        ];

        foreach ($map as $source => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($referer, $needle)) {
                    return $source;
                }
            }
        }

        return 'referral';
    }

    /**
     * Deteksi jenis perangkat dari User-Agent.
     */
    private function detectDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        $ua = strtolower($userAgent);

        if (preg_match('/ipad|tablet|playbook|silk/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
