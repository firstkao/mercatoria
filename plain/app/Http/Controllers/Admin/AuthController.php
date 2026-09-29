<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\AdminLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        // AdminLoginRequest::authenticate() sudah handle rate limit + attempt
        $request->authenticate();

        $request->session()->regenerate();

        // Buang url.intended sisa dari guard web — biar nggak nyasar ke halaman user
        $request->session()->forget('url.intended');

        AdminLog::record('login');

        // Langsung ke dashboard admin (JANGAN pakai intended())
        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}