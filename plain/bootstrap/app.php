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

        // Maintenance mode (Batch 28)
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();