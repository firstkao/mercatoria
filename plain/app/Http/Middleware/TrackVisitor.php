<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip request non-GET, AJAX, dan area admin
        if (! $request->isMethod('GET')
            || $request->ajax()
            || $request->is('office/*')
            || $request->is('___*')
        ) {
            return $next($request);
        }

        // Landing page: set sekali saat sesi baru dimulai
        if (! session()->has('landing_page')) {
            session(['landing_page' => $request->fullUrl()]);

            // UTM source juga disimpan di session sebagai fallback
            if ($utm = $request->input('utm_source')) {
                session(['utm_source' => strtolower(substr($utm, 0, 50))]);
            }
        }

        // Counter page views
        session(['page_views' => (int) session('page_views', 0) + 1]);

        return $next($request);
    }
}
