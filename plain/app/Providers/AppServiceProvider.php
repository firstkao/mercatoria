<?php

namespace App\Providers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ============================================================
        // DEFAULT SETTINGS
        // ============================================================
        if (Schema::hasTable('settings')) {
            $defaults = [
                'view_quota' => '10',
                'registration_limit' => '3',
                'spammer_ttl_days' => '30',
                'payment_deadline_hours' => '24',
                'customer_activity_retention_months' => '12',
                'coin_max_use_percent' => '5',
                'coin_earn_percent' => '1',
                'coin_expiry_months' => '12',
                'coin_expiry_reminder_days' => '7',
                'birthday_coin' => '1000',
                'customer_bonus_coin' => '1000',
                'birthday_voucher_nominal' => '20000',
                'birthday_voucher_min' => '100000',
                'birthday_voucher_valid_days' => '3',
                'referral_reward_referrer' => '5000',
                'referral_reward_referee' => '2000',
                'wa_widget_enabled' => '1',
                'wa_widget_label' => 'Chat CS',
                'wa_widget_greeting' => 'Halo, saya mau tanya tentang produk di MERCATORIA.',
                'cart_reminder_1_hours' => '4',
                'cart_reminder_2_hours' => '24',
                'best_seller_period_days' => '365',
                'best_seller_limit' => '8',
                'best_seller_min_sales' => '1',
                'maintenance_enabled' => '0',
                'maintenance_message' => 'Kami sedang melakukan pemeliharaan. Coba lagi sebentar lagi.',
                'maintenance_bypass_ips' => '',
                'email_verification_enabled' => '1',
                'wa_template_payment_reminder' => 'Halo {nama}, pesanan #{order_number} masih menunggu pembayaran. Batas waktu {deadline}. Yuk selesaikan ya 🙏',
                'wa_template_payment_received' => 'Halo {nama}, pembayaran pesanan #{order_number} sudah kami terima. Terima kasih! Pesanan segera diproses.',
                'wa_template_processing' => 'Halo {nama}, pesanan #{order_number} sedang kami proses. Update selanjutnya akan kami infokan ya.',
                'wa_template_shipped' => 'Halo {nama}, pesanan #{order_number} sudah dalam perjalanan ke Indonesia. Estimasi tiba ~45 hari setelah keluar dari gudang China.',
                'wa_template_completed' => 'Halo {nama}, pesanan #{order_number} sudah selesai. Terima kasih sudah berbelanja di MERCATORIA! 🎉',
                'footer_shopee_url' => '',
                'footer_tokopedia_url' => '',
                'footer_tiktok_shop_url' => '',
                'footer_copyright' => '',
                'footer_powered_by' => 'Powered by MERCATORIA',
            ];

            foreach ($defaults as $key => $value) {
                if (! Setting::where('key', $key)->exists()) {
                    Setting::create(['key' => $key, 'value' => $value]);
                }
            }
        }

        // ============================================================
        // OBSERVER
        // ============================================================
        if (class_exists(\App\Observers\ProductVariantObserver::class)) {
            \App\Models\ProductVariant::observe(\App\Observers\ProductVariantObserver::class);
        }

        // ============================================================
        // VIEW COMPOSER: layouts.app
        // ============================================================
        View::composer('layouts.app', function (ViewContract $view): void {
            // ===== Cart =====
            $cartCount = 0;
            $cartTotal = 0;

            if (auth()->check()) {
                try {
                    $cartRows = DB::table('cart_items')
                        ->join('product_variants', 'product_variants.id', '=', 'cart_items.product_variant_id')
                        ->join('products', 'products.id', '=', 'product_variants.product_id')
                        ->join('shipping_tiers', 'shipping_tiers.id', '=', 'products.shipping_tier_id')
                        ->where('cart_items.user_id', auth()->id())
                        ->select(
                            'cart_items.quantity',
                            'product_variants.price_yuan',
                            'product_variants.weight_grams',
                            'shipping_tiers.fee_yuan',
                            'shipping_tiers.min_purchase_yuan'
                        )
                        ->get();

                    $calculator = \App\Support\PriceCalculator::fromSettings();
                    if ($calculator->isConfigured()) {
                        foreach ($cartRows as $row) {
                            $price = $calculator->sellingPrice(
                                (float) $row->price_yuan,
                                (int) $row->weight_grams,
                                (float) $row->fee_yuan,
                                (float) $row->min_purchase_yuan
                            );
                            if ($price !== null) {
                                $cartCount += (int) $row->quantity;
                                $cartTotal += $price * (int) $row->quantity;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // ===== Sale =====
            $hasActiveSale = false;
            try {
                $hasActiveSale = Product::query()->published()->onSale()->exists();
            } catch (\Throwable $e) {}

            // ===== Nav: Games (query langsung, tanpa cache) =====
            $navGames = collect();
            try {
                if (Schema::hasTable('games')) {
                    $navGames = Game::query()
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get(['id', 'name', 'slug', 'image_path']);
                }
            } catch (\Throwable $e) {}

            // ===== Nav: Developers (query langsung, tanpa cache) =====
            $navDevelopers = collect();
            try {
                if (Schema::hasTable('developers')) {
                    $navDevelopers = Developer::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'slug']);
                }
            } catch (\Throwable $e) {}

            // ===== Footer pages (query langsung, tanpa cache) =====
            $footerPages = collect();
            try {
                if (Schema::hasTable('pages')) {
                    $footerPages = \App\Models\Page::query()
                        ->where('show_in_footer', true)
                        ->where('is_published', true)
                        ->orderBy('sort_order')
                        ->get(['id', 'title', 'slug']);
                }
            } catch (\Throwable $e) {}

            $view->with([
                'promoBarText' => Setting::get('promo_bar_text'),
                'hasLogo' => file_exists(public_path('images/logo.png')),
                'contactEmail' => Setting::get('contact_email'),
                'contactWhatsapp' => Setting::get('contact_whatsapp'),
                'contactHours' => Setting::get('contact_hours'),
                'storeAddress' => Setting::get('store_address'),
                'socialInstagram' => Setting::get('social_instagram'),
                'socialTiktok' => Setting::get('social_tiktok'),
                'socialFacebook' => Setting::get('social_facebook'),
                'socialX' => Setting::get('social_x'),
                'metaTitle' => Setting::get('meta_title', 'MERCATORIA — Merchandise Game Original dari Tmall'),
                'metaDescription' => Setting::get('meta_description', 'Group Order Manager untuk merchandise game original dari Tmall & Taobao. Kirim ke seluruh Indonesia.'),
                'metaKeywords' => Setting::get('meta_keywords'),
                'metaImage' => Setting::get('meta_image'),
                'waWidgetEnabled' => Setting::get('wa_widget_enabled', '1') !== '0',
                'waWidgetLabel' => Setting::get('wa_widget_label', 'Chat CS'),
                'waWidgetGreeting' => Setting::get('wa_widget_greeting', 'Halo, saya mau tanya tentang produk di MERCATORIA.'),
                'hasActiveSale' => $hasActiveSale,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'navGames' => $navGames,
                'navDevelopers' => $navDevelopers,
                'footerPages' => $footerPages,
                'footerShopeeUrl' => Setting::get('footer_shopee_url'),
                'footerTokopediaUrl' => Setting::get('footer_tokopedia_url'),
                'footerTiktokShopUrl' => Setting::get('footer_tiktok_shop_url'),
                'footerCopyright' => Setting::get('footer_copyright'),
                'footerPoweredBy' => Setting::get('footer_powered_by', 'Powered by MERCATORIA'),
            ]);
        });

        // ============================================================
        // VIEW COMPOSER: admin.layouts.app
        // ============================================================
        View::composer('admin.layouts.app', function (ViewContract $view): void {
            if (! auth('admin')->check()) {
                $view->with('adminBadges', []);
                return;
            }

            $badges = [
                'pendingProofs' => 0,
                'pendingOrders' => 0,
                'pendingResellers' => 0,
                'unreadContact' => 0,
            ];

            try {
                $badges['pendingProofs'] = PaymentProof::query()->where('status', 'pending')->count();
            } catch (\Throwable $e) {}

            try {
                $badges['pendingOrders'] = Order::query()
                    ->whereIn('status', ['menunggu_pembayaran', 'ditahan'])
                    ->count();
            } catch (\Throwable $e) {}

            try {
                if (Schema::hasTable('reseller_applications')) {
                    $badges['pendingResellers'] = DB::table('reseller_applications')
                        ->where('status', 'pending')
                        ->count();
                }
            } catch (\Throwable $e) {}

            try {
                if (class_exists(\App\Models\ContactMessage::class)) {
                    $badges['unreadContact'] = \App\Models\ContactMessage::query()
                        ->whereNull('read_at')
                        ->count();
                }
            } catch (\Throwable $e) {}

            $view->with('adminBadges', $badges);
        });
    }
}