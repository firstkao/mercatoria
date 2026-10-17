<?php

use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LegacyOrderImportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserMigrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileCompleteController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

// ─── Public Routes ──────────────────────────────
Route::get('/', function () {
    return view('home');
})->name('home');

// ─── Guest Routes (belum login) ─────────────────
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware(['throttle:3,1', ProtectAgainstSpam::class]);

    // Login
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware(['throttle:5,1', ProtectAgainstSpam::class]);

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware(['throttle:5,1', ProtectAgainstSpam::class])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware(['throttle:5,1', ProtectAgainstSpam::class])->name('password.update');
});

// ─── Auth Routes (udah login) ───────────────────
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/snapshots/{snapshot}/{image}', [OrderController::class, 'snapshotImage'])->name('orders.snapshots.image');
    Route::post('/orders/{order}/payment-proof', [OrderController::class, 'uploadProof'])->name('orders.payment-proof');
    Route::post('/orders/{order}/refunds', [OrderController::class, 'requestRefund'])->name('orders.refunds.store');
    Route::get('/profile/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/profile/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/profile/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/profile/notifications/{notification}/open', [UserNotificationController::class, 'open'])->name('notifications.open');
    Route::middleware(['ensure.verified', 'ensure.profile'])->post(
        '/checkout',
        [CartController::class, 'placeOrder'],
    )->name('cart.place-order');

    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Verifikasi email
    Route::get('/verify-email', [VerifyEmailController::class, 'notice'])->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', [VerifyEmailController::class, 'send'])
        ->middleware('throttle:3,60')->name('verification.send'); // max 3x/jam sesuai Design Spec

    // Lengkapi profil (wajib verif dulu)
    Route::middleware('ensure.verified')->group(function () {
        Route::get('/profile/complete', [ProfileCompleteController::class, 'create'])->name('profile.complete');
        Route::post('/profile/complete', [ProfileCompleteController::class, 'store']);
    });
});

Route::prefix('office')->name('office.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::patch('orders/bulk-status', [AdminOrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/note', [AdminOrderController::class, 'sendNote'])->name('orders.note');
    Route::patch('payments/{payment}/verify', [AdminOrderController::class, 'verifyPayment'])->name('payments.verify');
    Route::patch('payments/{payment}/reject', [AdminOrderController::class, 'rejectPayment'])->name('payments.reject');
    Route::get('payments/{payment}/proof', [AdminOrderController::class, 'paymentProof'])->name('payments.proof');
    Route::get('refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::patch('refunds/{refund}', [AdminRefundController::class, 'process'])->name('refunds.process');
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/open', [AdminNotificationController::class, 'open'])->name('notifications.open');
    Route::resource('games', GameController::class)->except(['show', 'destroy']);
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    Route::delete('products/bulk', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::patch('products/{product}/variants/{variant}/weight', [ProductVariantController::class, 'updateWeight'])->name('products.variants.weight.update');
    Route::patch('products/{product}/variants/weight', [ProductVariantController::class, 'bulkUpdateWeight'])->name('products.variants.weight.bulk');
    Route::resource('stores', StoreController::class)->except('show');
    Route::resource('users', UserMigrationController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('orders/create', [LegacyOrderImportController::class, 'create'])->name('orders.create');
    Route::post('orders', [LegacyOrderImportController::class, 'store'])->name('orders.store');
    Route::post('users/{user}/force-reset-password', [UserMigrationController::class, 'forceResetPassword'])->name('users.force-reset-password');
    Route::post('users/{user}/force-verify-email', [UserMigrationController::class, 'forceVerifyEmail'])->name('users.force-verify-email');
});

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/search', [CatalogController::class, 'search'])->name('catalog.search');
Route::get('/developer/{developer:slug}', [CatalogController::class, 'showDeveloper'])->name('developers.show');
Route::get('/product/{product:slug}', [CatalogController::class, 'showProduct'])->name('products.direct');
Route::get('/{game:slug}/kategori/{category:slug}', [CatalogController::class, 'showCategory'])->name('games.categories.show');
Route::get('/{game:slug}/{product:slug}', [CatalogController::class, 'showProductInGame'])->name('games.products.show');
Route::get('/{game:slug}', [CatalogController::class, 'showGame'])->name('games.show');
