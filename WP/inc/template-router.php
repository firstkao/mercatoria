<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   TEMPLATE ROUTER — deteksi page berdasarkan slug, load template
   sesuai device (desktop/mobile)
   ============================================================ */
add_filter( 'template_include', 'merc_page_template_router', 100 );
function merc_page_template_router( $template ) {

    if ( ! is_page() ) return $template;

    $page = get_queried_object();
    if ( ! $page || empty( $page->post_name ) ) return $template;

    $slug = $page->post_name;

    // Mapping: slug → nama template (tanpa prefix page-)
    $map = [
        'keranjang'             => 'cart',
        'cart'                  => 'cart',
        'lanjut-ke-pembayaran'  => 'checkout',
        'checkout'              => 'checkout',
        'order-received'        => 'order-received',
        'konfirmasi-pembayaran' => 'konfirmasi',
        'akun-saya'             => 'akun',
        'akun'                  => 'akun',
    ];

    if ( ! isset( $map[ $slug ] ) ) return $template;

    $name   = $map[ $slug ];
    $device = defined( 'MERCATORIA_DEVICE' ) ? MERCATORIA_DEVICE : 'desktop';

    // Cek: templates/{device}/page-{name}.php
    $device_path = MERCATORIA_DIR . "/templates/{$device}/page-{$name}.php";
    if ( file_exists( $device_path ) ) {
        return $device_path;
    }

    // Fallback: templates/{kebalikannya}/page-{name}.php
    $alt_device = $device === 'mobile' ? 'desktop' : 'mobile';
    $alt_path = MERCATORIA_DIR . "/templates/{$alt_device}/page-{$name}.php";
    if ( file_exists( $alt_path ) ) {
        return $alt_path;
    }

    return $template;
}