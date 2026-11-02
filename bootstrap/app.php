<?php

use App\Exceptions\InvalidOrderTransitionException;
use App\Http\Middleware\DetectBotCrawler;
use App\Http\Middleware\EnforceIpBlacklist;
use App\Http\Middleware\EnsureCheckoutReady;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ProtectImages;
use App\Http\Middleware\RejectHoneypot;
use App\Http\Middleware\TrackVisitor;
use App\Http\Middleware\VerifyTurnstile;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            DetectBotCrawler::class,
            EnforceIpBlacklist::class,
            TrackVisitor::class,
            ProtectImages::class,
        ]);

        // EnforceIpBlacklist harus jalan SEBELUM auth (guest kena blokir IP
        // sebelum dialihkan ke /login) — DetectBotCrawler lebih dulu untuk is_bot.
        $middleware->priority([
            HandlePrecognitiveRequests::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            DetectBotCrawler::class,
            EnforceIpBlacklist::class,
            AuthenticatesRequests::class,
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
            AuthenticatesSessions::class,
            SubstituteBindings::class,
            Authorize::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'honeypot' => RejectHoneypot::class,
            'turnstile' => VerifyTurnstile::class,
            'checkout.ready' => EnsureCheckoutReady::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Transisi status order tidak sah = kesalahan input admin, bukan 500.
        $exceptions->render(function (InvalidOrderTransitionException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['status' => $e->getMessage()]);
        });
    })->create();
