<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOtpController extends Controller
{
    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan login kembali untuk meminta kode OTP.']);
        }

        if (! $user->admin_otp_expires_at?->isFuture()) {
            $this->clearPending($request, $user);

            return redirect()->route('login')->withErrors(['email' => 'Kode OTP kedaluwarsa. Silakan login kembali.']);
        }

        return view('auth.admin-otp', [
            'email' => $user->email,
            'expiresAt' => $user->admin_otp_expires_at,
            'securityEmail' => config('mercatoria.store.security_email'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP tidak valid. Silakan login kembali.']);
        }

        $key = 'admin-otp:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            AuthLog::record('admin_otp_lockout', $user->email, 'too_many_attempts', $user->id);
            $this->clearPending($request, $user);

            return redirect()->route('login')->withErrors(['email' => 'Terlalu banyak percobaan kode OTP. Silakan login kembali.']);
        }

        $verified = DB::transaction(function () use ($user, $validated): bool {
            $lockedUser = User::query()->lockForUpdate()->find($user->id);
            if (! $lockedUser
                || ! $lockedUser->isAdmin()
                || ! $lockedUser->admin_otp_expires_at?->isFuture()
                || ! $lockedUser->admin_otp
                || ! Hash::check($validated['otp'], $lockedUser->admin_otp)) {
                return false;
            }

            $lockedUser->forceFill([
                'admin_otp' => null,
                'admin_otp_expires_at' => null,
                'last_login' => now(),
            ])->save();

            return true;
        });

        if (! $verified) {
            RateLimiter::hit($key, 15 * 60);
            AuthLog::record('admin_otp_failed', $user->email, 'invalid_or_expired_code', $user->id);
            throw ValidationException::withMessages(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        RateLimiter::clear($key);
        Auth::login($user, (bool) $request->session()->pull('pending_admin_otp_remember', false));
        $request->session()->forget('pending_admin_otp_user_id');
        $request->session()->regenerate();
        AuthLog::record('admin_login_success', $user->email, null, $user->id);

        return redirect()->intended(route('office.dashboard'))
            ->with('ga4_events', [['name' => 'login', 'params' => ['method' => 'email_otp']]]);
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('pending_admin_otp_user_id');
        if (! is_int($id) && ! ctype_digit((string) $id)) {
            return null;
        }

        $user = User::query()->find((int) $id);

        return $user?->isAdmin() ? $user : null;
    }

    private function clearPending(Request $request, User $user): void
    {
        $user->forceFill(['admin_otp' => null, 'admin_otp_expires_at' => null])->save();
        $request->session()->forget(['pending_admin_otp_user_id', 'pending_admin_otp_remember']);
    }
}
