<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Support\PriceCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data untuk layout publik (resources/views/layouts/app.blade.php).
        // View composer ini pernah hilang karena file provider tertimpa kode
        // OrderController, sehingga $socialMedias tidak pernah dikirim ke
        // header/footer dan ikon sosial media tidak muncul sama sekali.
        View::composer('layouts.app', function ($view): void {
            // Query dibungkus try/catch: bila tabel belum ada (mis. sebelum
            // migrasi dijalankan di server), halaman tetap tampil tanpa ikon.
            try {
                $view->with([
                    // Hanya sosmed aktif & punya URL yang dirender di layout.
                    'socialMedias' => SocialMedia::query()
                        ->where('is_active', true)
                        ->whereNotNull('url')
                        ->orderBy('sort_order')
                        ->get(),

                    'navGames' => Game::orderBy('sort_order')
                        ->orderBy('name')
                        ->limit(12)
                        ->get(),

                    'promoBarText' => Setting::get('promo_bar_text'),
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'socialMedias' => collect(),
                    'navGames' => collect(),
                    'promoBarText' => null,
                ]);
            }

            // Total harga keranjang untuk badge di header (hanya user login).
            $cartTotal = 0;

            if (Auth::check()) {
                try {
                    $items = CartItem::with('variant.product.shippingTier')
                        ->where('user_id', Auth::id())
                        ->get();

                    $calculator = PriceCalculator::fromSettings();

                    foreach ($items as $item) {
                        if ($item->variant !== null && $item->variant->isAvailable()) {
                            $cartTotal += ($item->variant->sellingPrice($calculator) ?? 0) * $item->quantity;
                        }
                    }
                } catch (\Throwable $e) {
                    $cartTotal = 0;
                }
            }

            $view->with('cartTotal', $cartTotal);
        });
    }
}
