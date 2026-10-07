<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CoinLot;
use App\Models\Product;
use App\Models\ReferralClick;
use App\Models\Setting;
use App\Models\User;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): View|RedirectResponse|Response
    {
        if (! $request->user()) {
            return redirect()->route('login')->with('error', 'Silakan masuk untuk melihat produk.');
        }

        abort_unless($product->is_published, 404);

        $user = $request->user();

        // Track share click: kalau URL punya ?ref=KODE dan pembuka bukan pemiliknya
        // sendiri, catat klik + kasih reward ke pemilik kode. Gagal tracking
        // TIDAK boleh menjatuhkan halaman → bungkus try/catch di dalam method.
        $this->trackProductShareClick($request, $product, $user);

        try {
            if ($user !== null && $user->isSpammer() && $user->hasExhaustedQuota()) {
                return redirect()->route('account.show');
            }

            if ($user !== null && $user->isSpammer() && ! $user->consumeViewQuota($product)) {
                return redirect()->route('account.show');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($user !== null) {
            try {
                ActivityLog::record($user, 'view_product', $request, [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        foreach (['images', 'variants', 'shippingTier', 'game', 'developer'] as $relation) {
            try {
                $product->load($relation);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $calculator = PriceCalculator::fromSettings();
        } catch (\Throwable $e) {
            report($e);
            $calculator = new PriceCalculator(null, null, null, 11.0, 5000);
        }

        $description = $product->description
            ? \Illuminate\Support\Str::limit(strip_tags($product->description), 155)
            : 'Beli ' . $product->name . ' original dari Tmall. Kirim ke seluruh Indonesia.';

        $variantRows = [];
        foreach ($product->variants as $variant) {
            try {
                $variantRows[] = [
                    'id' => $variant->id,
                    'name' => (string) ($variant->name ?? 'Varian'),
                    'available' => $variant->isAvailable(),
                    'price' => $variant->sellingPrice($calculator),
                    'comparePrice' => $variant->comparePrice($calculator),
                    'imageUrl' => $variant->imageUrl(),
                ];
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $relatedProducts = Product::where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                if ($product->game_id) {
                    $query->orWhere('game_id', $product->game_id);
                }
                if ($product->developer_id) {
                    $query->orWhere('developer_id', $product->developer_id);
                }
            })
            ->where('is_published', true)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $relatedProducts->load(['images', 'variants', 'game', 'developer']);

        try {
            return response(view('products.show', [
                'user' => $user,
                'product' => $product,
                'calculator' => $calculator,
                'relatedProducts' => $relatedProducts,
                'variants' => collect($variantRows),
                'viewQuota' => Setting::integer('view_quota', 10),
                'title' => $product->name,
                'metaDescription' => $description,
                'metaImage' => $product->images->first()?->url(),
                'ogType' => 'product',
            ])->render());
        } catch (\Throwable $e) {
            report($e);

            return response()->view('errors.product-unavailable', [
                'title' => 'Produk sementara tidak tersedia',
                'productName' => $product->name ?? 'produk ini',
            ], 200);
        }
    }

    /**
     * Catat klik share produk dari ?ref=KODE.
     *
     * Aturan:
     *  - 1 kali reward per (referrer × produk × IP) seumur hidup
     *  - Pembuka link = si pemilik kode → tidak dihitung (biar gak farming sendiri)
     *  - Reward berbentuk CoinLot source='referral_click' dengan masa berlaku 3 bulan
     *  - Gagal tracking = log saja, tidak menjatuhkan halaman produk
     */
    private function trackProductShareClick(Request $request, Product $product, User $viewer): void
    {
        $refCode = strtoupper(trim((string) $request->query('ref', '')));
        if ($refCode === '') {
            return;
        }

        try {
            $referrer = User::where('referral_code', $refCode)->first();
        } catch (\Throwable $e) {
            return;
        }

        if (! $referrer || $referrer->id === $viewer->id) {
            return;
        }

        $ip = (string) $request->ip();
        if ($ip === '') {
            return;
        }

        // Fast-path cek duplikat (tanpa lock), hindari transaction untuk kasus umum.
        $exists = ReferralClick::where('referrer_id', $referrer->id)
            ->where('product_id', $product->id)
            ->where('ip_address', $ip)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            DB::transaction(function () use ($referrer, $product, $ip): void {
                // Lock untuk cegah race dari dua request paralel (IP sama).
                $dup = ReferralClick::where('referrer_id', $referrer->id)
                    ->where('product_id', $product->id)
                    ->where('ip_address', $ip)
                    ->lockForUpdate()
                    ->exists();

                if ($dup) {
                    return;
                }

                ReferralClick::create([
                    'referrer_id' => $referrer->id,
                    'product_id'  => $product->id,
                    'ip_address'  => $ip,
                    'clicked_on'  => now()->toDateString(),
                ]);

                $reward = (int) Setting::integer('referral_reward_click', 10);

                if ($reward > 0) {
                    CoinLot::create([
                        'user_id'    => $referrer->id,
                        'source'     => 'referral_click',
                        'amount'     => $reward,
                        'remaining'  => $reward,
                        'earned_at'  => now(),
                        'expires_at' => now()->addMonths(3),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Unique violation dari race = ok, bukan error fatal.
            Log::info('Share click tracking dilewati', [
                'referrer_id' => $referrer->id,
                'product_id'  => $product->id,
                'ip'          => $ip,
                'error'       => $e->getMessage(),
            ]);
        }
    }
} 
