<?php

namespace App\Support;

use App\Models\SocialMedia;

/**
 * Helper untuk baris social_media yang dirender sebagai IKON SOSIAL MEDIA
 * di header & footer publik.
 *
 * PENTING: class ini HANYA menangani sosmed. Baris marketplace (Toco,
 * Shopee, Tokopedia, TikTok Shop) dikecualikan di sini dan ditangani
 * terpisah oleh App\Support\PublicMarketplace.
 *
 * BUG FIX:
 * - whereNotNull('url') DIBUANG. Baris dengan url=NULL tapi punya icon_key
 *   bawaan (mis. Threads yang belum diisi URL) harus tetap tampil sebagai
 *   ikon non-link.
 */
class PublicSocialMedia
{
    /**
     * Query dasar: semua baris AKTIF yang BUKAN marketplace dan punya
     * sesuatu untuk dirender (url, icon_url, atau icon_key yang dikenali).
     *
     * @return \Illuminate\Support\Collection<int, SocialMedia>
     */
    public static function all(): \Illuminate\Support\Collection
    {
        return SocialMedia::query()
            // In(0,1,'0','1') lebih toleran daripada perbandingan boolean ketat.
            ->whereIntegerInRaw('is_active', [1])
            // Kecualikan marketplace di level SQL: lebih efisien dan bikin
            // niatnya eksplisit (class ini memang cuma untuk sosmed).
            ->where(function ($q) {
                $q->whereNull('icon_key')
                    ->orWhereNotIn('icon_key', PublicMarketplace::ICON_KEYS);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function ($sm) {
                // Baris valid jika punya salah satu dari:
                //  - url non-kosong
                //  - icon_url non-kosong
                //  - icon_key yang dikenali
                if (trim((string) $sm->url) !== '') {
                    return true;
                }

                if (trim((string) $sm->icon_url) !== '') {
                    return true;
                }

                return self::normalizedIconKey($sm) !== null;
            })
            ->values();
    }

    /**
     * Normalisasi icon_key (trim + lower) agar pencocokan SVG bawaan
     * tidak gagal karena beda kapitalisasi.
     */
    public static function normalizedIconKey(SocialMedia $sm): ?string
    {
        $key = strtolower(trim((string) $sm->icon_key));

        return $key === '' ? null : $key;
    }
}
