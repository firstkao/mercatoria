<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileService
{
    public function verify(string $token): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (! $secret) {
            if ((app()->runningUnitTests() || ! app()->isProduction())
                && ! config('services.turnstile.site_key')) {
                return true;
            }

            Log::error('Cloudflare Turnstile secret key is not configured.');

            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ]);
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Cloudflare Turnstile verification request failed.');

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Cloudflare Turnstile returned an unsuccessful HTTP response.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        if ($response->json('success') !== true) {
            Log::warning('Cloudflare Turnstile rejected a verification token.', [
                'error_codes' => $response->json('error-codes', []),
            ]);

            return false;
        }

        $expectedHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $actualHost = $response->json('hostname');
        if (! is_string($expectedHost) || ! is_string($actualHost)
            || ! hash_equals(strtolower($expectedHost), strtolower($actualHost))) {
            Log::warning('Cloudflare Turnstile hostname did not match the configured application URL.', [
                'expected_host' => $expectedHost,
                'actual_host' => $actualHost,
            ]);

            return false;
        }

        return true;
    }

    public function isConfigured(): bool
    {
        return (bool) config('services.turnstile.site_key')
            && (bool) config('services.turnstile.secret_key');
    }

    public function shouldSkipForUnconfiguredDevelopment(): bool
    {
        return ! config('services.turnstile.site_key')
            && ! config('services.turnstile.secret_key')
            && (app()->runningUnitTests() || ! app()->isProduction());
    }
}
