<?php

use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LegacyOrderImportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserMigrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProfileCompleteController;
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
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

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
    Route::resource('games', GameController::class)->except(['show', 'destroy']);
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
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
