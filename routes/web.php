<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderPaymentController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

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

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/office', [AdminDashboardController::class, 'index'])->name('office.dashboard');

    Route::post('/office/orders/{order:no_order}/payments/{payment}/verify', [OrderPaymentController::class, 'verify'])
        ->name('office.payments.verify');
    Route::post('/office/orders/{order:no_order}/payments/{payment}/reject', [OrderPaymentController::class, 'reject'])
        ->name('office.payments.reject');
    Route::patch('/office/orders/{order:no_order}/status', [OrderStatusController::class, 'update'])
        ->name('office.orders.status');
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

    Route::get('/orders/{order:no_order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:no_order}/payments', [OrderController::class, 'storePayment'])
        ->name('orders.payments.store');
    Route::get('/orders/{order:no_order}/invoice', [OrderController::class, 'invoice'])
        ->name('orders.invoice');
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
