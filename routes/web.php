<?php

use App\Http\Controllers\Admin\CustomerRankController as AdminCustomerRankController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeveloperController as AdminDeveloperController;
use App\Http\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\HeroSlideController as AdminHeroSlideController;
use App\Http\Controllers\Admin\InjectOrderController as AdminInjectOrderController;
use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\MerchCategoryController as AdminMerchCategoryController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderPaymentController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\Admin\PreorderPageController as AdminPreorderPageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SpammerController as AdminSpammerController;
use App\Http\Controllers\Admin\SplitBoxBatchController as AdminSplitBoxBatchController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\PublicSlugController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\ShareController;
use App\Http\Controllers\ProtectedImageController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [HomeController::class, 'robots'])->name('robots');

Route::get('/menyusul', ComingSoonController::class)->name('coming-soon');

// Halaman CMS publik (PRD 25/36)
foreach (['tentang-kami', 'faq', 'cara-belanja', 'tnc', 'kebijakan-privasi'] as $staticPage) {
    Route::get('/'.$staticPage, [PageController::class, 'show'])
        ->defaults('slug', $staticPage)
        ->name('page.'.$staticPage);
}

// Anti-hotlink (PRD 8.10)
Route::get('/protected-images/{filename}', [ProtectedImageController::class, 'show'])
    ->name('protected-images.show');

// Dropdown berantai alamat (PRD 7.3)
Route::prefix('api/regions')->group(function () {
    Route::get('/provinces', [RegionController::class, 'provinces'])->name('api.regions.provinces');
    Route::get('/regencies/{province}', [RegionController::class, 'regencies'])->name('api.regions.regencies');
    Route::get('/districts/{regency}', [RegionController::class, 'districts'])->name('api.regions.districts');
    Route::get('/postal-codes/{district}', [RegionController::class, 'postalCodes'])->name('api.regions.postalCodes');
});

/*
|--------------------------------------------------------------------------
| 2FA (PRD 8.3) — guest only
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/2fa/verify', [TwoFactorController::class, 'show'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:5,1')->name('2fa.verify.store');
});

/*
|--------------------------------------------------------------------------
| Auth — guest only
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware(['honeypot', 'turnstile', 'throttle:login'])
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware(['honeypot', 'turnstile', 'throttle:register'])
        ->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware(['honeypot', 'turnstile', 'throttle:5,1'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware(['turnstile', 'throttle:5,1'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Email verification
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');

    Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:5,1')
        ->name('verification.resend');
});

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Panel (PRD 8/13/22) — auth + admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('office')->name('office.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Produk (PRD 22.6) ──
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::post('/products/bulk', [AdminProductController::class, 'bulk'])->name('products.bulk');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/duplicate', [AdminProductController::class, 'duplicate'])->name('products.duplicate');
    Route::post('/products/{product}/split-clone', [AdminProductController::class, 'splitClone'])->name('products.split-clone');
    Route::get('/products/{product}/preview', [AdminProductController::class, 'preview'])->name('products.preview');

    // Varian
    Route::post('/products/{product}/variants', [AdminProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('/products/{product}/variants/{variant}', [AdminProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [AdminProductController::class, 'destroyVariant'])->name('products.variants.destroy');
    Route::post('/products/{product}/variants/bulk-price', [AdminProductController::class, 'bulkPrice'])->name('products.variants.bulk-price');
    Route::post('/products/{product}/variants/bulk-weight', [AdminProductController::class, 'bulkWeight'])->name('products.variants.bulk-weight');

    // ── Katalog pendukung ──
    Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');
    Route::post('/games', [AdminGameController::class, 'store'])->name('games.store');
    Route::post('/games/bulk', [AdminGameController::class, 'bulk'])->name('games.bulk');
    Route::post('/games/bulk-delete', [AdminGameController::class, 'bulkDelete'])->name('games.bulk-delete');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [AdminGameController::class, 'destroy'])->name('games.destroy');

    Route::get('/developers', [AdminDeveloperController::class, 'index'])->name('developers.index');
    Route::post('/developers', [AdminDeveloperController::class, 'store'])->name('developers.store');
    Route::post('/developers/bulk', [AdminDeveloperController::class, 'bulk'])->name('developers.bulk');
    Route::post('/developers/bulk-delete', [AdminDeveloperController::class, 'bulkDelete'])->name('developers.bulk-delete');
    Route::put('/developers/{developer}', [AdminDeveloperController::class, 'update'])->name('developers.update');
    Route::delete('/developers/{developer}', [AdminDeveloperController::class, 'destroy'])->name('developers.destroy');

    Route::get('/stores', [AdminStoreController::class, 'index'])->name('stores.index');
    Route::post('/stores', [AdminStoreController::class, 'store'])->name('stores.store');
    Route::post('/stores/bulk', [AdminStoreController::class, 'bulk'])->name('stores.bulk');
    Route::post('/stores/bulk-delete', [AdminStoreController::class, 'bulkDelete'])->name('stores.bulk-delete');
    Route::put('/stores/{store}', [AdminStoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [AdminStoreController::class, 'destroy'])->name('stores.destroy');

    Route::get('/merch-categories', [AdminMerchCategoryController::class, 'index'])->name('merch-categories.index');
    Route::post('/merch-categories', [AdminMerchCategoryController::class, 'store'])->name('merch-categories.store');
    Route::post('/merch-categories/bulk', [AdminMerchCategoryController::class, 'bulk'])->name('merch-categories.bulk');
    Route::post('/merch-categories/bulk-delete', [AdminMerchCategoryController::class, 'bulkDelete'])->name('merch-categories.bulk-delete');
    Route::put('/merch-categories/{merch_category}', [AdminMerchCategoryController::class, 'update'])->name('merch-categories.update');
    Route::delete('/merch-categories/{merch_category}', [AdminMerchCategoryController::class, 'destroy'])->name('merch-categories.destroy');

    Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [AdminTagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');

    // ── Transaksi ──
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/inject', [AdminInjectOrderController::class, 'index'])->name('orders.inject');
    Route::post('/orders/inject', [AdminInjectOrderController::class, 'store'])->name('orders.inject.store');
    Route::post('/orders/bulk-status', [AdminOrderController::class, 'bulkStatus'])->name('orders.bulk-status');
    Route::get('/orders/{order:no_order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order:no_order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order:no_order}/packing-list', [AdminOrderController::class, 'packingList'])->name('orders.packing-list');
    Route::patch('/orders/{order:no_order}/status', [OrderStatusController::class, 'update'])->name('orders.status');
    Route::post('/orders/{order:no_order}/note-to-customer', [AdminOrderController::class, 'noteToCustomer'])->name('orders.note-to-customer');
    Route::post('/orders/{order:no_order}/settlements', [AdminOrderController::class, 'storeSettlement'])->name('orders.settlements');
    Route::post('/orders/{order:no_order}/unlock-penalty', [AdminOrderController::class, 'unlockPenalty'])->name('orders.unlock-penalty');
    Route::post('/orders/{order:no_order}/hangus-permanent', [AdminOrderController::class, 'hangusPermanent'])->name('orders.hangus-permanent');

    // Pembayaran
    Route::post('/orders/{order:no_order}/payments/{payment}/verify', [OrderPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/orders/{order:no_order}/payments/{payment}/reject', [OrderPaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

    Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [AdminPaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::put('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::delete('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    // Refund
    Route::post('/orders/{order:no_order}/refund', [AdminRefundController::class, 'requestFull'])->name('orders.refund.full');
    Route::post('/orders/{order:no_order}/refund/partial', [AdminRefundController::class, 'requestPartial'])->name('orders.refund.partial');
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refund}/complete', [AdminRefundController::class, 'complete'])->name('refunds.complete');
    Route::post('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');

    // Search & Notifications (admin)
    Route::get('/search', [AdminSearchController::class, '__invoke'])->name('search');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.read-all');

    // ── Email Templates ──
    Route::get('/email-templates', [AdminEmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::post('/email-templates', [AdminEmailTemplateController::class, 'store'])->name('email-templates.store');
    Route::get('/email-templates/{emailTemplate}/preview', [AdminEmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::post('/email-templates/{emailTemplate}/test-send', [AdminEmailTemplateController::class, 'testSend'])->name('email-templates.test-send');
    Route::put('/email-templates/{emailTemplate}', [AdminEmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::delete('/email-templates/{emailTemplate}', [AdminEmailTemplateController::class, 'destroy'])->name('email-templates.destroy');

    // ── Spammers ──
    Route::get('/spammers', [AdminSpammerController::class, 'index'])->name('spammers.index');
    Route::post('/spammers', [AdminSpammerController::class, 'store'])->name('spammers.store');
    Route::post('/spammers/{spammer}/unblock', [AdminSpammerController::class, 'unblock'])->name('spammers.unblock');

    // ── Pengguna ──
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/customers/rank', [AdminCustomerRankController::class, 'index'])->name('customers.rank');
    Route::get('/customers/rank/export', [AdminCustomerRankController::class, 'export'])->name('customers.rank.export');
    Route::post('/users/{user}/gift', [AdminCustomerRankController::class, 'gift'])->name('users.gift');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/force-reset-password', [AdminUserController::class, 'forceResetPassword'])->name('users.force-reset-password');
    Route::post('/users/{user}/force-verify-email', [AdminUserController::class, 'forceVerifyEmail'])->name('users.force-verify-email');
    Route::post('/users/{user}/notify', [AdminUserController::class, 'notify'])->name('users.notify');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/forever-delete', [AdminUserController::class, 'foreverDelete'])->name('users.forever-delete');

    // ── Settings & SEO ──
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [AdminSettingsController::class, 'testEmail'])
        ->name('settings.test-email');
    Route::get('/seo', [AdminSettingsController::class, 'seo'])->name('seo.index');
    Route::post('/seo', [AdminSettingsController::class, 'updateSeo'])->name('seo.update');
    Route::get('/marketing', [AdminSettingsController::class, 'marketing'])->name('marketing.index');
    Route::post('/marketing', [AdminSettingsController::class, 'updateMarketing'])->name('marketing.update');

    // ── CMS Pages ──
    Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

    // ── Preorder Builder ──
    Route::get('/preorder', [AdminPreorderPageController::class, 'index'])->name('preorder.index');
    Route::get('/preorder/create', [AdminPreorderPageController::class, 'create'])->name('preorder.create');
    Route::post('/preorder', [AdminPreorderPageController::class, 'store'])->name('preorder.store');
    Route::get('/preorder/{preorderPage}/edit', [AdminPreorderPageController::class, 'edit'])->name('preorder.edit');
    Route::put('/preorder/{preorderPage}', [AdminPreorderPageController::class, 'update'])->name('preorder.update');

    // ── Hero Slides ──
    Route::get('/hero-slides', [AdminHeroSlideController::class, 'index'])->name('hero-slides.index');
    Route::post('/hero-slides', [AdminHeroSlideController::class, 'store'])->name('hero-slides.store');
    Route::put('/hero-slides/{heroSlide}', [AdminHeroSlideController::class, 'update'])->name('hero-slides.update');
    Route::delete('/hero-slides/{heroSlide}', [AdminHeroSlideController::class, 'destroy'])->name('hero-slides.destroy');

    // ── Vouchers ──
    Route::get('/vouchers', [AdminVoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])->name('vouchers.store');
    Route::put('/vouchers/{voucher}', [AdminVoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{voucher}', [AdminVoucherController::class, 'destroy'])->name('vouchers.destroy');
    Route::post('/vouchers/{voucher}/issue', [AdminVoucherController::class, 'issue'])->name('vouchers.issue');
    Route::delete('/user-vouchers/{userVoucher}', [AdminVoucherController::class, 'revoke'])->name('user-vouchers.revoke');

    // ── Laporan ──
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales-chart', [AdminReportController::class, 'salesChart'])->name('reports.sales-chart');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');

    // ── Split Box ──
    Route::post('/split-box-batches/{split_box_batch}/close-refund', [AdminSplitBoxBatchController::class, 'closeRefund'])
        ->name('split-box-batches.close-refund');

    // ── Logs ──
    Route::get('/admin-logs', [AdminLogController::class, 'admin'])->name('admin-logs');
    Route::get('/auth-logs', [AdminLogController::class, 'auth'])->name('auth-logs');
    Route::get('/activity-logs', [AdminLogController::class, 'activity'])->name('activity-logs');
    Route::get('/logs/export', [AdminLogController::class, 'export'])->name('logs.export');
});

/*
|--------------------------------------------------------------------------
| Cart, Checkout, Orders (PRD 16) — butuh login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('checkout.ready')
        ->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:no_order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:no_order}/refund-request', [OrderController::class, 'requestRefund'])->name('orders.refund-request');
    Route::post('/orders/{order:no_order}/payments', [OrderController::class, 'storePayment'])
        ->middleware('checkout.ready')
        ->name('orders.payments.store');
    Route::get('/orders/{order:no_order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::get('/dashboard', [ProfileController::class, 'show'])->name('dashboard');
    Route::get('/profile', fn () => redirect()->route('dashboard'));
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/complete', [ProfileController::class, 'complete'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileController::class, 'storeComplete'])->name('profile.complete.store');
    Route::get('/profile/koin', [ProfileController::class, 'coins'])->name('profile.coins');
    Route::get('/profile/wallet', [ProfileController::class, 'wallet'])->name('profile.wallet');
    Route::get('/profile/undang', [ProfileController::class, 'referral'])->name('profile.referral');

    // Notifikasi user
    Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::get('/profile/notifications/unread-count', [ProfileController::class, 'unreadCount'])->name('profile.notifications.unread-count');
    Route::post('/profile/notifications/{notification}/read', [ProfileController::class, 'readNotification'])->name('profile.notifications.read');
    Route::post('/profile/notifications/read-all', [ProfileController::class, 'readAllNotifications'])->name('profile.notifications.read-all');

    Route::post('/profile/tour-seen', [ProfileController::class, 'markTourSeen'])->name('profile.tour-seen');

    Route::post('/share/{product:slug}', [ShareController::class, 'store'])->name('share.store');
});

/*
|--------------------------------------------------------------------------
| Katalog (PRD 13.8) — butuh login (Guest Barrier, PRD 7.2)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/developer/{developer:slug}', [CatalogController::class, 'developer'])
        ->name('catalog.developer');

    Route::get('/{game:slug}/kategori/{category:slug}', [CatalogController::class, 'category'])
        ->withoutScopedBindings()
        ->name('catalog.category');

    Route::get('/{game:slug}/{product:slug}', [ProductController::class, 'show'])
        ->name('product.show');

});

Route::get('/{slug}', [PublicSlugController::class, 'show'])->name('catalog.game');
