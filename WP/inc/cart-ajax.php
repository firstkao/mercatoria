<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   NONCE CHECK
   ============================================================ */
function merc_cart_verify_nonce() {
    $nonce = $_REQUEST['nonce'] ?? '';
    if ( ! wp_verify_nonce( $nonce, 'mercatoria_nonce' ) ) {
        wp_send_json_error( [ 'message' => 'Nonce gak valid. Refresh halaman.' ], 403 );
    }
}

/* ============================================================
   ADD
   ============================================================ */
add_action( 'wp_ajax_merc_cart_add', 'merc_ajax_cart_add' );
add_action( 'wp_ajax_nopriv_merc_cart_add', 'merc_ajax_cart_add' );
function merc_ajax_cart_add() {
    merc_cart_verify_nonce();

    $product_id      = (int) ( $_POST['product_id'] ?? 0 );
    $qty             = (int) ( $_POST['qty'] ?? 1 );
    $variation_index = isset( $_POST['variation_index'] ) && $_POST['variation_index'] !== '' && $_POST['variation_index'] !== 'null'
        ? (int) $_POST['variation_index']
        : null;

    $result = merc_cart_add( $product_id, $qty, $variation_index );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( [
        'cart' => merc_cart_get_totals(),
        'message' => 'Produk ditambahkan ke keranjang.',
    ] );
}

/* ============================================================
   UPDATE QTY
   ============================================================ */
add_action( 'wp_ajax_merc_cart_update', 'merc_ajax_cart_update' );
add_action( 'wp_ajax_nopriv_merc_cart_update', 'merc_ajax_cart_update' );
function merc_ajax_cart_update() {
    merc_cart_verify_nonce();

    $key = sanitize_text_field( $_POST['key'] ?? '' );
    $qty = (int) ( $_POST['qty'] ?? 1 );

    if ( ! $key ) {
        wp_send_json_error( [ 'message' => 'Key gak valid.' ] );
    }

    $result = merc_cart_update_qty( $key, $qty );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( [ 'cart' => merc_cart_get_totals() ] );
}

/* ============================================================
   REMOVE
   ============================================================ */
add_action( 'wp_ajax_merc_cart_remove', 'merc_ajax_cart_remove' );
add_action( 'wp_ajax_nopriv_merc_cart_remove', 'merc_ajax_cart_remove' );
function merc_ajax_cart_remove() {
    merc_cart_verify_nonce();

    $key = sanitize_text_field( $_POST['key'] ?? '' );
    if ( ! $key ) {
        wp_send_json_error( [ 'message' => 'Key gak valid.' ] );
    }

    $result = merc_cart_remove( $key );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( [ 'cart' => merc_cart_get_totals() ] );
}

/* ============================================================
   CLEAR
   ============================================================ */
add_action( 'wp_ajax_merc_cart_clear', 'merc_ajax_cart_clear' );
add_action( 'wp_ajax_nopriv_merc_cart_clear', 'merc_ajax_cart_clear' );
function merc_ajax_cart_clear() {
    merc_cart_verify_nonce();
    merc_cart_clear();
    wp_send_json_success( [ 'cart' => merc_cart_get_totals() ] );
}

/* ============================================================
   GET
   ============================================================ */
add_action( 'wp_ajax_merc_cart_get', 'merc_ajax_cart_get' );
add_action( 'wp_ajax_nopriv_merc_cart_get', 'merc_ajax_cart_get' );
function merc_ajax_cart_get() {
    merc_cart_verify_nonce();
    wp_send_json_success( [ 'cart' => merc_cart_get_totals() ] );
}