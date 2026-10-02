<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
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

        // GATEKEEPER #5 (Data Integrity Force): saat login, paksa cek profil
        // billing. Kosong ATAU junk data (asdasd/qwerty/12345/gajelas/dst)
        // → langsung lempar ke edit profil dengan pesan error.
        $u = $request->user();
        if (SecurityHelper::isJunkData($u->full_name, null, $u->street_address, $u->city)) {
            return redirect()->route('account.profile.edit')->with(
                'gatekeeper_error',
                '⛔ Data Tidak Valid! Sistem mendeteksi profil kamu tidak lengkap '
                    . 'atau menggunakan data palsu. Segera isi nama asli dan alamat valid. '
                    . 'Akun akan dibatasi jika tidak diperbaiki.'
            );
        }

        // GATEKEEPER #1: simpan IP asli (valid Cloudflare CIDR, bukan header mentah).
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_login_ip')) {
                $u->forceFill(['last_login_ip' => SecurityHelper::getRealIp($request)])->saveQuietly();
            }
        } catch (\Throwable $e) {
            report($e); // kolom belum ada di DB lama → jangan gagalkan login
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