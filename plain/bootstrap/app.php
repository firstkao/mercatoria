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

        // ============================================================
        // REDIRECT GUEST (belum login)
        // ============================================================
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('office') || $request->is('office/*')) {
                return route('admin.login');
            }
            return route('login');
        });

        // ============================================================
        // REDIRECT USER (sudah login)
        // ============================================================
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('office') || $request->is('office/*')) {
                return route('admin.dashboard');
            }
            return '/akun';
        });

        // ============================================================
        // MIDDLEWARE GLOBAL (append ke grup 'web')
        // ============================================================
        $middleware->web(append: [
            // (4) Tracking kunjungan — landing page, page views, UTM
            \App\Http\Middleware\TrackVisitor::class,

            // Maintenance mode (Batch 28)
            \App\Http\Middleware\CheckMaintenanceMode::class,

            // GATEKEEPER #2: IP blacklist — hanya homepage yang boleh
            // diakses IP banned.
            \App\Http\Middleware\IpBlacklistMiddleware::class,
        ]);

        // ============================================================
        // ALIAS MIDDLEWARE
        // ============================================================
        // Catatan:
        // - 'verified'        → dipakai di route group (Batch 32)
        // - 'data.integrity'  → dipakai di route /keranjang & /checkout
        //
        // Alias 'ip.blacklist' & 'guest.barrier' TIDAK didaftarkan karena:
        //   - IpBlacklistMiddleware sudah berjalan global via ->web(append)
        //   - GuestBarrierMiddleware tidak aktif karena group auth/verified
        //     sudah menutup /keranjang dan /checkout
        $middleware->alias([
            'verified'       => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'data.integrity' => \App\Http\Middleware\DataIntegrityMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
