<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Crush;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($hash !== sha1($user->getEmailForVerification())) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/');
        }

        // PRD 7.4: "Setelah verifikasi -> role = Crush" (naik ke Customer hanya lewat order terverifikasi).
        $user->forceFill([
            'email_verified_at' => now(),
            'role' => $user->isAdmin() ? $user->role : User::ROLE_CRUSH,
        ])->save();

        if (! $user->isAdmin()) {
            Crush::firstOrCreate(['user_id' => $user->id]);
        }

        return $request->user()
            ? redirect()->route('dashboard')
            : redirect('/login');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return redirect('/email/verify')->with('status', 'Link verifikasi baru telah dikirim ke email Anda.');
    }
}
