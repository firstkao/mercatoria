<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle submit form login
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // ─── 2. Rate Limit (5x/menit per IP+email) ───
        $throttleKey = strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            AuthLog::create([
                'user_id' => null,
                'email_attempt' => $validated['email'],
                'action' => 'lockout',
                'reason' => 'too_many_attempts',
                'ip_address' => function_exists('get_real_ip') ? get_real_ip() : $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // ─── 3. Attempt Login ────────────────────────
        $remember = $request->boolean('remember');
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials, $remember)) {
            // Gagal login → hit rate limit + log
            RateLimiter::hit($throttleKey, 15 * 60);

            AuthLog::create([
                'user_id' => null,
                'email_attempt' => $validated['email'],
                'action' => 'login_failed',
                'reason' => 'invalid_credentials',
                'ip_address' => function_exists('get_real_ip') ? get_real_ip() : $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        // ─── 4. Sukses Login ─────────────────────────
        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user = Auth::user();
        $user->update(['last_login' => now()]);

        AuthLog::create([
            'user_id' => $user->id,
            'email_attempt' => $validated['email'],
            'action' => 'login_success',
            'reason' => null,
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // ─── 5. Regenerate session ───────────────────
        $request->session()->regenerate();

        // ─── 6. Redirect ke intended URL atau dashboard ──
        return redirect()->intended(route('dashboard'))
            ->with('ga4_events', [[
                'name' => 'login',
                'params' => ['method' => 'email'],
            ]]);
    }
}
