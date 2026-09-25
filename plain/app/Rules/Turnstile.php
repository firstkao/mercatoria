<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a Cloudflare Turnstile token with Cloudflare's siteverify endpoint.
 */
class Turnstile implements ValidationRule
{
    public function __construct(private ?string $ipAddress = null) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = 'Verifikasi keamanan gagal. Muat ulang halaman lalu coba lagi.';

        if (! is_string($value) || $value === '') {
            $fail($message);

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $value,
                    'remoteip' => $this->ipAddress,
                ]);
        } catch (ConnectionException) {
            $fail($message);

            return;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            $fail($message);
        }
    }
}