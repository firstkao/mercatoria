<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Struktur:
|   1. Publik          — bisa diakses siapa saja
|   2. Guest           — login/register/reset password
|   3. Guest Admin     — login /office
|   4. Admin           — area /office/* (auth:admin)
|   5. Authenticated   — area pembeli (auth)
|   6. Stream Bukti    — /payment-proof/* (owner atau admin)
|   7. Legal & Slug    — HARUS PALING BAWAH
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PreorderPageController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentProofStreamController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResellerApplicationController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BestSellerController;
use App\Http\Controllers\Admin\CartReminderController;
use App\Http\Controllers\Admin\CoinController as AdminCoinController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\NameBlacklistController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\OrderPrintController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentProofController;
use App\Http\Controllers\Admin\PreorderPageController as AdminPreorderPageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;

use App\Models\Page;
use App\Models\Product;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIK
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/cari', [SearchController::class, 'index'])->name('search.index');

// ---------------------------------------------------------------------------
// robots.txt
// ---------------------------------------------------------------------------
// File fisik public/robots.txt HARUS DIHAPUS. Kalau ada, web server akan
// menyajikan file fisik DULU sebelum route ini — aturan di bawah tidak akan
// pernah dipakai.
//
// Enforcement nyata (bukan cuma "sopan" ke crawler):
//   - middleware('auth')       → /akun, /keranjang, /checkout
//   - middleware('auth:admin') → /office/*
//   - SecurityHelper::isLegitCrawlerBot() di ProductController
// ---------------------------------------------------------------------------
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Allow: /katalog',
        'Allow: /katalog/*',
        'Allow: /promo',
        'Allow: /pre-order-baru',
        'Allow: /sitemap.xml',
        '',
        'Disallow: /office',
        'Disallow: /office/*',
        'Disallow: /akun',
        'Disallow: /akun/*',
        'Disallow: /keranjang',
        'Disallow: /keranjang/*',
        'Disallow: /checkout',
        'Disallow: /checkout/*',
        'Disallow: /masuk',
        'Disallow: /daftar',
        'Disallow: /verifikasi-email',
        'Disallow: /lupa-password',
        'Disallow: /reset-password',
        'Disallow: /reset-password/*',
        'Disallow: /undang',
        'Disallow: /notifikasi',
        'Disallow: /notifikasi/*',
        'Disallow: /pesanan',
        'Disallow: /pesanan/*',
        'Disallow: /cari',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

// ---------------------------------------------------------------------------
// Katalog & Produk
// ---------------------------------------------------------------------------
// Semua route di bawah ini GLOBAL (guest boleh lihat). Dulu dikunci
// middleware('auth') → tamu yang klik menu Game/Developer kena 500
// ("Call to a member function isSpammer() on null" di ProductController).
// ---------------------------------------------------------------------------

Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');

// URL produk canonical = /{slug} (route slug.show di section 7).
// Route legacy /produk/{product} tetap didaftarkan lalu redirect 301 supaya
// bookmark lama tidak mati & tidak duplikat SEO.
Route::get('/produk/{product}', function (Product $product) {
    return redirect(route('slug.show', $product->slug), 301);
})->name('products.legacy');

Route::get('/pre-order-baru', [PreorderPageController::class, 'show'])->name('preorder.show');
Route::get('/promo', [SaleController::class, 'index'])->name('sale.index');

// Reseller (guest & member)
Route::get('/reseller', [ResellerApplicationController::class, 'create'])->name('reseller.create');
Route::post('/reseller', [ResellerApplicationController::class, 'store'])
    ->middleware('throttle:5,1,reseller');

/*
|--------------------------------------------------------------------------
| 2. GUEST (login, register, reset password)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    // Register
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:10,1,register');

    // Login
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);

    // Reset password
    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1,pw-email')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1,pw-reset')
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| 3. GUEST ADMIN (login /office)
|--------------------------------------------------------------------------
*/

Route::middleware('guest:admin')->group(function (): void {
    Route::get('/office', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/office', [AdminAuthController::class, 'store'])->name('admin.login.store');
});

/*
|--------------------------------------------------------------------------
| 4. ADMIN (auth:admin) — semua di bawah prefix /office, name admin.*
|--------------------------------------------------------------------------
*/

Route::prefix('office')->name('admin.')->middleware('auth:admin')->group(function (): void {

    // === Dashboard ===
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // === Katalog Produk ===
    // AdminProductController tidak punya method show() → resource di-except.
    Route::post('/products/bulk', [AdminProductController::class, 'bulk'])->name('products.bulk');
    Route::post('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])
        ->name('products.toggle-featured');
    Route::resource('/products', AdminProductController::class)->except('show');

    // === Game & Developer ===
    Route::resource('games', GameController::class)->except('show');
    Route::resource('developers', DeveloperController::class)->except('show');

    // === Pelanggan ===
    Route::post('/users/{user}/reset-quota', [UserController::class, 'resetQuota'])->name('users.reset-quota');
    Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/users/{user}/extend', [UserController::class, 'extend'])->name('users.extend');
    Route::post('/users/{user}/send-reset', [UserController::class, 'sendPasswordReset'])->name('users.send-reset');
    Route::post('/users/{user}/anonymize', [UserController::class, 'anonymize'])->name('users.anonymize');
    Route::resource('/users', UserController::class);

    // === Identities (Blokir & Banding) ===
    Route::post('/identities/{identity}/reset', [IdentityController::class, 'reset'])->name('identities.reset');
    Route::post('/identities/{identity}/block', [IdentityController::class, 'block'])->name('identities.block');
    Route::resource('/identities', IdentityController::class)->only(['index', 'store']);

    // === Nama Terlarang ===
    Route::get('/names', [NameBlacklistController::class, 'index'])->name('names.index');
    Route::post('/names', [NameBlacklistController::class, 'store'])->name('names.store');
    Route::delete('/names/{name}', [NameBlacklistController::class, 'destroy'])->name('names.destroy');

    // === Log Aktivitas ===
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

    // === Laporan ===
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // === Maintenance ===
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance/backup', [MaintenanceController::class, 'backupNow'])->name('maintenance.backup');
    Route::post('/maintenance/cleanup', [MaintenanceController::class, 'cleanupNow'])->name('maintenance.cleanup');
    Route::get('/maintenance/backup/{filename}/download', [MaintenanceController::class, 'download'])
        ->name('maintenance.download');
    Route::delete('/maintenance/backup/{filename}', [MaintenanceController::class, 'destroy'])
        ->name('maintenance.destroy');

    // === Profil Admin ===
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'password'])->name('profile.password');

    // === Konten: Pages & Hero Slide ===
    Route::resource('pages', PageController::class)->except('show');
    Route::resource('hero-slides', HeroSlideController::class)->except('show');

    // === Konten: Pre-Order ===
    Route::get('/preorder', [AdminPreorderPageController::class, 'index'])->name('preorder.index');
    Route::get('/preorder/create', [AdminPreorderPageController::class, 'create'])->name('preorder.create');
    Route::post('/preorder', [AdminPreorderPageController::class, 'store'])->name('preorder.store');
    Route::get('/preorder/{preorder}/edit', [AdminPreorderPageController::class, 'edit'])->name('preorder.edit');
    Route::put('/preorder/{preorder}', [AdminPreorderPageController::class, 'update'])->name('preorder.update');
    Route::delete('/preorder/{preorder}', [AdminPreorderPageController::class, 'destroy'])->name('preorder.destroy');
    Route::post('/preorder/{preorder}/toggle', [AdminPreorderPageController::class, 'toggle'])->name('preorder.toggle');

    // === Manajemen Order ===
    Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk', [OrderManagementController::class, 'bulk'])->name('orders.bulk');

    // Order manual (admin input order lama)
    Route::get('/orders/create', [ManualOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [ManualOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/search-users', [ManualOrderController::class, 'searchUsers'])->name('orders.search-users');
    Route::get('/orders/search-variants', [ManualOrderController::class, 'searchVariants'])
        ->name('orders.search-variants');

    // Print
    Route::get('/orders/{order}/invoice', [OrderPrintController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/packing-slip', [OrderPrintController::class, 'packingSlip'])
        ->name('orders.packing-slip');

    // Ubah status
    Route::post('/orders/{order}/status', [OrderManagementController::class, 'updateStatus'])
        ->name('orders.status');
    Route::put('/orders/{order}/items/{item}/status', [OrderManagementController::class, 'updateItemStatus'])
        ->name('orders.items.status');

    // Detail order (paling bawah setelah route statis di atas)
    Route::get('/orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');

    // === Bukti Pembayaran (panel admin) ===
    Route::get('/pembayaran', [PaymentProofController::class, 'index'])->name('payments.index');
    Route::get('/pembayaran/{proof}', [PaymentProofController::class, 'show'])->name('payments.show');
    Route::post('/pembayaran/{proof}/setujui', [PaymentProofController::class, 'approve'])->name('payments.approve');
    Route::post('/pembayaran/{proof}/tolak', [PaymentProofController::class, 'reject'])->name('payments.reject');

    // === Voucher & Metode Pembayaran ===
    Route::resource('vouchers', VoucherController::class)->except('show');
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');

    // === Best Seller ===
    Route::get('/best-sellers', [BestSellerController::class, 'index'])->name('best-sellers.index');
    Route::post('/best-sellers/refresh', [BestSellerController::class, 'refresh'])->name('best-sellers.refresh');
    Route::post('/best-sellers/{product}/toggle-exclude', [BestSellerController::class, 'toggleExclude'])
        ->name('best-sellers.toggle-exclude');

    // === Cart Reminder ===
    Route::get('/cart-reminders', [CartReminderController::class, 'index'])->name('cart-reminders.index');
    Route::post('/cart-reminders/refresh', [CartReminderController::class, 'refresh'])
        ->name('cart-reminders.refresh');

    // === Referral ===
    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('referrals.index');

    // === Koin Pengguna ===
    // ⚠️ /coins/lots/{lot} HARUS di atas /coins/{user} — kalau tidak,
    // "/coins/lots/5" bakal match ke {user} dengan $user="lots" → 404.
    Route::get('/coins', [AdminCoinController::class, 'index'])->name('coins.index');
    Route::delete('/coins/lots/{lot}', [AdminCoinController::class, 'destroyLot'])->name('coins.lots.destroy');
    Route::get('/coins/{user}', [AdminCoinController::class, 'show'])->name('coins.show');
    Route::post('/coins/{user}/adjust', [AdminCoinController::class, 'store'])->name('coins.adjust');

    // === Pengaturan ===
    Route::get('/settings/pricing', [SettingsController::class, 'pricing'])->name('settings.pricing');
    Route::put('/settings/pricing', [SettingsController::class, 'updatePricing'])->name('settings.pricing.update');
    Route::get('/settings/tiers', [SettingsController::class, 'tiers'])->name('settings.tiers');
    Route::put('/settings/tiers', [SettingsController::class, 'updateTiers'])->name('settings.tiers.update');
    Route::get('/settings/marketplaces', [SettingsController::class, 'marketplaces'])->name('settings.marketplaces');
    Route::put('/settings/marketplaces', [SettingsController::class, 'updateMarketplaces'])
        ->name('settings.marketplaces.update');
    Route::get('/settings/display', [SettingsController::class, 'display'])->name('settings.display');
    Route::put('/settings/display', [SettingsController::class, 'updateDisplay'])->name('settings.display.update');
    Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
    Route::put('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('/settings/seo', [SettingsController::class, 'seo'])->name('settings.seo');
    Route::put('/settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo.update');

    // === Logout Admin ===
    Route::post('/keluar', [AdminAuthController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 5. AUTHENTICATED (Pembeli) — belum wajib verified
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {

    // --- Verifikasi email (harus di dalam auth, sebelum 'verified') ---
    Route::get('/verifikasi-email', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::post('/verifikasi-email', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:10,1,otp-verify')
        ->name('verification.verify');
    Route::post('/verifikasi-email/kirim-ulang', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,1,otp-resend')
        ->name('verification.resend');

    // --- Keranjang ---
    // GATEKEEPER #5: profil billing harus lengkap & bukan junk data
    // (asdasd/qwerty/dst) sebelum boleh sentuh keranjang.
    Route::middleware('data.integrity')->group(function (): void {
        Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
        Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');

        // ⚠️ Nama segmen HARUS sama dengan nama parameter method
        // ({cartItem} ↔ CartItem $cartItem). Kalau beda, implicit binding
        // tidak jalan → Laravel bikin model kosong → cek user_id selalu 403.
        Route::match(['put', 'patch'], '/keranjang/{cartItem}', [CartController::class, 'update'])
            ->name('cart.update');
        Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])
            ->name('cart.destroy');
    });

    // --- WAJIB VERIFIED EMAIL ---
    Route::middleware('verified')->group(function (): void {

        // Checkout — GATEKEEPER #5 juga.
        Route::middleware('data.integrity')->group(function (): void {
            Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        });

        // Akun
        Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
        Route::get('/akun/stats', [AccountController::class, 'stats'])->name('account.stats');
        Route::get('/akun/koin', [CoinController::class, 'index'])->name('account.coins.index');
        Route::get('/akun/profil', [ProfileController::class, 'edit'])->name('account.profile.edit');
        Route::put('/akun/profil', [ProfileController::class, 'update'])->name('account.profile.update');

        // Referral
        Route::get('/akun/undang', [ReferralController::class, 'index'])->name('referral.index');
        Route::redirect('/undang', '/akun/undang');

        // Notifikasi
        Route::get('/akun/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('/akun/notifikasi/baca-semua', [NotificationController::class, 'readAll'])
            ->name('notifications.read-all');
        Route::delete('/akun/notifikasi/hapus-semua', [NotificationController::class, 'destroyAll'])
            ->name('notifications.destroy-all');
        Route::get('/akun/notifikasi/{notification}', [NotificationController::class, 'read'])
            ->name('notifications.read');
        Route::delete('/akun/notifikasi/{notification}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy');

        // Alias lama (redirect)
        Route::redirect('/notifikasi/baca-semua', '/akun/notifikasi/baca-semua');
        Route::redirect('/notifikasi/hapus-semua', '/akun/notifikasi/hapus-semua');
        Route::redirect('/notifikasi/{notification}', '/akun/notifikasi/{notification}')
            ->where('notification', '[0-9]+');

        // Pesanan
        Route::get('/akun/pesanan', [OrderController::class, 'index'])->name('account.orders.index');
        Route::get('/akun/pesanan/{orderNumber}/invoice', [OrderController::class, 'invoice'])
            ->name('account.orders.invoice');
        Route::get('/akun/pesanan/{orderNumber}', [OrderController::class, 'show'])
            ->name('account.orders.show');
        Route::post('/akun/pesanan/{orderNumber}/bukti-pembayaran', [OrderController::class, 'proof'])
            ->name('account.orders.upload');

        // Alias lama (redirect)
        Route::redirect('/pesanan', '/akun/pesanan');
        Route::redirect('/pesanan/{orderNumber}', '/akun/pesanan/{orderNumber}')
            ->where('orderNumber', '[A-Za-z0-9_-]+');
        Route::redirect('/pesanan/{orderNumber}/invoice', '/akun/pesanan/{orderNumber}/invoice')
            ->where('orderNumber', '[A-Za-z0-9_-]+');
    });

    // Logout pembeli
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 6. STREAM BUKTI PEMBAYARAN (owner atau admin)
|--------------------------------------------------------------------------
| File fisik disimpan di storage/app/private/payment-proofs/ (tidak publik).
| Akses lewat route ini, diverifikasi di controller:
| owner order atau admin yang login boleh lihat; selain itu 403.
|
| ⚠️ Guard 'auth:web,admin' — kalau guard 'admin' belum didefinisikan
| di config/auth.php, tambahkan dulu. Tanpa itu, request akan error
| "Auth guard [admin] is not defined".
|--------------------------------------------------------------------------
*/

Route::get('/payment-proof/{proof}', [PaymentProofStreamController::class, 'show'])
    ->middleware('auth:web,admin')
    ->name('payment-proof.show');

/*
|--------------------------------------------------------------------------
| 7. LEGAL & SLUG — HARUS PALING BAWAH
|--------------------------------------------------------------------------
| Jangan taruh route dengan prefix statis di bawah sini — akan "ketelan"
| oleh catch-all /{slug}.
|--------------------------------------------------------------------------
*/

Route::get('/{page}', [LegalPageController::class, 'show'])
    ->whereIn('page', ['syarat-dan-ketentuan', 'kebijakan-privasi', 'faq'])
    ->name('legal.show');

// Route canonical produk & halaman statis: /{slug}
// Bot crawler resmi DIBOLEHKAN lewat (lihat SecurityHelper::isLegitCrawlerBot
// di ProductController) supaya SEO index tetap jalan.
Route::get('/{slug}', function (string $slug) {
    $product = Product::query()->where('slug', $slug)->first();
    if ($product) {
        return app(ProductController::class)->show(request(), $product);
    }

    $page = Page::query()->where('slug', $slug)->where('is_published', true)->first();
    if ($page) {
        return view('pages.legal', [
            'title'       => $page->title,
            'contentHtml' => $page->html(),
        ]);
    }

    abort(404);
})->name('slug.show');
