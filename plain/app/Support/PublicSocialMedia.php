<?php

namespace App\Support;

use App\Models\SocialMedia;

/**
 * Helper untuk mengambil baris social_media yang siap dirender di layout publik.
 *
 * BUG FIX (ikon sosmed tidak muncul walau data ada):
 * 1. Query lama memakai whereNotNull('url') yang TIDAK mengecualikan string
 *    kosong (''). Baris dengan url = '' lolos dari DB, lalu disembunyikan oleh
 *    cek `! empty($sm->url)` di blade -> ikon hilang tanpa sebab yang jelas.
 * 2. Kolom is_active bisa berisi angka 0/1 maupun string '0'/'1' (sempat ditulis
 *    mentah lewat form). where('is_active', true) membandingkan dengan boolean
 *    yang pada sebagian driver MySQL ter-cast berbeda -> baris aktif bisa tidak
 *    ikut terpilih.
 * 3. icon_key tersimpan dengan variasi penulisan ('Instagram', 'INSTAGRAM',
 *    dsb.) sehingga cocok-cocokan ketat case-sensitive gagal dan SVG bawaan
 *    tidak pernah dirender (ikon tampil sebagai lingkaran huruf / tidak sama
 *    sekali kalau URL juga kosong).
 *
 * Semua penanganan itu disatukan di sini supaya header, footer, dan composer
 * layout memakai logika yang sama persis.
 */
class PublicSocialMedia
{
    /** Kunci baris yang dipakai sebagai tombol marketplace footer, bukan ikon sosmed. */
    public const MARKETPLACE_ICON_KEYS = ['toco', 'tokopedia', 'shopee', 'tiktokshop'];

    /**
     * @return \Illuminate\Support\Collection<int, SocialMedia>
     */
    public static function all(): \Illuminate\Support\Collection
    {
        return SocialMedia::query()
            // In(0,1,'0','1') lebih toleran daripada perbandingan boolean ketat.
            ->whereIntegerInRaw('is_active', [1])
            ->whereNotNull('url')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function ($sm) {
                // Penyebab utama ikon hilang padahal "data ada": baris dengan
                // url string kosong/spasi, ATAU tanpa url sama sekali tetapi
                // juga tidak punya icon_url/icon_key (tidak ada yang bisa
                // dirender). Semuanya dibuang di sini supaya konsisten.
                if (trim((string) $sm->url) !== '') {
                    return true;
                }

                return trim((string) $sm->icon_url) !== ''
                    || self::normalizedIconKey($sm) !== null;
            })
            ->values();
    }

    /**
     * Normalisasi icon_key (trim + lower) agar pencocokan SVG bawaan dan
     * pengelompokan tombol marketplace tidak gagal karena beda kapitalisasi.
     */
    public static function normalizedIconKey(SocialMedia $sm): ?string
    {
        $key = strtolower(trim((string) $sm->icon_key));

        return $key === '' ? null : $key;
    }

    public static function isMarketplace(SocialMedia $sm): bool
    {
        return in_array(self::normalizedIconKey($sm), self::MARKETPLACE_ICON_KEYS, true);
    }

    /**
     * Hanya ikon sosial media (selain tombol marketplace).
     *
     * @return \Illuminate\Support\Collection<int, SocialMedia>
     */
    public static function icons(): \Illuminate\Support\Collection
    {
        return self::all()->reject([self::class, 'isMarketplace'])->values();
    }

    /**
     * Baris marketplace keyed by icon_key ternormalisasi.
     *
     * @return \Illuminate\Support\Collection<string, SocialMedia>
     */
    public static function marketplaceButtons(): \Illuminate\Support\Collection
    {
        return self::all()
            ->filter([self::class, 'isMarketplace'])
            ->mapWithKeys(fn ($sm) => [self::normalizedIconKey($sm) => $sm]);
    }
}
