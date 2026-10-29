<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCheckoutReady
{
    /**
     * PRD 48.4: sebelum Buat Pesanan / Konfirmasi Pembayaran —
     * email wajib terverifikasi + tgl lahir wajib terisi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('status', 'Verifikasi email dulu biar bisa order.');
        }

        if (! $user->hasCompletedProfile()) {
            return redirect()->route('profile.complete')
                ->with('status', 'Lengkapi tanggal lahir dulu sebelum membuat pesanan.');
        }

        return $next($request);
    }
}
