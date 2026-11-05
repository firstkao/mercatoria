<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\UserNotification;
use App\Services\Admin\AdminNotificationService;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
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
        // ═══ SEO & JSON-LD default global (fallback semua halaman) ═══
        // Controller yang butuh custom tinggal override via compact('seo') / compact('jsonLd').
        View::share('seo', [
            'title' => config('app.name', 'MERCATORIA'),
            'description' => '',
            'image' => '',
            'url' => '',
            'canonical' => '',
            'robots' => 'index, follow',
            'type' => 'website',
            'site_name' => config('app.name', 'MERCATORIA'),
            'locale' => 'id_ID',
            'keywords' => '',
        ]);
        View::share('jsonLd', []);

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute((int) setting('login_rate_limit_per_minute', 5))->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Navbar dropdown Game / Developer → route katalog (PRD 7.2) + bell (PRD 43.2) + keranjang.
        View::composer(['layouts.app', 'layouts.guest', 'frontend.home.index', 'frontend.home.bot', 'frontend.profile.show', 'frontend.partials._navbar'], function ($view): void {
            $user = auth()->user();

            $cartTotal = 0;
            $cartCount = 0;

            if ($user) {
                [$cartTotal, $cartCount] = self::perRequest('nav_cart', fn () => self::cartSummary($user));
            }

            $view->with('navGames', $this->activeGames())
                ->with('navDevelopers', $this->activeDevelopers())
                ->with('bellNotifications', $user
                    ? self::perRequest('nav_bell', fn () => UserNotification::where('user_id', $user->id)->orderByDesc('id')->limit(10)->get())
                    : collect())
                ->with('bellUnreadCount', $user
                    ? self::perRequest('nav_unread', fn () => (int) UserNotification::where('user_id', $user->id)->where('is_read', false)->count())
                    : 0)
                ->with('cartTotal', $cartTotal)
                ->with('cartCount', $cartCount);
        });

        // Footer "Produk Terpopuler" (Design Spec 4.4.5) — {image, url, name, price}.
        View::composer(['layouts.app', 'layouts.guest', 'frontend.home.index', 'frontend.home.bot', 'frontend.partials._footer'], function ($view): void {
            $view->with('footerTopProducts', self::perRequest('footer_top_products', function (): Collection {
                $shipping = app(ShippingCalculatorService::class);

                return Product::where('is_active', true)
                    ->with(['game.defaultStore', 'store'])
                    ->orderByDesc('sold_count')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get()
                    ->map(fn (Product $product) => [
                        'image' => $product->image_path ? Storage::url($product->image_path) : null,
                        'url' => $product->game ? '/'.$product->game->slug.'/'.$product->slug : null,
                        'name' => $product->name,
                        'price' => $shipping->calculateItemShipping($product)['final_price_bersih'],
                    ])
                    ->values();
            }));
        });

        // Footer "Game" & "Developer" (pola $navGames) untuk layout yang memakai footer.
        View::composer(['layouts.app', 'layouts.guest', 'frontend.home.index', 'frontend.home.bot', 'frontend.partials._footer'], function ($view): void {
            $view->with('footerGames', $this->activeGames())
                ->with('footerDevelopers', $this->activeDevelopers());
        });

        // Bell notifikasi admin (PRD 41.1) — 10 terbaru + unread count untuk topbar.
        View::composer('admin.layouts.admin', function ($view): void {
            $data = app(AdminNotificationService::class)->topbarData();

            $view->with('adminBellNotifications', $data['notifications'])
                ->with('adminBellUnreadCount', $data['unread'])
                // PRD 24.5: indikator topbar kill-switch email customer (PR UI).
                ->with('customerEmailsPaused', (bool) setting('pause_customer_emails', false));
        });
    }

    /**
     * Composer menempel di beberapa view (layout + halaman); hitung sekali per request.
     */
    public static function perRequest(string $key, callable $resolve): mixed
    {
        $attributes = request()->attributes;

        if (! $attributes->has($key)) {
            $attributes->set($key, $resolve());
        }

        return $attributes->get($key);
    }

    /**
     * Total harga bersih + jumlah item keranjang user.
     * Dipakai composer navbar (page load) dan CartController@store (respons JSON AJAX) agar angkanya selalu sama.
     *
     * @return array{0: int|float, 1: int}
     */
    public static function cartSummary(\App\Models\User $user): array
    {
        $shipping = app(ShippingCalculatorService::class);
        $total = 0;
        $count = 0;

        foreach (Cart::with(['product.game.defaultStore', 'product.store', 'variant'])->where('user_id', $user->id)->get() as $item) {
            $pricing = $shipping->calculateItemShipping($item->product, $item->variant);
            $total += $pricing['final_price_bersih'] * $item->qty;
            $count += $item->qty;
        }

        return [$total, $count];
    }

    private function activeGames(): Collection
    {
        return self::perRequest('nav_games', function (): Collection {
            // Simpan atribut polos (bukan object model) agar payload cache tidak rusak
            // jadi __PHP_Incomplete_Class bila class pindah/berubah nama antar deploy.
            $rows = Cache::get(Game::NAV_CACHE_KEY);

            if (! is_array($rows)) {
                Cache::forget(Game::NAV_CACHE_KEY);
                $rows = Game::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])->map->getAttributes()->all();
                Cache::put(Game::NAV_CACHE_KEY, $rows, 3600);
            }

            return Game::hydrate($rows);
        });
    }

    private function activeDevelopers(): Collection
    {
        return self::perRequest('nav_developers', function (): Collection {
            $rows = Cache::get(Developer::NAV_CACHE_KEY);

            if (! is_array($rows)) {
                Cache::forget(Developer::NAV_CACHE_KEY);
                $rows = Developer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])->map->getAttributes()->all();
                Cache::put(Developer::NAV_CACHE_KEY, $rows, 3600);
            }

            return Developer::hydrate($rows);
        });
    }
}
