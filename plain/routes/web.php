<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LegalPageController;
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

Route::middleware('auth')->group(function (): void {
    Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});