<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TwoFactorController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use App\Models\AuthLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // PRD 8.3: 2FA Admin wajib — OTP 6 digit via email, berlaku 5 menit.
        if ($user->isAdmin()) {
            Auth::logout();
            $request->session()->forget('password_hash_web');
            TwoFactorController::issue($user);
            $request->session()->put('admin_2fa_id', $user->id);

            return redirect('/2fa/verify');
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login' => now()])->save();

        AuthLog::record(
            AuthLog::ACTION_LOGIN_SUCCESS,
            (string) $request->input('email'),
            $user->id,
        );

        ActivityLog::record('login', 'Login berhasil');

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        AuthLog::record(
            AuthLog::ACTION_LOGOUT,
            (string) ($user?->email ?? ''),
            $user?->id,
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
