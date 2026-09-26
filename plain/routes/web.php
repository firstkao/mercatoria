<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentProofController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController; // Pastikan ini di-import
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResellerApplicationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/katalog')->name('home');

Route::get('/{page}', [LegalPageController::class, 'show'])
    ->whereIn('page', ['syarat-dan-ketentuan', 'kebijakan-privasi', 'faq'])
    ->name('legal.show');

Route::get('/reseller', [ResellerApplicationController::class, 'create'])->name('reseller.create');
Route::post('/reseller', [ResellerApplicationController::class, 'store'])->middleware('throttle:5,1');

Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/masuk', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/masuk', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/pembayaran', [PaymentProofController::class, 'index'])->name('payments.index');
        Route::get('/pembayaran/{proof}', [PaymentProofController::class, 'show'])->name('payments.show');
        Route::post('/pembayaran/{proof}/setujui', [PaymentProofController::class, 'approve'])->name('payments.approve');
        Route::post('/pembayaran/{proof}/tolak', [PaymentProofController::class, 'reject'])->name('payments.reject');
        Route::post('/keluar', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');
    
    // --- FITUR CART (Keranjang) ---
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    
    // --- FITUR CHECKOUT (Di-mix menggunakan CheckoutController) ---
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    
    // --- FITUR PESANAN & AKUN ---
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{orderNumber}/bukti-pembayaran', [OrderController::class, 'proof'])->name('orders.proof');
    Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
