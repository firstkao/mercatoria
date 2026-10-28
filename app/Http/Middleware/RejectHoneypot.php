<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectHoneypot
{
    /**
     * Field tersembunyi yang menandakan bot (PRD 8.2). Terisi = tolak diam-diam.
     */
    private const HONEYPOT_FIELDS = ['website', 'url', 'phone2'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::HONEYPOT_FIELDS as $field) {
            if (filled($request->input($field))) {
                return redirect()->back();
            }
        }

        return $next($request);
    }
}
