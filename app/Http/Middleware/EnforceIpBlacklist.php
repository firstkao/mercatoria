<?php

namespace App\Http\Middleware;

use App\Models\SpammerIp;
use App\Services\Security\CrawlerBotService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceIpBlacklist
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        $isLoginOrLogout = in_array($path, ['login', 'logout'], true);
        $isRegisterSubmission = $path === 'register' && $request->isMethod('POST');

        if ($path === '' || $path === 'up' || $isLoginOrLogout || $isRegisterSubmission
            || app(CrawlerBotService::class)->isWhitelisted($request)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user?->isCustomer()
            && in_array($user->customer?->current_state, ['mvp', 'ex', 'ons'], true)) {
            return $next($request);
        }

        $ip = $request->ip();
        if ($ip && SpammerIp::query()
            ->where('ip_address', $ip)
            ->active()
            ->whereHas('spammer', fn ($query) => $query->where('status', 'blocked'))
            ->exists()) {
            return redirect()->route('home')->with('blocked_spammer_ip', true);
        }

        return $next($request);
    }
}
