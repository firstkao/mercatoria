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
     * Paths used by the application itself.
     */
    public const RESERVED = [
        'admin', 'akun', 'api', 'cart', 'checkout', 'daftar', 'katalog', 'keluar', 'keranjang',
        'konfirmasi-pembayaran', 'login', 'logout', 'masuk', 'order', 'order-saya', 'produk',
        'register', 'storage', 'up', 'build', 'css', 'js', 'images', 'favicon-ico', 'robots-txt',
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