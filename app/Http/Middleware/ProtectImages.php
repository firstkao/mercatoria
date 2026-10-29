<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectImages
{
    /**
     * PRD 8.10 layer 2: tanda respons halaman yang aset fotonya
     * dilindungi Image Shield (klik-kanan/drag dicegah oleh image-protection.js).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Protected-Image', '1');

        return $response;
    }
}
