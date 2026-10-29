<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

class TurnstileService
{
    /**
     * Verifikasi token Cloudflare Turnstile (PRD 8.1) — custom via Http::asForm() (AMANDEMEN).
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $secret = (string) config('services.turnstile.secret');

        if ($secret === '') {
            return true; // Turnstile tidak dikonfigurasi → lewati.
        }

        if ($token === '') {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        return (bool) $response->json('success');
    }
}
