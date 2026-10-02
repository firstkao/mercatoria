<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * GATEKEEPER #5 — Data Integrity Force.
 *
 * Saat user login dan mencoba belanja (keranjang/checkout), paksa dia
 * melengkapi profil billing (nama, alamat, kota). Kalau kosong ATAU
 * terdeteksi junk data (asdasd/qwerty/12345/dst), lempar ke halaman
 * edit profil dengan pesan error yang jelas.
 *
 * Diterapkan HANYA pada jalur transaksi supaya browsing katalog tetap
 * nyaman (sesuai prinsip UX) — tapi bisa diluaskan lewat route group.
 */
class DataIntegrityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin tidak punya akun web (guard terpisah) — skip total.
        $user = $request->user();
        if ($user === null || $request->is('office', 'office/*')) {
            return $next($request);
        }

        $incomplete = SecurityHelper::isJunkData(
            $user->full_name,
            null,
            $user->street_address,
            $user->city
        );

        if ($incomplete && !$request->routeIs('account.profile.*')) {
            return redirect()->route('account.profile.edit')->with(
                'gatekeeper_error',
                '⛔ Data Tidak Valid! Sistem mendeteksi profil kamu tidak lengkap '
                    . 'atau menggunakan data palsu. Segera isi nama asli dan alamat valid. '
                    . 'Akun akan dibatasi jika tidak diperbaiki.'
            );
        }

        return $next($request);
    }
}
