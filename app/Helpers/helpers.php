<?php

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Ambil nilai setting dari database (dengan cache) atau fallback ke default.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            $settings = Cache::remember('app_settings_all', 600, function (): array {
                return Setting::pluck('value', 'key')->all();
            });

            return $settings[$key] ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }
}

if (! function_exists('format_rp')) {
    /**
     * Format angka ke Rupiah (contoh: Rp255.000).
     */
    function format_rp(float|int|string $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('order_status_label')) {
    /**
     * Label tampilan status order (config/order_statuses.php — satu sumber).
     */
    function order_status_label(string $status): string
    {
        return config('order_statuses.'.$status.'.label') ?? ucwords(str_replace('_', ' ', $status));
    }
}

if (! function_exists('order_status_color')) {
    /**
     * Kelas warna badge status order (config/order_statuses.php — satu sumber).
     */
    function order_status_color(string $status): string
    {
        return config('order_statuses.'.$status.'.color') ?? 'bg-paper text-ink/70';
    }
}

if (! function_exists('get_real_ip')) {
    /**
     * IP asli client (PRD 8.7): HTTP_X_FORWARDED_FOR → HTTP_X_REAL_IP → REMOTE_ADDR.
     * Validasi filter_var, skip IP private & reserved; XFF bisa berupa daftar.
     */
    function get_real_ip(?Request $request = null): ?string
    {
        $request ??= request();

        foreach ([
            (string) $request->server('HTTP_X_FORWARDED_FOR', ''),
            (string) $request->server('HTTP_X_REAL_IP', ''),
            (string) $request->server('REMOTE_ADDR', ''),
        ] as $candidates) {
            foreach (explode(',', $candidates) as $candidate) {
                $ip = trim($candidate);

                if ($ip !== ''
                    && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }

        return null;
    }
}

if (! function_exists('ip_in_cidr')) {
    /**
     * Cek IP dalam range CIDR (PRD 8.9) — IPv4 & IPv6.
     */
    function ip_in_cidr(string $ip, string $cidr): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException("IP tidak valid: {$ip}");
        }

        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, null);

        if ($bits === null || ! is_numeric($bits) || filter_var($subnet, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException("CIDR tidak valid: {$cidr}");
        }

        $bits = (int) $bits;
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($bits < 0 || $bits > $maxBits) {
            throw new InvalidArgumentException("CIDR tidak valid: {$cidr}");
        }

        $fullBytes = intdiv($bits, 8);
        $remaining = $bits % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }

        if ($remaining === 0) {
            return true;
        }

        $mask = 0xFF << (8 - $remaining) & 0xFF;

        return (ord($ipBin[$fullBytes]) & $mask) === (ord($subnetBin[$fullBytes]) & $mask);
    }
}

if (! function_exists('normalize_phone')) {
    /**
     * Seragamkan nomor telepon ke format 62xxx.
     */
    function normalize_phone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\.()+]/', '', $phone) ?? '';

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        return $phone;
    }
}

if (! function_exists('generate_referral_code')) {
    /**
     * Kode referral unik per user, format MER-REF-XXXX (Design Spec Bab 3).
     */
    function generate_referral_code(): string
    {
        do {
            $code = 'MER-REF-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}

if (! function_exists('generate_order_number')) {
    /**
     * Nomor order format MER-{4 huruf kapital}-{4 digit} (PRD 17.8 — contoh MER-XKDR-0004).
     * Bagian akhir = nomor urut harian; huruf acak tanpa I/O agar mudah dibaca.
     */
    function generate_order_number(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $sequence = Order::whereDate('created_at', today())->count() + 1;
            $code = '';

            for ($i = 0; $i < 4; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $no = 'MER-'.$code.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        } while (Order::where('no_order', $no)->exists());

        return $no;
    }
}
