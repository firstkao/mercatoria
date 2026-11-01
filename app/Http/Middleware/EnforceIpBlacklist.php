<?php

namespace App\Http\Middleware;

use App\Models\SpammerIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceIpBlacklist
{
    /**
     * PRD 8.5: Smart Scoped IP Blacklist (anti false-positive CGNAT).
     * IP di spammer_ips (TTL 30 hari) hanya boleh akses Beranda + /login + /logout;
     * /register dan katalog diblokir. Bot crawler & Customer sah (MVP/Ex/ONS) dikecualikan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('is_bot')) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->role === 'customer'
            && in_array($user->customer?->current_state, ['MVP', 'Ex', 'ONS', 'Grace'], true)) {
            return $next($request);
        }

        $ip = get_real_ip($request);

        $isBannedIp = $ip && SpammerIp::query()
            ->where('ip_address', $ip)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($isBannedIp) {
            if ($request->is('/') || $request->is('login') || $request->is('logout')) {
                return $next($request);
            }

            return redirect('/')->with('error', 'Akses pendaftaran dan katalog dibatasi dari jaringan ini.');
        }

        return $next($request);
    }
}
