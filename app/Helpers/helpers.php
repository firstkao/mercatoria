<?php

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
