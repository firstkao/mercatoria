<?php

namespace App\Http\Middleware;

use App\Models\VisitorTracking;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public const ATTRIBUTION_COOKIE = 'referral_attribution';

    public const TRACKED_COOKIE = 'visitor_tracked';

    public function handle(Request $request, Closure $next): Response
    {
        // PRD 12: klik link referral/share (?ref=) → cookie atribusi 30 hari.
        if ($request->isMethod('GET') && $request->filled('ref')) {
            Cookie::queue(self::ATTRIBUTION_COOKIE, json_encode([
                'code' => substr((string) $request->query('ref'), 0, 40),
                'set_at' => now()->toIso8601String(),
            ]), 60 * 24 * 30);
        }

        $response = $next($request);

        if ($this->shouldTrack($request)) {
            VisitorTracking::create([
                'source' => $this->source($request),
                'medium' => $this->medium($request),
                'landing_page' => substr($request->fullUrl(), 0, 500),
                'user_id' => $request->user()?->id,
            ]);

            Cookie::queue(self::TRACKED_COOKIE, '1', 60 * 24 * 30);
        }

        return $response;
    }

    /**
     * PRD 12: hanya kunjungan PERTAMA; skip login, register, cart, checkout,
     * my-account (dashboard/profil/order), admin, dan AJAX.
     */
    private function shouldTrack(Request $request): bool
    {
        // PRD 8.8: bot crawler tidak ditrack sebagai pengunjung.
        if ($request->attributes->get('is_bot')) {
            return false;
        }

        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($request->is(
            'login*', 'register*', 'cart*', 'checkout*',
            'dashboard', 'profile*', 'orders*', 'office/*', '2fa/*', 'email/*', 'password*'
        )) {
            return false;
        }

        return ! $request->hasCookie(self::TRACKED_COOKIE);
    }

    private function source(Request $request): ?string
    {
        if ($request->filled('utm_source')) {
            return substr((string) $request->query('utm_source'), 0, 100);
        }

        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return $host ? substr($host, 0, 100) : 'direct';
    }

    private function medium(Request $request): ?string
    {
        if ($request->filled('utm_medium')) {
            return substr((string) $request->query('utm_medium'), 0, 100);
        }

        return $request->filled('ref') ? 'referral' : null;
    }
}
