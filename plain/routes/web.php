<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PreorderPageController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BestSellerController;
use App\Http\Controllers\Admin\CartReminderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\NameBlacklistController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\OrderNoteController;
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
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResellerApplicationController;
use App\Http\Controllers\SearchController;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// ============================================================
// 1. PUBLIK
// ============================================================

Route::get('/', [HomeController::class, 'index'])->name('home');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /office',
        'Disallow: /office/*',
        'Disallow: /akun',
        'Disallow: /akun/*',
        'Disallow: /keranjang',
        'Disallow: /checkout',
        'Disallow: /masuk',
        'Disallow: /daftar',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ];
    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// Kontak: controller & view /kontak sudah dihapus dari kodebase (PR user),
// route-nya ikut dicabut — sebelumnya masih terdaftar dan menunjuk class
// yang tidak ada -> setiap request /kontak = fatal "Class not found" (500).

// Search
Route::get('/cari', [SearchController::class, 'index'])->name('search.index');

// Katalog & produk — GLOBAL (guest boleh lihat; sesuai permintaan user:
// dropdown game/developer header/footer harus bisa diklik tanpa login).
// Dulu route ini ada di dalam grup middleware('auth') sehingga tamu yang
// klik menu Game/Developer langsung kena 500 ("Call to a member function
// isSpammer() on null" di ProductController).
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');

// Permintaan user #1: URL produk canonical adalah /{slug} TANPA prefix
// /produk. Route '/produk/{product}' tetap didaftarkan lalu redirect 301 ke
// slug bersih supaya bookmark/link lama tidak mati dan tidak duplikat SEO.
// BUG FIX (500 di semua halaman publik): route ini memakai nama
// 'products.show' padahal TIDAK ada lagi route bernama products.show yang
// global — route canonical produk sekarang adalah slug.show (/{slug}).
// Akibatnya setiap render view publik (home, katalog, search, footer, sale)
// melempar RouteNotDefinedException -> 500. Redirect legacy kini menunjuk
// ke route('slug.show', $product->slug).
Route::get('/produk/{product}', function (Product $product) {
    return redirect(route('slug.show', $product->slug), 301);
})->name('products.legacy');

// Pre-Order Baru — GLOBAL juga (menu header tampil untuk semua pengunjung).
Route::get('/pre-order-baru', [PreorderPageController::class, 'show'])->name('preorder.show');

// Promo (Batch 24)
Route::get('/promo', [SaleController::class, 'index'])->name('sale.index');

// Reseller (guest & member)
Route::get('/reseller', [ResellerApplicationController::class, 'create'])->name('reseller.create');
Route::post('/reseller', [ResellerApplicationController::class, 'store'])->middleware('throttle:5,1,reseller');

// ============================================================
// 2. GUEST (Login & Register)
// ============================================================
Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1,register');
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);

    // Lupa Password (Batch 27)
    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1,pw-email')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1,pw-reset')
        ->name('password.store');
});

// ============================================================
// 3. GUEST:ADMIN (Login Admin /office)
// ============================================================
Route::middleware('guest:admin')->group(function () {
    Route::get('/office', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/office', [AdminAuthController::class, 'store'])->name('admin.login.store');
});

// ============================================================
// 4. ADMIN (login:admin)
// ============================================================
Route::prefix('office')->name('admin.')->middleware('auth:admin')->group(function (): void {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Katalog Produk + Bulk + Featured
    Route::post('/products/bulk', [AdminProductController::class, 'bulk'])->name('products.bulk');
    Route::post('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    // BUG FIX: AdminProductController tidak punya method show(), jadi
    // /office/products/{slug} melempar BadMethodCallException (500).
    Route::resource('/products', AdminProductController::class)->except('show');

    // Game & Developer
    Route::resource('games', GameController::class)->except('show');
    Route::resource('developers', DeveloperController::class)->except('show');

    // Pelanggan & Pengguna
    Route::post('/users/{user}/reset-quota', [UserController::class, 'resetQuota'])->name('users.reset-quota');
    // Permintaan user #2: aksi "buka kunci" spammer dari panel admin.
    Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/users/{user}/extend', [UserController::class, 'extend'])->name('users.extend');
    Route::post('/users/{user}/send-reset', [UserController::class, 'sendPasswordReset'])->name('users.send-reset');
    Route::post('/users/{user}/anonymize', [UserController::class, 'anonymize'])->name('users.anonymize');
    Route::resource('/users', UserController::class);

    // Identities (Blokir & Banding)
    Route::post('/identities/{identity}/reset', [IdentityController::class, 'reset'])->name('identities.reset');
    Route::post('/identities/{identity}/block', [IdentityController::class, 'block'])->name('identities.block');
    Route::resource('/identities', IdentityController::class)->only(['index', 'store']);

    // Nama Terlarang
    Route::get('/names', [NameBlacklistController::class, 'index'])->name('names.index');
    Route::post('/names', [NameBlacklistController::class, 'store'])->name('names.store');
    Route::delete('/names/{name}', [NameBlacklistController::class, 'destroy'])->name('names.destroy');

    // Log Aktivitas
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

    // BUG 5 STRICT MODE: fitur admin "Pesan Kontak" (ContactMessageController)
    // DIHAPUS sesuai permintaan user. Form kontak publik tetap jalan — pesan
    // masuk ke DB + email CS, hanya panel admin-nya yang tidak disediakan lagi.

    // Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Maintenance
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance/backup', [MaintenanceController::class, 'backupNow'])->name('maintenance.backup');
    Route::post('/maintenance/cleanup', [MaintenanceController::class, 'cleanupNow'])->name('maintenance.cleanup');
    Route::get('/maintenance/backup/{filename}/download', [MaintenanceController::class, 'download'])->name('maintenance.download');
    Route::delete('/maintenance/backup/{filename}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

    // Profil Admin
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'password'])->name('profile.password');

    // Konten: Pages
    // BUG FIX: PageController (admin) tidak punya method show() → 500.
    Route::resource('pages', PageController::class)->except('show');

    // Konten: Hero Slide
    Route::resource('hero-slides', HeroSlideController::class)->except('show');

    // Konten: Pre-Order
    Route::get('/preorder', [AdminPreorderPageController::class, 'index'])->name('preorder.index');
    Route::get('/preorder/create', [AdminPreorderPageController::class, 'create'])->name('preorder.create');
    Route::post('/preorder', [AdminPreorderPageController::class, 'store'])->name('preorder.store');
    Route::get('/preorder/{preorder}/edit', [AdminPreorderPageController::class, 'edit'])->name('preorder.edit');
    Route::put('/preorder/{preorder}', [AdminPreorderPageController::class, 'update'])->name('preorder.update');
    Route::delete('/preorder/{preorder}', [AdminPreorderPageController::class, 'destroy'])->name('preorder.destroy');
    Route::post('/preorder/{preorder}/toggle', [AdminPreorderPageController::class, 'toggle'])->name('preorder.toggle');

    // Manajemen Order
    Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/invoice', [OrderPrintController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/packing-slip', [OrderPrintController::class, 'packingSlip'])->name('orders.packing-slip');
    Route::post('/orders/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');
    
    // ===== Order Notes (internal admin) =====
    Route::post('/orders/{order}/notes', [OrderNoteController::class, 'store'])->name('orders.notes.store');
    Route::delete('/orders/{order}/notes/{note}', [OrderNoteController::class, 'destroy'])->name('orders.notes.destroy');
    Route::post('/orders/{order}/notes/{note}/toggle-pin', [OrderNoteController::class, 'togglePin'])->name('orders.notes.toggle-pin');

    // Manajemen Bukti Pembayaran
    Route::get('/pembayaran', [PaymentProofController::class, 'index'])->name('payments.index');
    Route::get('/pembayaran/{proof}', [PaymentProofController::class, 'show'])->name('payments.show');
    Route::post('/pembayaran/{proof}/setujui', [PaymentProofController::class, 'approve'])->name('payments.approve');
    Route::post('/pembayaran/{proof}/tolak', [PaymentProofController::class, 'reject'])->name('payments.reject');

    // Manajemen Voucher
    Route::resource('vouchers', VoucherController::class)->except('show');

    // Metode Pembayaran
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');

    // Best Seller (Batch 30)
    Route::get('/best-sellers', [BestSellerController::class, 'index'])->name('best-sellers.index');
    Route::post('/best-sellers/refresh', [BestSellerController::class, 'refresh'])->name('best-sellers.refresh');
    Route::post('/best-sellers/{product}/toggle-exclude', [BestSellerController::class, 'toggleExclude'])->name('best-sellers.toggle-exclude');

    // Cart Reminder (Batch 31)
    Route::get('/cart-reminders', [CartReminderController::class, 'index'])->name('cart-reminders.index');
    Route::post('/cart-reminders/refresh', [CartReminderController::class, 'refresh'])->name('cart-reminders.refresh');

    // Referral (Batch 22)
    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('referrals.index');

    // Pengaturan
    Route::get('/settings/pricing', [SettingsController::class, 'pricing'])->name('settings.pricing');
    Route::put('/settings/pricing', [SettingsController::class, 'updatePricing'])->name('settings.pricing.update');
    Route::get('/settings/tiers', [SettingsController::class, 'tiers'])->name('settings.tiers');
    Route::put('/settings/tiers', [SettingsController::class, 'updateTiers'])->name('settings.tiers.update');
    Route::get('/settings/marketplaces', [SettingsController::class, 'marketplaces'])->name('settings.marketplaces');
    Route::put('/settings/marketplaces', [SettingsController::class, 'updateMarketplaces'])->name('settings.marketplaces.update');
    Route::get('/settings/display', [SettingsController::class, 'display'])->name('settings.display');
    Route::put('/settings/display', [SettingsController::class, 'updateDisplay'])->name('settings.display.update');
    Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
    Route::put('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('/settings/seo', [SettingsController::class, 'seo'])->name('settings.seo');
    Route::put('/settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo.update');

    // Logout Admin
    Route::post('/keluar', [AdminAuthController::class, 'destroy'])->name('logout');
});

// ============================================================
// 5. AUTHENTICATED (Pembeli) — belum wajib verified
// ============================================================
Route::middleware('auth')->group(function (): void {
    // Verifikasi email (harus di dalam auth, tapi sebelum 'verified')
    Route::get('/verifikasi-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/verifikasi-email', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:10,1,otp-verify')
        ->name('verification.verify');
    Route::post('/verifikasi-email/kirim-ulang', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,1,otp-resend')
        ->name('verification.resend');

    // Cart — GATEKEEPER #5: sebelum boleh menyentuh keranjang/checkout,
    // profil billing harus lengkap & bukan junk data (asdasd/qwerty/dst).
    Route::middleware('data.integrity')->group(function (): void {
        Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
        Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
        // BUG FIX: nama segmen HARUS sama dengan nama parameter method
        // ({cartItem} ↔ CartItem $cartItem). Sebelumnya route-nya /keranjang/{item}
        // sehingga implicit binding tidak pernah jalan — Laravel membuat model
        // kosong, lalu abort_if($cartItem->user_id !== auth()->id()) selalu true
        // → hapus/ubah item keranjang selalu 403.
        Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    });

    // Notifikasi (canonical: /akun/notifikasi — lihat blok "Akun" di bawah.
    // URL lama /notifikasi/* tetap dilayani via redirect permanen.)

    // Referral sudah pindah ke dalam grup /akun (canonical: /akun/undang)
    // supaya anak-menu ikut induknya; URL lama di-redirect permanen di bawah.

    // Pre-Order (halaman publik /pre-order-baru sudah pindah ke blok GLOBAL;
    // route lama di sini dihapus agar tidak duplikat/ambigu)

    // ========================================================
    // WAJIB VERIFIED EMAIL
    // ========================================================
    Route::middleware('verified')->group(function (): void {
        // Checkout — GATEKEEPER #5 juga (profil billing wajib valid sebelum bayar).
        Route::middleware('data.integrity')->group(function (): void {
            Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        });

        // Pesanan (canonical: /akun/pesanan/... — lihat blok "Akun" di bawah.
        // ✅ BUG FIX: sebelumnya ada dua set route kembar (/pesanan/* dan
        // /akun/pesanan/*) yg menunjuk controller & view yang sama; route lama
        // dihapus supaya tidak ada ambiguitas penamaan.)

        // Akun
        Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
        Route::get('/akun/stats', [AccountController::class, 'stats'])->name('account.stats');  
        Route::get('/akun/koin', [CoinController::class, 'index'])->name('account.coins.index');
        Route::get('/akun/profil', [ProfileController::class, 'edit'])->name('account.profile.edit');
        Route::put('/akun/profil', [ProfileController::class, 'update'])->name('account.profile.update');

        // Referral / undang teman (canonical: /akun/undang — bug fix: dulu
        // jalurnya /undang sehingga tidak terasa satu area dengan akun)
        Route::get('/akun/undang', [ReferralController::class, 'index'])->name('referral.index');
        Route::redirect('/undang', '/akun/undang'); // bookmark URL lama

        // Notifikasi milik akun (canonical: /akun/notifikasi — bug fix: dulu
        // jalurnya /notifikasi sehingga tidak terasa satu area dengan profil)
        Route::get('/akun/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('/akun/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::delete('/akun/notifikasi/hapus-semua', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::get('/akun/notifikasi/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
        Route::delete('/akun/notifikasi/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Redirect permanen dari URL lama /notifikasi/* (bookmark terdahulu).
        // Catatan: {notification} dipatok [0-9]+ supaya tab "Semua"/"Belum
        // dibaca" (/notifikasi?filter=unread → /akun/notifikasi) tidak ikut ke sini.
        Route::redirect('/notifikasi/baca-semua', '/akun/notifikasi/baca-semua');
        Route::redirect('/notifikasi/hapus-semua', '/akun/notifikasi/hapus-semua');
        Route::redirect('/notifikasi/{notification}', '/akun/notifikasi/{notification}')->where('notification', '[0-9]+');

        // Pesanan milik akun (canonical)
        Route::get('/akun/pesanan', [OrderController::class, 'index'])->name('account.orders.index');
        Route::get('/akun/pesanan/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('account.orders.invoice');
        Route::get('/akun/pesanan/{orderNumber}', [OrderController::class, 'show'])->name('account.orders.show');
        Route::post('/akun/pesanan/{orderNumber}/bukti-pembayaran', [OrderController::class, 'proof'])->name('account.orders.upload');

        // Redirect permanen dari URL lama /pesanan/* (bookmark & email terdahulu)
        Route::redirect('/pesanan', '/akun/pesanan');
        Route::redirect('/pesanan/{orderNumber}', '/akun/pesanan/{orderNumber}')->where('orderNumber', '[A-Za-z0-9_-]+');
        Route::redirect('/pesanan/{orderNumber}/invoice', '/akun/pesanan/{orderNumber}/invoice')->where('orderNumber', '[A-Za-z0-9_-]+');
    });

    // Logout
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ============================================================
// 6. LEGAL & SLUG (paling bawah)
// ============================================================
Route::get('/{page}', [LegalPageController::class, 'show'])
    ->whereIn('page', ['syarat-dan-ketentuan', 'kebijakan-privasi', 'faq'])
    ->name('legal.show');

// Slug untuk produk & halaman — INI route canonical produk: /{slug}
// (permintaan user #1: plain.mercatoria.id/honkai-star-rail-... bukan
// /produk/...). Produk WAJIB LOGIN: guest yang membuka /{slug} produk
// di-redirect ke halaman login oleh ProductController::show.
Route::get('/{slug}', function (string $slug) {
    $product = Product::query()->where('slug', $slug)->first();
    if ($product) {
        return app(ProductController::class)->show(request(), $product);
    }

    $page = Page::query()->where('slug', $slug)->where('is_published', true)->first();
    if ($page) {
        return view('pages.legal', [
            'title' => $page->title,
            'contentHtml' => $page->html(),
        ]);
    }

    abort(404);
})->name('slug.show');

