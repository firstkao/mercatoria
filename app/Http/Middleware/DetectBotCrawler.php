<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectBotCrawler
{
    /**
     * PRD 8.8: daftar bot whitelist (search engine, AI, social preview).
     *
     * @var array<int, string>
     */
    private const CRAWLERS = [
        'Googlebot', 'Bingbot', 'YandexBot', 'Baiduspider', 'DuckDuckBot', 'Sogou',
        'GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'anthropic-ai', 'PerplexityBot',
        'facebookexternalhit', 'Twitterbot', 'WhatsApp', 'Pinterestbot', 'LinkedInBot',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCrawler($request)) {
            $request->attributes->set('is_bot', true);
        }

        return $next($request);
    }

    public function isCrawler(Request $request): bool
    {
        $agent = strtolower((string) $request->userAgent());

        foreach (self::CRAWLERS as $crawler) {
            if (str_contains($agent, strtolower($crawler))) {
                return true;
            }
        }

        return false;
    }
}
