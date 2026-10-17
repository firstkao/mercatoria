<?php

namespace App\Http\Middleware;

use App\Models\SpammerIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSpammerIps
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        if ($path === '' || $path === 'up' || ($path === 'register' && $request->isMethod('POST'))) {
            return $next($request);
        }

        $ip = $request->ip();
        if ($ip && SpammerIp::query()
            ->where('ip_address', $ip)
            ->whereHas('spammer', fn ($query) => $query->where('status', 'blocked'))
            ->exists()) {
            return redirect()->route('home')->with('blocked_spammer_ip', true);
        }

        return $next($request);
    }
}
