<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        $request->session()->regenerate();

        $request->user()->forceFill(['last_login' => now()])->save();

        AuthLog::record(
            AuthLog::ACTION_LOGIN_SUCCESS,
            (string) $request->input('email'),
            $request->user()->id,
        );

        return redirect()->intended('/');
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
