<?php

namespace App\Http\Middleware;

use App\Services\Security\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function __construct(private readonly TurnstileService $turnstile) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->turnstile->shouldSkipForUnconfiguredDevelopment()) {
            return $next($request);
        }

        $token = $request->input('cf-turnstile-response');
        if (! is_string($token) || $token === '' || strlen($token) > 2048 || ! $this->turnstile->verify($token)) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Verifikasi keamanan gagal. Muat ulang halaman dan coba lagi.',
            ]);
        }

        return $next($request);
    }
}
