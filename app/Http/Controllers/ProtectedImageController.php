<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProtectedImageController extends Controller
{
    /**
     * PRD 8.10 layer 4: anti-hotlink — akses langsung (tanpa referer) atau
     * hotlink dari domain lain menerima gambar decoy heavy watermark;
     * hanya halaman Mercatoria sendiri yang menerima gambar asli.
     */
    public function show(Request $request, string $filename): Response
    {
        $refererHost = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        $allowed = is_string($refererHost) && (
            strcasecmp($refererHost, 'mercatoria.id') === 0
            || strcasecmp($refererHost, (string) $request->getHost()) === 0
        );

        if (! $allowed) {
            return response($this->decoyPng(), 200, [
                'Content-Type' => 'image/png',
                'X-Protected-Image' => '1',
            ]);
        }

        $path = storage_path('app/private/products/'.basename($filename));
        abort_unless(is_file($path), 404);

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => mime_content_type($path) ?: 'image/png',
            'X-Protected-Image' => '1',
        ]);
    }

    /**
     * Sajikan file gambar tayang dari storage privat untuk URL /storage/* yang
     * dibangkitkan Storage::url() (disk local, root app/private). Hanya subfolder
     * gambar publik yang boleh tayang — master originals/ dan payment-proofs
     * tetap privat.
     */
    public function showFile(string $path): Response
    {
        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, '..'), 404);
        abort_unless(Str::startsWith($path, ['products/', 'games/', 'developers/', 'hero-slides/']), 404);

        $absolute = storage_path('app/private/'.$path);
        abort_unless(is_file($absolute), 404);

        return response((string) file_get_contents($absolute), 200, [
            'Content-Type' => mime_content_type($absolute) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Bukti bayar (PRD 17.7) — hanya admin (middleware admin), folder payment-proofs saja.
     * Dipakai link "Lihat" di halaman order admin.
     */
    public function showPaymentProof(string $path): Response
    {
        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, '..'), 404);

        $absolute = storage_path('app/private/payment-proofs/'.$path);
        abort_unless(is_file($absolute), 404);

        return response((string) file_get_contents($absolute), 200, [
            'Content-Type' => mime_content_type($absolute) ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /**
     * Gambar decoy: kotak gelap dengan teks heavy watermark.
     */
    private function decoyPng(): string
    {
        $im = imagecreatetruecolor(400, 300);
        $bg = imagecolorallocate($im, 40, 40, 40);
        $fg = imagecolorallocate($im, 230, 230, 230);
        imagefill($im, 0, 0, $bg);

        foreach ([60, 150, 240] as $y) {
            imagestring($im, 5, 20, $y, 'MERCATORIA.ID - DO NOT STEAL', $fg);
        }

        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }
}
