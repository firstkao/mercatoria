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
                'referral_reward_click' => '10', // +koin per klik link; anti-spam: 1x per IP unik SELAMANYA (bukan per hari)
                'referral_reward_referee' => '0', // referee tidak dapat bonus tambahan (hanya welcome + cashback order)
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

            // Sekali saja: kebijakan baru -> referee tidak dapat bonus tambahan referral.
            // (Yang diundang cukup welcome/first-order bonus + cashback transaksi biasa.)
            if ((string) Setting::where('key', 'referral_reward_referee')->value('value') === '2000') {
                Setting::where('key', 'referral_reward_referee')->update(['value' => '0']);
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

            // ===== FIX 3: Footer "Produk Terpopuler" — REAL DATA, no dummy =====
            // Kriteria A: produk paling banyak dilihat (activity_logs action
            // view_product, product_id tersimpan di kolom JSON `metadata`).
            // Kriteria B: produk terlaris (order_items x orders status selesai).
            // Kalau keduanya ada -> random pilih salah satu; kalau hanya satu
            // yang ada -> pakai yang itu; keduanya kosong -> collection kosong
            // dan view tidak menampilkan apa-apa. Semua dibungkus try/catch
            // supaya footer tidak pernah menjatuhkan halaman manapun (bug 500).
            $footerProducts = collect();
            try {
                $mostViewed = collect();
                if (Schema::hasTable('activity_logs')) {
                    $mostViewed = \App\Models\ActivityLog::query()
                        ->where('action', 'view_product')
                        ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.product_id')) as product_id, COUNT(*) as aggregate_count")
                        ->groupBy('product_id')
                        ->orderByDesc('aggregate_count')
                        ->limit(6)
                        ->pluck('product_id')
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->values();
                }

                $bestSelling = collect();
                if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
                    $bestSelling = \App\Models\OrderItem::query()
                        ->from('order_items')
                        ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                        ->join('orders', 'orders.id', '=', 'order_items.order_id')
                        ->whereIn('orders.status', ['selesai', 'sampai_wh_indonesia', 'bea_cukai', 'dikirim_ke_indonesia'])
                        ->selectRaw('product_variants.product_id as product_id, SUM(order_items.quantity) as aggregate_count')
                        ->groupBy('product_variants.product_id')
                        ->orderByDesc('aggregate_count')
                        ->limit(6)
                        ->pluck('product_id')
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->values();
                }

                $selectedIds = collect();
                if ($mostViewed->isNotEmpty() && $bestSelling->isNotEmpty()) {
                    $selectedIds = random_int(0, 1) ? $mostViewed : $bestSelling;
                } elseif ($mostViewed->isNotEmpty()) {
                    $selectedIds = $mostViewed;
                } elseif ($bestSelling->isNotEmpty()) {
                    $selectedIds = $bestSelling;
                }

                if ($selectedIds->isNotEmpty() && Schema::hasTable('products')) {
                    $footerProducts = Product::query()
                        ->whereIn('id', $selectedIds)
                        ->where('is_published', true)
                        ->with(['images', 'variants'])
                        ->get();
                }
            } catch (\Throwable $e) {}

            // ===== Badge tagihan menunggu di header (akun) =====
            // Dihitung di composer + dishare secara global supaya TIDAK ada
            // risiko "Undefined variable" dari compiled view basi maupun saat
            // render halaman error publik. Pakai auth()->user()?->id + cast
            // (int): di beberapa setup (session/cache guard) auth()->id() bisa
            // mengembalikan string yang ditolak MySQL 8. Try/catch: halaman
            // publik tidak boleh ikut mati bila tabel orders bermasalah —
            // badge cukup diam (0), bukan crash.
            $headerUnpaidCount = 0;
            try {
                $__headerUserId = (int) (auth()->user()?->id ?? 0);
                if ($__headerUserId > 0) {
                    $headerUnpaidCount = Order::where('user_id', $__headerUserId)
                        ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
                        ->count();
                }
            } catch (\Throwable $e) {}

            // ===== Badge notifikasi unread di header (lonceng) =====
            // SAMA SEKALI jangan dihitung via blok PHP inline di blade: bila
            // compiled view basi / ter-pull parsial, Blade tetap menghasilkan
            // "Undefined variable" saat render (persis bug 500 sebelumnya).
            // Dihitung di sini, dishare global + dikirim eksplisit lewat with().
            $unreadNotifHeader = 0;
            try {
                if ($__headerUserId > 0) {
                    $unreadNotifHeader = \App\Models\UserNotification::query()
                        ->where('user_id', $__headerUserId)
                        ->whereNull('read_at')
                        ->count();
                }
            } catch (\Throwable $e) {}

            // Safety net untuk render DI LUAR composer (mis. errors/500.blade.php
            // extends layouts.app tapi dirender lewat Handler tanpa memicu
            // composer). Tanpa blok ini, pemakaian variabel di blade bisa
            // melempar "Undefined variable" dan mengubah error page menjadi
            // 500 kedua. Hanya diisi kalau belum pernah di-share.
            try {
                $__shared = \Illuminate\Support\Facades\View::getShared();
                foreach (['headerUnpaidCount' => $headerUnpaidCount, 'unreadNotifHeader' => $unreadNotifHeader, 'footerProducts' => $footerProducts] as $__k => $__v) {
                    if (! array_key_exists($__k, $__shared)) {
                        view()->share($__k, $__v);
                    }
                }
            } catch (\Throwable $e) {}

            $view->with([
                'promoBarText' => Setting::get('promo_bar_text'),
                'hasLogo' => file_exists(public_path('images/logo.png')),
                'contactEmail' => Setting::get('contact_email'),
                'contactWhatsapp' => Setting::get('contact_whatsapp'),
                'contactHours' => Setting::get('contact_hours'),
                'storeAddress' => Setting::get('store_address'),
                // SOSIAL MEDIA DINAMIS: sumber tunggal = tabel social_media (kelola di Admin > Pengaturan > Umum).
                // Variabel lama socialInstagram/socialTiktok/socialFacebook/socialX DIHAPUS — diganti $socialMedias.
                'socialMedias' => \App\Models\SocialMedia::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(),
                'metaTitle' => Setting::get('meta_title', 'MERCATORIA — Merchandise Game Original dari Tmall'),
                'metaDescription' => Setting::get('meta_description', 'Group Order Manager untuk merchandise game original dari Tmall & Taobao. Kirim ke seluruh Indonesia.'),
                'metaKeywords' => Setting::get('meta_keywords'),
                'metaImage' => Setting::get('meta_image'),
                'waWidgetEnabled' => Setting::get('wa_widget_enabled', '1') !== '0',
                'waWidgetGreeting' => Setting::get('wa_widget_greeting', 'Halo, saya mau tanya tentang produk di MERCATORIA.'),
                'hasActiveSale' => $hasActiveSale,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'headerUnpaidCount' => $headerUnpaidCount,
                'unreadNotifHeader' => $unreadNotifHeader,
                'navGames' => $navGames,
                'navDevelopers' => $navDevelopers,
                'footerPages' => $footerPages,
                'footerProducts' => $footerProducts,
                'footerShopeeUrl' => Setting::get('footer_shopee_url'),
                'footerTocoUrl' => Setting::get('footer_toco_url', Setting::get('footer_tokopedia_url')),
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

            // BUG 5 STRICT MODE: badge "Pesan kontak" dihapus dari sidebar
            // karena panel admin-nya sudah tidak ada (pesan tetap masuk DB+email).

            $view->with('adminBadges', $badges);
        });
    }
}