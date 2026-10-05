<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Belum login → mau ke mana
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('office') || $request->is('office/*')) {
                return route('admin.login');
            }
            return route('login');
        });

        // Sudah login → mau ke mana
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('office') || $request->is('office/*')) {
                return route('admin.dashboard');
            }
            return '/akun';
        });

        // Alias 'verified' (Batch 32)
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Alias Gatekeeper (adaptasi MERCATORIA GATEKEEPER v17.4 dari WP).
        // Hanya 'data.integrity' yang dipakai lewat route middleware. Alias
        // 'ip.blacklist' & 'guest.barrier' tidak direferensikan di route manapun
        // (IpBlacklistMiddleware sudah berjalan global via ->web(append) di bawah;
        // GuestBarrierMiddleware tidak aktif karena group auth/verified sudah
        // menutup /keranjang dan /checkout), jadi tidak perlu didaftarkan.
        $middleware->alias([
            'data.integrity' => \App\Http\Middleware\DataIntegrityMiddleware::class,
        ]);

        // Maintenance mode (Batch 28)
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
            // GATEKEEPER #2: IP blacklist → hanya homepage yang boleh diakses IP banned.
            \App\Http\Middleware\IpBlacklistMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();