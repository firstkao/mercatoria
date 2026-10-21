<?php

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

if (! function_exists('setting')) {
    /**
     * Ambil value dari tabel settings (dengan cache).
     */
    function setting(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            try {
                $setting = Setting::where('key', $key)->first();

                return $setting?->value ?? $default;
            } catch (Throwable $e) {
                return $default;
            }
        });
    }
}

if (! function_exists('get_real_ip')) {
    function get_real_ip(): string
    {
        return request()->ip() ?? '0.0.0.0';
    }
}

if (! function_exists('ip_in_cidr')) {
    function ip_in_cidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);
        $mask = (int) $mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            $maskBin = str_repeat("\xff", $mask >> 3);
            if ($mask % 8) {
                $maskBin .= chr((0xFF << (8 - ($mask % 8))) & 0xFF);
            }
            $maskBin = str_pad($maskBin, 16, "\0");

            return ($ipBin & $maskBin) === ($subnetBin & $maskBin);
        }

        return false;
    }
}

if (! function_exists('generate_order_number')) {
    function generate_order_number(): string
    {
        do {
            $random = strtoupper(Str::random(4));
            $random = preg_replace('/[^A-Z]/', 'X', $random);
            $number = str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);
            $orderNo = "MER-{$random}-{$number}";
        } while (Order::where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}

if (! function_exists('format_rupiah')) {
    function format_rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('format_tanggal_id')) {
    function format_tanggal_id($date, bool $shortMonth = true, bool $twoDigitYear = false): string
    {
        if (! $date) {
            return '—';
        }
        $full = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $short = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $date = Carbon::parse($date);
        $month = ($shortMonth ? $short : $full)[$date->month - 1];

        return $date->format('d').' '.$month.' '.($twoDigitYear ? $date->format('y') : $date->format('Y'));
    }
}
