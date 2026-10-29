<?php

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
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
     * Nomor order format MER-{5}-{4} (PRD 17.8 — contoh MER-J79NR-0004).
     * Bagian akhir = nomor urut harian; huruf acak tanpa I/O/0/1 agar mudah dibaca.
     */
    function generate_order_number(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $sequence = Order::whereDate('created_at', today())->count() + 1;
            $code = '';

            for ($i = 0; $i < 5; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $no = 'MER-'.$code.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        } while (Order::where('no_order', $no)->exists());

        return $no;
    }
}
