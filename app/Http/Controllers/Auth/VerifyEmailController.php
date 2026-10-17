<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    // Halaman /verify-email (tujuan redirect gate "Buat Pesanan")
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    // Klik link dari email
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $user->crush()->update(['verified_at' => $user->email_verified_at]);

        // Nanti kalau /profile/complete udah ada, otomatis diarahkan ke sana
        if ($user->tgl_lahir === null && Route::has('profile.complete')) {
            return redirect()->route('profile.complete');
        }

        return redirect()->route('dashboard')->with('status', 'Email berhasil diverifikasi.');
    }

    // Kirim (ulang) link verifikasi
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
