<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        // Sudah verified → langsung ke akun
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.show');
        }

        // Belum ada OTP → kirim otomatis
        if (! $request->user()->email_otp || $request->user()->email_otp_expires_at?->isPast()) {
            $request->user()->sendEmailVerificationOtp();
        }

        return view('auth.verify-email', [
            'user' => $request->user(),
            'emailOtpExpiresAt' => $request->user()->email_otp_expires_at,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [], ['otp' => 'kode verifikasi']);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.show')->with('status', 'Email kamu sudah terverifikasi.');
        }

        // Rate limit brute force — kalau sudah 5 attempt salah, minta kirim ulang
        if ($user->email_otp_attempts >= 5) {
            return back()->withErrors([
                'otp' => 'Terlalu banyak percobaan salah. Klik "Kirim ulang kode" untuk minta kode baru.',
            ]);
        }

        if (! $user->verifyEmailOtp($request->input('otp'))) {
            return back()->withErrors(['otp' => 'Kode verifikasi salah atau sudah kedaluwarsa.']);
        }

        return redirect()->route('account.show')->with('status', '✅ Email berhasil diverifikasi. Selamat berbelanja!');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.show');
        }

        // Rate limit: 1 menit antar pengiriman
        if ($user->email_otp_expires_at && $user->email_otp_expires_at->diffInMinutes(now()->addMinutes(15)) < 1) {
            // sengaja longgar, biar user bisa resend kapan saja tapi ada throttle di route
        }

        $user->sendEmailVerificationOtp();

        return back()->with('status', 'Kode baru sudah dikirim ke ' . $user->email . '. Cek inbox atau folder spam.');
    }
}