<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AdminOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->session()->has('admin_2fa_id'), 403);

        return view('two-factor.verify', [
            'otpExpiresAt' => Cache::get('admin_otp_expires_at:'.(int) $request->session()->get('admin_2fa_id')),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = (int) $request->session()->get('admin_2fa_id');
        $user = User::find($userId);
        $expected = Cache::get('admin_otp:'.$userId);

        if (! $user || ! $user->isAdmin() || $expected === null || ! hash_equals((string) $expected, (string) $request->input('code'))) {
            // Batasi tebakan per OTP: 5 salah = OTP dihanguskan, admin harus login ulang.
            $attemptsKey = 'admin_otp_attempts:'.$userId;
            Cache::add($attemptsKey, 0, 300);

            if (Cache::increment($attemptsKey) >= 5) {
                Cache::forget('admin_otp:'.$userId);
                Cache::forget($attemptsKey);
            }

            return back()->withErrors(['code' => 'Kode OTP salah atau kedaluwarsa.']);
        }

        Cache::forget('admin_otp:'.$userId);
        Cache::forget('admin_otp_expires_at:'.$userId);
        $request->session()->forget('admin_2fa_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/office');
    }

    /**
     * Kirim OTP 6 digit, berlaku 5 menit (PRD 8.3).
     */
    public static function issue(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('admin_otp:'.$user->id, $code, 300);
        Cache::forget('admin_otp_attempts:'.$user->id);
        Cache::put('admin_otp_expires_at:'.$user->id, now()->addSeconds(300)->toIso8601String(), 300);
        $user->notify(new AdminOtpNotification($code));

        return $code;
    }
}
