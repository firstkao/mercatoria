<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * MERCATORIA GATEKEEPER (adaptasi v17.4 dari WordPress ke Laravel).
 *
 * Menyediakan:
 *  - getRealIp()          : IP asli di balik Cloudflare (valid CIDR, bukan header mentah).
 *  - isLegitCrawlerBot()  : verifikasi bot resmi via reverse DNS (bukan cuma User-Agent).
 *  - isJunkData()         : deteksi data profil sampah ("asdasd", "qwerty", dst).
 */
class SecurityHelper
{
    /**
     * Range CIDR publik Cloudflare (IPv4 + IPv6).
     * Sumber: https://www.cloudflare.com/ips/ — refresh berkala bila perlu.
     */
    public const CLOUDFLARE_CIDRS = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Pola domain reverse-DNS yang sah untuk crawler resmi.
     */
    private const CRAWLER_DOMAINS = [
        '/\.(googlebot|google)\.com$/i',
        '/\.search\.msn\.com$/i',
        '/\.crawl\.yahoo\.net$/i',
        '/\.duckduckbot\.com$/i',
        '/\.applebot\.apple\.com$/i',
        '/\.yandex\.com$|\.yandex\.ru$/i',
    ];

    /**
     * Ambil IP KLIEN SESUNGGUHNYA, aman terhadap pemalsuan header.
     *
     * Logika Gatekeeper:
     *  1. Jika koneksi langsung (REMOTE_ADDR) BUKAN IP Cloudflare,
     *     abaikan X-Forwarded-For sepenuhnya (header itu bisa dipalsukan siapa saja).
     *  2. Jika REMOTE_ADDR adalah IP Cloudflare, pakai entri TERAKHIR yang valid
     *     dari XFF (rantai paling dekat dengan origin = IP klien asli),
     *     tetap divalidasi format IP.
     */
    public static function getRealIp(Request $request): string
    {
        $rawIp = $_SERVER['REMOTE_ADDR'] ?? ($request->ip() ?? '0.0.0.0');

        if (!self::isCloudflareIp($rawIp)) {
            // Tidak lewat Cloudflare → REMOTE_ADDR adalah kebenaran tunggal.
            return $request->ip() ?: $rawIp;
        }

        // Lewat Cloudflare → baca X-Forwarded-For tapi hanya IP berformat valid.
        $xff = $request->server('HTTP_X_FORWARDED_FOR')
            ?? (string) $request->header('X-Forwarded-For', '');

        foreach (array_reverse(array_map('trim', explode(',', $xff))) as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return $rawIp;
    }

    /**
     * Cek apakah sebuah IP termasuk range publik Cloudflare.
     */
    public static function isCloudflareIp(?string $ip): bool
    {
        if ($ip === null || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        foreach (self::CLOUDFLARE_CIDRS as $cidr) {
            if (self::ipInCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pencocokan CIDR manual (mendukung IPv4 & IPv6) tanpa ekstensi tambahan.
     */
    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false; // beda keluarga (v4 vs v6)
        }

        $mask = str_repeat('1', $bits) . str_repeat('0', strlen($ipBin) * 8 - $bits);
        $maskBin = '';
        foreach (str_split($mask, 8) as $byte) {
            $maskBin .= chr(bindec($byte));
        }

        return ($ipBin & $maskBin) === ($subnetBin & $maskBin);
    }

    /**
     * Verifikasi crawler resmi dengan TRIPLE CHECK:
     *  1. User-Agent cocok dengan pola bot terkenal,
     *  2. Reverse DNS (gethostbyaddr) jatuh ke domain resmi mereka,
     *  3. Forward DNS (gethostbyname) konsisten bolak-balik (anti-spoofing).
     *
     * Hasil dicache 6 jam per IP supaya lookup DNS tidak membebani tiap request.
     */
    public static function isLegitCrawlerBot(?string $ip = null, ?string $userAgent = null): bool
    {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $userAgent = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');

        if (!$ip || !$userAgent) {
            return false;
        }

        // Step 1 — cepat: cek UA dulu sebelum lookup DNS (hemat CPU).
        $uaPattern = '/(Googlebot|Bingbot|Slurp|DuckDuckBot|Baiduspider|YandexBot|Applebot|facebookexternalhit)/i';
        if (!preg_match($uaPattern, $userAgent)) {
            return false;
        }

        return Cache::remember('gatekeeper:crawler:' . sha1($ip), 6 * 3600, function () use ($ip) {
            // Step 2 — reverse DNS.
            $hostname = @gethostbyaddr($ip);
            if (!$hostname || $hostname === $ip) {
                return false;
            }

            $matched = false;
            foreach (self::CRAWLER_DOMAINS as $pattern) {
                if (preg_match($pattern, strtolower($hostname))) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }

            // Step 3 — forward DNS harus kembali ke IP asal.
            $resolved = @gethostbyname($hostname);

            return $resolved !== $hostname && $resolved === $ip;
        });
    }

    /**
     * Deteksi data profil sampah ala Gatekeeper WP v17.4.
     *
     * Menganggap data JUNK jika salah satu field wajib kosong ATAU cocok pola
     * ketikan asal-asalan / placeholder.
     */
    public static function isJunkData(
        ?string $firstNameOrFullName,
        ?string $lastName = null,
        ?string $address = null,
        ?string $city = null
    ): bool {
        // Field wajib kosong → belum lengkap → junk secara gatekeeper.
        foreach ([$firstNameOrFullName, $address, $city] as $value) {
            if ($value === null || trim((string) $value) === '') {
                return true;
            }
        }

        $junkPatterns = [
            '/^(as+da+|asd+|qwerty|asdf+|zxcv|dfgh|abcde|abc123|aaa+|xxx+|test|testing|sample|dummy)$/i',
            '/^\d{3,}$/',                       // murni angka pendek: 12345, 123456
            '/^(gajelas|ga\s*jelas|ngasal|palsu|kosong|belum|tdk|tidak tahu)$/i',
            '/([a-z])\1{3,}/i',                 // aaaaa, fffffff — keyboard smash
            '/^[bcdfghjklmnpqrstvwxyz]{4,}$/i', // deretan konsonan acak: qwrt, dfgh
        ];

        $nameFields = array_filter([$firstNameOrFullName, $lastName]);
        foreach ($nameFields as $field) {
            $clean = trim((string) $field);
            foreach ($junkPatterns as $pattern) {
                if (preg_match($pattern, $clean)) {
                    return true;
                }
            }
            if (mb_strlen($clean) < 2) {
                return true; // nama < 2 karakter = hampir pasti sampah
            }
        }

        if (mb_strlen(trim((string) $address)) < 5) {
            return true; // alamat terlalu pendek untuk dikirim kurir
        }

        return false;
    }
}
