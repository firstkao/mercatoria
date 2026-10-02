<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * GATEKEEPER #4 — Guest Access Barrier.
 *
 * Pengunjung yang BELUM login yang mencoba mengakses halaman sensitif
 * (keranjang, checkout) diarahkan ke login — KECUALI dia adalah legit
 * crawler bot (diverifikasi reverse DNS), supaya SEO tidak terganggu.
 *
 * Catatan desain: halaman detail produk SENGAJA tidak diblokir untuk
 * guest karena user meminta katalog & produk tetap global/publik
 * (lihat riwayat: "menu ini jg bisa diliat tanpa perlu login ya jd dia
 * global"). Kalau nanti mau produk ikut jadi barrier, cukup tambahkan
 * pola 'slug' ke $sensitivePaths di bawah.
 */
class GuestBarrierMiddleware
{
    /**
     * Pola path yang butuh login (guest → redirect).
     */
    private const SENSITIVE_PATHS = [
        'keranjang', 'keranjang/*',
        'checkout', 'checkout/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request); // member boleh lewat
        }

        if ($request->is(self::SENSITIVE_PATHS)) {
            // Crawler resmi tidak boleh kena barrier.
            if (SecurityHelper::isLegitCrawlerBot(SecurityHelper::getRealIp($request), $request->userAgent())) {
                return $next($request);
            }

            return redirect()->route('login')->with(
                'gatekeeper_notice',
                'Silakan login dulu untuk menggunakan keranjang dan checkout.'
            );
        }

        return $next($request);
    }
}
