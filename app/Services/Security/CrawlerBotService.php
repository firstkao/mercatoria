<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Whitelist crawler resmi (PRD 8.8). Bot mesin pencari wajib lolos rDNS + forward DNS,
 * bot AI dan social preview cukup dicocokkan lewat User-Agent. Hasil verifikasi di-cache 1 hari per IP.
 */
class CrawlerBotService
{
    private const SEARCH_ENGINES = [
        'googlebot' => ['.googlebot.com', '.google.com'],
        'bingbot' => ['.search.msn.com'],
        'yandexbot' => ['.yandex.ru', '.yandex.net', '.yandex.com'],
        'baiduspider' => ['.baidu.com', '.baidu.jp'],
        'duckduckbot' => ['.duckduckgo.com'],
        'sogou' => ['.sogou.com'],
    ];

    private const DIRECT_WHITELIST = [
        'gptbot', 'chatgpt-user', 'oai-searchbot', 'claudebot', 'anthropic-ai', 'perplexitybot',
        'facebookexternalhit', 'twitterbot', 'whatsapp', 'pinterestbot', 'linkedinbot',
    ];

    public function isWhitelisted(Request $request): bool
    {
        $userAgent = mb_strtolower((string) $request->userAgent());
        if ($userAgent === '') {
            return false;
        }

        foreach (self::DIRECT_WHITELIST as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }

        foreach (self::SEARCH_ENGINES as $bot => $domains) {
            if (str_contains($userAgent, $bot)) {
                return $this->verifySearchEngine((string) $request->ip(), $domains);
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $domains
     */
    private function verifySearchEngine(string $ip, array $domains): bool
    {
        if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return Cache::remember('crawler-bot:'.$ip, now()->addDay(), function () use ($ip, $domains): bool {
            $host = gethostbyaddr($ip);
            if (! is_string($host) || $host === $ip) {
                return false;
            }

            $host = mb_strtolower($host);
            $matchesDomain = collect($domains)->contains(fn (string $domain): bool => str_ends_with($host, $domain));
            if (! $matchesDomain) {
                return false;
            }

            $resolved = gethostbynamel($host) ?: [];

            return in_array($ip, $resolved, true);
        });
    }
}
