<?php

use App\Http\Middleware\EnforceIpBlacklist;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\VerifyTurnstile;
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
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '')),
        )));
        $middleware->trustProxies(
            at: $trustedProxies,
            headers: Request::HEADER_X_FORWARDED_FOR,
        );
        $middleware->appendToGroup('web', EnforceIpBlacklist::class);
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'ensure.verified' => EnsureEmailVerified::class,
            'ensure.profile' => EnsureProfileComplete::class,
            'turnstile' => VerifyTurnstile::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
