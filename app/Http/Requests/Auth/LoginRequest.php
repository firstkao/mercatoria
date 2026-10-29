<?php

namespace App\Http\Requests\Auth;

use App\Models\AuthLog;
use App\Models\Spammer;
use App\Models\SpammerIp;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Maksimal percobaan gagal sebelum akun dikunci (PRD 7.5 / 8.1).
     */
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Durasi kunci akun dalam detik (15 menit).
     */
    private const LOCKOUT_SECONDS = 900;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Cek lockout & spammer, lalu coba autentikasi (PRD 7.5, 8.1, 10.3).
     */
    public function authenticate(): void
    {
        $email = Str::lower((string) $this->input('email'));
        $lockKey = $this->lockoutKey($email);

        $this->ensureIsNotLockedOut($lockKey);

        if (Spammer::blocksIdentity($email, null)) {
            AuthLog::record(AuthLog::ACTION_LOGIN_FAILED, $email, null, 'identitas diblokir');

            // PRD 10.5: IP yang mencoba login dengan identitas ter-block ikut dicatat.
            if ($ip = get_real_ip($this)) {
                SpammerIp::recordAttempt($ip, (string) $this->userAgent());
            }

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak dapat digunakan untuk login.',
            ]);
        }

        if (! auth()->attempt([
            'email' => $email,
            'password' => (string) $this->input('password'),
        ], $this->boolean('remember'))) {
            RateLimiter::hit($lockKey, self::LOCKOUT_SECONDS);

            AuthLog::record(AuthLog::ACTION_LOGIN_FAILED, $email, null, 'kredensial salah');

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($lockKey);
    }

    private function ensureIsNotLockedOut(string $lockKey): void
    {
        if (! RateLimiter::tooManyAttempts($lockKey, self::MAX_FAILED_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($lockKey);

        AuthLog::record(AuthLog::ACTION_LOGIN_FAILED, $this->input('email'), null, 'akun terkunci');

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan gagal. Akun terkunci selama 15 menit ('.$seconds.' detik lagi).',
        ]);
    }

    private function lockoutKey(string $email): string
    {
        return 'login-lock:'.$email;
    }
}
