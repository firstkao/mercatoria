<?php

namespace App\Http\Middleware;

use App\Services\Security\CrawlerBotService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guest Barrier (PRD 7.2): katalog, developer, kategori, detail produk, dan search
 * mengarahkan guest manusia langsung ke /login tanpa modal. Crawler resmi (PRD 8.8) dikecualikan.
 */
class RedirectGuestsToLogin
{
    public function __construct(private readonly CrawlerBotService $bots) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() || $this->bots->isWhitelisted($request)) {
            return $next($request);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('login');
    }
}
