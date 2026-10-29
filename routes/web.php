<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeveloperController as AdminDeveloperController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\MerchCategoryController as AdminMerchCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderPaymentController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SplitBoxBatchController as AdminSplitBoxBatchController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\ShareController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

// Placeholder jujur untuk fitur/halaman yang belum dibangun (antrean).
Route::get('/menyusul', ComingSoonController::class)->name('coming-soon');
foreach (['tentang-kami', 'faq', 'cara-belanja', 'tnc', 'kebijakan-privasi'] as $staticPage) {
    Route::get('/'.$staticPage, ComingSoonController::class)->name('page.'.$staticPage);
}

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware(['honeypot', 'throttle:login'])
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware(['honeypot', 'throttle:register'])
        ->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('honeypot')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');

    Route::post('/email/resend', [VerifyEmailController::class, 'resend'])->name('verification.resend');
});

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware(['auth', 'admin'])->prefix('office')->name('office.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Produk (PRD 22.6)
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::post('/products/bulk', [AdminProductController::class, 'bulk'])->name('products.bulk');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/variants', [AdminProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::post('/products/{product}/variants/bulk-weight', [AdminProductController::class, 'bulkWeight'])->name('products.variants.bulk-weight');

    // Katalog pendukung
    Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');
    Route::post('/games', [AdminGameController::class, 'store'])->name('games.store');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [AdminGameController::class, 'destroy'])->name('games.destroy');

    Route::get('/developers', [AdminDeveloperController::class, 'index'])->name('developers.index');
    Route::post('/developers', [AdminDeveloperController::class, 'store'])->name('developers.store');
    Route::put('/developers/{developer}', [AdminDeveloperController::class, 'update'])->name('developers.update');
    Route::delete('/developers/{developer}', [AdminDeveloperController::class, 'destroy'])->name('developers.destroy');

    Route::get('/stores', [AdminStoreController::class, 'index'])->name('stores.index');
    Route::post('/stores', [AdminStoreController::class, 'store'])->name('stores.store');
    Route::put('/stores/{store}', [AdminStoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [AdminStoreController::class, 'destroy'])->name('stores.destroy');

    Route::get('/merch-categories', [AdminMerchCategoryController::class, 'index'])->name('merch-categories.index');
    Route::post('/merch-categories', [AdminMerchCategoryController::class, 'store'])->name('merch-categories.store');
    Route::put('/merch-categories/{merch_category}', [AdminMerchCategoryController::class, 'update'])->name('merch-categories.update');

    Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');

    // Transaksi
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk-status', [AdminOrderController::class, 'bulkStatus'])->name('orders.bulk-status');
    Route::get('/orders/{order:no_order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order:no_order}/status', [OrderStatusController::class, 'update'])->name('orders.status');
    Route::post('/orders/{order:no_order}/settlements', [AdminOrderController::class, 'storeSettlement'])->name('orders.settlements');
    Route::post('/orders/{order:no_order}/unlock-penalty', [AdminOrderController::class, 'unlockPenalty'])->name('orders.unlock-penalty');
    Route::post('/orders/{order:no_order}/hangus-permanent', [AdminOrderController::class, 'hangusPermanent'])->name('orders.hangus-permanent');
    Route::post('/orders/{order:no_order}/payments/{payment}/verify', [OrderPaymentController::class, 'verify'])
        ->name('payments.verify');
    Route::post('/orders/{order:no_order}/payments/{payment}/reject', [OrderPaymentController::class, 'reject'])
        ->name('payments.reject');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

    // Pengguna
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    // Settings, laporan, log
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::post('/split-box-batches/{split_box_batch}/close-refund', [AdminSplitBoxBatchController::class, 'closeRefund'])
        ->name('split-box-batches.close-refund');
    Route::get('/admin-logs', [AdminLogController::class, 'admin'])->name('admin-logs');
    Route::get('/auth-logs', [AdminLogController::class, 'auth'])->name('auth-logs');
    Route::get('/activity-logs', [AdminLogController::class, 'activity'])->name('activity-logs');
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
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:no_order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:no_order}/payments', [OrderController::class, 'storePayment'])
        ->name('orders.payments.store');
    Route::get('/orders/{order:no_order}/invoice', [OrderController::class, 'invoice'])
        ->name('orders.invoice');

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
    Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::post('/profile/notifications/read-all', [ProfileController::class, 'readAllNotifications'])->name('profile.notifications.read-all');

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

    Route::get('/{game:slug}', [CatalogController::class, 'game'])
        ->name('catalog.game');
});
