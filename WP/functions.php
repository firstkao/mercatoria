<?php
/**
 * Mercatoria Theme Functions
 *
 * @package Mercatoria
 * @version 2.9.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MERCATORIA_VERSION', '2.9.4' );
define( 'MERCATORIA_DIR', get_template_directory() );
define( 'MERCATORIA_URI', get_template_directory_uri() );

require_once MERCATORIA_DIR . '/inc/device.php';
require_once MERCATORIA_DIR . '/inc/icons.php';

foreach ( [
    'inc/setup.php',
    'inc/hooks.php',
    'inc/cpt.php',
    'inc/taxonomies.php',
    'inc/meta-variasi.php',
    'inc/meta-fields.php',
    'inc/pricing.php',
    'inc/template-tags.php',
    'inc/template-router.php',
    'inc/cart.php',
    'inc/cart-ajax.php',
    'inc/order.php',
    'inc/checkout.php',
    'inc/admin/settings.php',
    'inc/admin/payment-settings.php',
    'inc/admin/cleanup.php',
] as $rel ) {
    $path = MERCATORIA_DIR . '/' . $rel;
    if ( file_exists( $path ) ) require_once $path;
}

add_action( 'init', 'mercatoria_maybe_flush_rewrite', 99 );
function mercatoria_maybe_flush_rewrite() {
    $flag = 'mercatoria_rewrite_v';
    if ( get_option( $flag ) !== MERCATORIA_VERSION ) {
        flush_rewrite_rules( false );
        update_option( $flag, MERCATORIA_VERSION );
    }
}