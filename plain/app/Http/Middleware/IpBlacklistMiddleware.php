<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * GATEKEEPER #2 — IP Blacklist Enforcement.
 *
 * IP yang masuk daftar hitam HANYA boleh mengakses homepage (/).
 * Selain itu di-redirect kembali ke /. Legit crawler bot dibypass
 * (diverifikasi reverse DNS, bukan cuma User-Agent).
 *
 * Sumber data: tabel `blacklisted_ips` (dibuat oleh migration
 * 2026_10_03_030000_create_blacklisted_ips_table.php). Kalau tabel
 * belum ada (DB dump lama), middleware jadi no-op supaya tidak 500.
 */
class IpBlacklistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Homepage & aset/endpoint sistem selalu boleh.
        if ($request->path() === '/' || $request->is('up', 'index.php')) {
            return $next($request);
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('blacklisted_ips')) {
                return $next($request); // skema lama → skip diam-diam
            }

            $ip = SecurityHelper::getRealIp($request);

            $blocked = DB::table('blacklisted_ips')
                ->where('ip_address', $ip)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();

            if ($blocked && !SecurityHelper::isLegitCrawlerBot($ip, $request->userAgent())) {
                return redirect('/')->with(
                    'gatekeeper_notice',
                    'Akses Anda dari jaringan ini dibatasi. Silakan hubungi admin.'
                );
            }
        } catch (\Throwable $e) {
            report($e); // jangan pernah robohkan situs karena masalah blacklist
        }

        return $next($request);
    }
}
