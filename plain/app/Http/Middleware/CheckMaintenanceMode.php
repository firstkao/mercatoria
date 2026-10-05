<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip kalau maintenance mode mati
        if (Setting::get('maintenance_enabled', '0') !== '1') {
            return $next($request);
        }

        // Admin yang sudah login tetap bisa akses
        if (auth('admin')->check()) {
            return $next($request);
        }

        // Skip route internal & health check
        if ($request->is('office', 'office/*', 'up', 'api/*')) {
            return $next($request);
        }

        // Bypass IP dari setting (admin bisa test tanpa login)
        $bypassIps = array_filter(array_map('trim', explode(',', (string) Setting::get('maintenance_bypass_ips', ''))));

        // ✅ BUG FIX: dulu pakai `$request->ip()`. Di situs yang berada di balik
        // Cloudflare / reverse proxy, nilai itu adalah IP Proxy-nya, bukan IP
        // admin — hasilnya daftar bypass TIDAK PERNAH cocok: admin mengaktifkan
        // maintenance lalu terkunci dari situsnya sendiri (dan sebaliknya, kalau
        // proxy-nya trusted semua, header X-Forwarded-For bisa dipalsukan siapa
        // saja untuk lolos dari halaman maintenance). Sekarang pakai helper yang
        // sama dengan Gatekeeper: SecurityHelper::getRealIp().
        $clientIp = \App\Helpers\SecurityHelper::getRealIp($request);

        if (! empty($bypassIps) && in_array($clientIp, $bypassIps, true)) {
            return $next($request);
        }

        return response()
            ->view('errors.maintenance', [
                'message' => Setting::get('maintenance_message', 'Kami sedang melakukan pemeliharaan. Coba lagi sebentar lagi.'),
            ], 503);
    }
}