<?php

namespace App\Support;

use App\Models\SocialMedia;

/**
 * Helper untuk baris social_media yang dirender sebagai TOMBOL MARKETPLACE
 * (teks + URL) di footer publik: TOCO, Shopee.
 *
 * Baris-baris ini "nitip" di tabel social_media supaya admin cukup kelola
 * dari satu halaman (Pengaturan Umum), tapi diperlakukan berbeda saat
 * render: bukan ikon SVG, melainkan tombol teks.
 */
class PublicMarketplace
{
    /** Kunci icon_key yang menandai sebuah baris adalah tombol marketplace. */
    public const ICON_KEYS = ['toco', 'shopee'];

    /**
     * Semua baris marketplace yang aktif dan punya URL.
     *
     * @return \Illuminate\Support\Collection<int, SocialMedia>
     */
    public static function all(): \Illuminate\Support\Collection
    {
        return SocialMedia::query()
            ->whereIntegerInRaw('is_active', [1])
            ->whereIn('icon_key', self::ICON_KEYS)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn ($sm) => trim((string) $sm->url) !== '')
            ->values();
    }

    /**
     * Baris marketplace keyed by icon_key ternormalisasi (lowercase, trim).
     * Dipakai AppServiceProvider untuk lookup cepat:
     *   $buttons->get('toco')  -> SocialMedia baris Toco
     *   $buttons->get('shopee')-> SocialMedia baris Shopee
     *
     * @return \Illuminate\Support\Collection<string, SocialMedia>
     */
    public static function buttons(): \Illuminate\Support\Collection
    {
        return self::all()->mapWithKeys(function ($sm) {
            $key = strtolower(trim((string) $sm->icon_key));

            return $key === '' ? [] : [$key => $sm];
        });
    }

    /**
     * Normalisasi icon_key (trim + lower). Ditaruh di sini supaya konsumen
     * class tidak perlu import PublicSocialMedia hanya untuk 1 method util.
     */
    public static function normalizedIconKey(SocialMedia $sm): ?string
    {
        $key = strtolower(trim((string) $sm->icon_key));

        return $key === '' ? null : $key;
    }
}
