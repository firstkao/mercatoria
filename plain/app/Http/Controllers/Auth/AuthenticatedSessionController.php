<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        ActivityLog::record($request->user(), 'login', $request);

        // Kalau email belum verified → arahkan ke halaman verify
        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Buang url.intended sisa dari guard admin — biar nggak nyasar ke /office
        $request->session()->forget('url.intended');

        return redirect()->route('account.show');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record($request->user(), 'logout', $request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}