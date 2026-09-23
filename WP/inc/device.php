<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mercatoria_detect_device() {
    if ( isset( $_GET['device'] ) && in_array( $_GET['device'], [ 'mobile', 'desktop' ], true ) ) {
        setcookie( 'mercatoria_device', $_GET['device'], time() + DAY_IN_SECONDS, '/' );
        $_COOKIE['mercatoria_device'] = $_GET['device'];
    }
    if ( ! empty( $_COOKIE['mercatoria_device'] ) && in_array( $_COOKIE['mercatoria_device'], [ 'mobile', 'desktop' ], true ) ) {
        return $_COOKIE['mercatoria_device'];
    }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ( preg_match( '/(iPad|Tablet|PlayBook|Silk|Kindle|SM-T)/i', $ua ) ) return 'desktop';
    if ( preg_match( '/(Mobile|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone)/i', $ua ) ) return 'mobile';
    return 'desktop';
}

if ( ! defined( 'MERCATORIA_DEVICE' ) )     define( 'MERCATORIA_DEVICE', mercatoria_detect_device() );
if ( ! defined( 'MERCATORIA_IS_MOBILE' ) )  define( 'MERCATORIA_IS_MOBILE', MERCATORIA_DEVICE === 'mobile' );

add_filter( 'template_include', function ( $template ) {
    $file = basename( $template );
    $alt  = MERCATORIA_DIR . '/templates/' . MERCATORIA_DEVICE . '/' . $file;
    return file_exists( $alt ) ? $alt : $template;
}, 99 );

add_filter( 'body_class', function ( $c ) {
    $c[] = 'device-' . MERCATORIA_DEVICE;
    return $c;
} );