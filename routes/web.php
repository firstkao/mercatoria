<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileCompleteController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;   // ← tambah

// ─── Public Routes ──────────────────────────────
Route::get('/', function () {
    return view('test.home');
})->name('home');

// ─── Guest Routes (belum login) ─────────────────
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:3,1');

    // Login
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
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