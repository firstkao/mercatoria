<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Product;

/**
 * Products and pages share the site root (mercatoria.id/{slug}), so their slugs must not collide.
 */
final class Slugs
{
    /**
     * Paths used by the application itself (bahasa Indonesia).
     */
    public const RESERVED = [
        // Admin & auth
        'office',
        'masuk',
        'keluar',
        'daftar',

        // Akun pembeli
        'akun',

        // Belanja
        'katalog',
        'produk',
        'keranjang',
        'checkout',
        'pesanan',

        // Pembayaran
        'konfirmasi-pembayaran',
        'pembayaran',

        // Lupa password
        'lupa-password',
        'reset-password',

        // Verifikasi email
        'verifikasi-email',

        // Notifikasi
        'notifikasi',

        // Referral
        'undang',

        // Promo
        'promo',

        // Kontak
        'kontak',

        // Search
        'cari',

        // Pre-order
        'pre-order-baru',

        // Reseller
        'reseller',

        // Kategori (untuk antisipasi halaman per-game/developer)
        'game',
        'developer',

        // Sistem & asset
        'api',
        'storage',
        'up',
        'build',
        'css',
        'js',
        'images',
        'favicon-ico',
        'robots-txt',
        'sitemap',
    ];

    /**
     * Get the reason a slug cannot be used, or null when it is free.
     */
    public static function conflict(string $slug, ?Product $ignoreProduct = null, ?Page $ignorePage = null): ?string
    {
        if (in_array($slug, self::RESERVED, true)) {
            return 'Alamat ini dipakai sistem. Pilih alamat lain.';
        }

        if (Product::query()->where('slug', $slug)->when($ignoreProduct, fn ($query) => $query->whereKeyNot($ignoreProduct->getKey()))->exists()) {
            return 'Alamat ini sudah dipakai produk lain.';
        }

        if (Page::query()->where('slug', $slug)->when($ignorePage, fn ($query) => $query->whereKeyNot($ignorePage->getKey()))->exists()) {
            return 'Alamat ini sudah dipakai halaman lain.';
        }

        return null;
    }
}