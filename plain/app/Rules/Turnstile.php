<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a Cloudflare Turnstile token with Cloudflare's siteverify endpoint.
 *
 * CATATAN PENTING: rule ini mengimplementasikan ValidationRule (interface
 * modern) SEKALIGUS ImplicitRule. ImplicitRule diperlukan supaya rule tetap
 * dijalankan walau field `cf-turnstile-response` tidak dikirim sama sekali —
 * tanpa itu, bot bisa melewati CAPTCHA hanya dengan menghilangkan field.
 *
 * Di Laravel, `Illuminate\Contracts\Validation\ImplicitRule` mewarisi interface
 * LAMA `Illuminate\Contracts\Validation\Rule`, sehingga passes() dan message()
 * WAJIB ikut diimplementasikan. Tanpa keduanya, kelas ini fatal error
 * ("contains 2 abstract methods ...") dan SEMUA request login/register mati.
 */
class Turnstile implements ValidationRule, ImplicitRule
{
    private const FAIL_MESSAGE = 'Verifikasi keamanan gagal. Muat ulang halaman lalu coba lagi.';

    public function __construct(private ?string $ipAddress = null) {}

    /**
     * Jalankan validasi (interface modern ValidationRule).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = (string) (config('services.turnstile.secret_key') ?? '');
        $siteKey = (string) (config('services.turnstile.site_key') ?? '');

        // Turnstile hanya bisa ditegakkan kalau KEDUA kunci terisi. Kalau salah
        // satu kosong, widget tidak pernah merender / mengirim token, sehingga
        // menolak request hanya akan mengunci SEMUA orang dari login. Maka:
        // fail-open saat belum dikonfigurasi, fail-closed saat sudah.
        if ($secretKey === '' || $siteKey === '') {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail(self::FAIL_MESSAGE);

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
            $fail(self::FAIL_MESSAGE);

            return;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            $fail(self::FAIL_MESSAGE);
        }
    }

    /**
     * Interface lama Rule (diwajibkan oleh ImplicitRule).
     * Delegasikan ke validate() supaya hasilnya identik.
     *
     * @param  mixed  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $failed = false;

        $this->validate((string) $attribute, $value, function () use (&$failed): void {
            $failed = true;
        });

        return ! $failed;
    }

    /**
     * Interface lama Rule (diwajibkan oleh ImplicitRule).
     */
    public function message(): string
    {
        return self::FAIL_MESSAGE;
    }
}
