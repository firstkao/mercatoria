<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a Cloudflare Turnstile token with Cloudflare's siteverify endpoint.
 */
class Turnstile implements ValidationRule, ImplicitRule
{
    public function __construct(private ?string $ipAddress = null) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = (string) (config('services.turnstile.secret_key') ?? '');
        $siteKey = (string) (config('services.turnstile.site_key') ?? '');

        // Turnstile hanya bisa ditegakkan kalau KEDUA kunci terisi. Kalau salah
        // satu kosong, widget tidak pernah merender / mengirim token, sehingga
        // menolak request hanya akan mengunci SEMUA orang dari login (admin &
        // user) tanpa cara memperbaikinya dari UI. Karena itu: fail-open saat
        // belum dikonfigurasi, tetap fail-closed saat sudah dikonfigurasi.
        if ($secretKey === '' || $siteKey === '') {
            return;
        }

        $message = 'Verifikasi keamanan gagal. Muat ulang halaman lalu coba lagi.';

        if (! is_string($value) || $value === '') {
            $fail($message);

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
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