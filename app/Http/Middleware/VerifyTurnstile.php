<?php

namespace App\Http\Middleware;

use App\Services\Security\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function __construct(private TurnstileService $turnstile) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->turnstile->verify((string) $request->input('cf-turnstile-response'), $request->ip())) {
            return back()
                ->withErrors(['cf-turnstile-response' => 'Verifikasi anti-bot gagal. Silakan coba lagi.'])
                ->withInput($request->only('email'));
        }

        return $next($request);
    }
}
