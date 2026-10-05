<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Support\PriceCalculator;
use App\Support\PublicSocialMedia;
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
        //
        // BUG FIX (500 "Undefined variable"): layout juga memakai $navDevelopers,
        // $footerPages, $footerProducts, $cartCount, $headerUnpaidCount,
        // $unreadNotifHeader, $hasLogo, meta SEO, tombol marketplace footer, dan
        // variabel WA widget — yang sebelumnya TIDAK PERNAH dikirim dari composer
        // ini. Pemanggilan ->isNotEmpty() pada variabel tak-terdefinisi melempar
        // Error dan menjatuhkan SEMUA halaman publik. Kini semua variabel layout
        // selalu tersedia, dengan fallback aman bila tabel belum ada.
        View::composer('layouts.app', function ($view): void {
            $data = [
                'socialMedias' => collect(),
                'marketplaceButtons' => collect(),
                'navGames' => collect(),
                'navDevelopers' => collect(),
                'footerPages' => collect(),
                'footerProducts' => collect(),
                'calculator' => null,
                'promoBarText' => null,
                'footerCopyright' => null,
                'footerPoweredBy' => null,
                'footerTocoUrl' => null,
                'footerTokopediaUrl' => null,
                'footerShopeeUrl' => null,
                'footerTiktokShopUrl' => null,
                'metaTitle' => null,
                'metaDescription' => null,
                'metaKeywords' => null,
                'metaImage' => null,
                'ogType' => null,
                'hasLogo' => false,
                'waWidgetEnabled' => false,
                'waWidgetGreeting' => null,
                'contactWhatsapp' => null,
                'cartCount' => 0,
                'cartTotal' => 0,
                'headerUnpaidCount' => 0,
                'unreadNotifHeader' => 0,
            ];

            // Satu-satunya kalkulator harga per request (dipakai footer produk).
            try {
                $data['calculator'] = PriceCalculator::fromSettings();
            } catch (\Throwable $e) {
                $data['calculator'] = null;
            }

            // Query dibungkus try/catch: bila tabel belum ada (mis. sebelum
            // migrasi dijalankan di server), halaman tetap tampil tanpa data.
            try {
                // BUG FIX (ikon sosmed tidak muncul walau data ada): filter
                // terpusat di PublicSocialMedia — is_active longgar (0/1/'0'/'1'),
                // URL null DAN string kosong dibuang, icon_key dinormalisasi.
                $socials = PublicSocialMedia::all();

                // Tombol marketplace footer (TOCO/TOKOPEDIA/SHOPEE/TIKTOK SHOP):
                // diambil dari baris social_media dengan icon_key khusus, supaya
                // admin cukup kelola satu tempat. Key settings lama tetap jadi
                // fallback bila barisnya belum ada.
                $data['marketplaceButtons'] = $socials
                    ->filter([PublicSocialMedia::class, 'isMarketplace'])
                    ->mapWithKeys(fn ($sm) => [PublicSocialMedia::normalizedIconKey($sm) => $sm]);
                $data['socialMedias'] = $socials->reject(
                    [PublicSocialMedia::class, 'isMarketplace']
                )->values();

                $data['navGames'] = Game::orderBy('sort_order')
                    ->orderBy('name')
                    ->limit(12)
                    ->get();

                $data['navDevelopers'] = Developer::orderBy('name')
                    ->limit(12)
                    ->get();

                // Halaman CMS yang dicentang "tampil di footer".
                $data['footerPages'] = Page::query()
                    ->where('is_published', true)
                    ->where('show_in_footer', true)
                    ->orderBy('sort_order')
                    ->get();

                // Produk terpopuler untuk kolom footer.
                $data['footerProducts'] = Product::query()
                    ->bestSellers()
                    ->with(['images', 'variants', 'shippingTier'])
                    ->take(4)
                    ->get();
            } catch (\Throwable $e) {
                // Pertahankan fallback kosong di $data.
            }

            // Setting teks (tabel key-value; aman walau barisnya belum ada).
            try {
                $data['promoBarText'] = Setting::get('promo_bar_text');
                $data['footerCopyright'] = Setting::get('footer_copyright');
                $data['footerPoweredBy'] = Setting::get('footer_powered_by');
                $data['metaTitle'] = Setting::get('meta_title');
                $data['metaDescription'] = Setting::get('meta_description');
                $data['metaKeywords'] = Setting::get('meta_keywords');
                $data['metaImage'] = Setting::get('meta_image');
                $data['waWidgetGreeting'] = Setting::get('wa_widget_greeting');
                $data['contactWhatsapp'] = Setting::get('contact_whatsapp');
                $data['waWidgetEnabled'] = Setting::get('wa_widget_enabled', '0') === '1';

                // URL marketplace footer: prioritas baris social_media, fallback
                // ke settings lama (footer_toco_url / footer_shopee_url).
                $data['footerTocoUrl'] = optional($data['marketplaceButtons']->get('toco'))->url
                    ?? Setting::get('footer_toco_url');
                $data['footerShopeeUrl'] = optional($data['marketplaceButtons']->get('shopee'))->url
                    ?? Setting::get('footer_shopee_url');
                $data['footerTokopediaUrl'] = optional($data['marketplaceButtons']->get('tokopedia'))->url;
                $data['footerTiktokShopUrl'] = optional($data['marketplaceButtons']->get('tiktokshop'))->url;
            } catch (\Throwable $e) {
                // Pertahankan fallback null di $data.
            }

            $data['hasLogo'] = file_exists(public_path('images/logo.png'));

            // Badge keranjang (jumlah item + total harga) & badge akun untuk
            // user login.
            if (Auth::check()) {
                try {
                    $items = CartItem::with('variant.product.shippingTier')
                        ->where('user_id', Auth::id())
                        ->get();

                    $cartCount = 0;
                    $cartTotal = 0;

                    foreach ($items as $item) {
                        if ($item->variant !== null && $item->variant->isAvailable()) {
                            $cartCount += $item->quantity;
                            $cartTotal += ($item->variant->sellingPrice($data['calculator'] ?? PriceCalculator::fromSettings()) ?? 0) * $item->quantity;
                        }
                    }

                    $data['cartCount'] = $cartCount;
                    $data['cartTotal'] = $cartTotal;
                } catch (\Throwable $e) {
                    // cartCount/cartTotal tetap 0.
                }

                try {
                    $data['headerUnpaidCount'] = Order::query()
                        ->where('user_id', Auth::id())
                        ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
                        ->count();
                } catch (\Throwable $e) {
                    $data['headerUnpaidCount'] = 0;
                }

                try {
                    $data['unreadNotifHeader'] = Auth::user()->unreadNotificationsCount();
                } catch (\Throwable $e) {
                    $data['unreadNotifHeader'] = 0;
                }
            }

            $view->with($data);
        });
    }
}
