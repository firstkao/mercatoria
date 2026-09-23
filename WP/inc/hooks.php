<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   BERSIHIN HEAD
   ============================================================ */
add_action( 'init', function () {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
} );

add_filter( 'excerpt_length', fn() => 20 );
add_filter( 'excerpt_more', fn() => '…' );

/* ============================================================
   ARCHIVE QUERY — Sort + Filter Harga
   ============================================================ */
add_action( 'pre_get_posts', 'merc_archive_pre_get_posts' );
function merc_archive_pre_get_posts( $q ) {

    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( ! is_post_type_archive( 'produk' ) && ! is_tax( 'kategori_produk' ) && ! is_tax( 'developer_produk' ) ) return;

    // Posts per page
    $q->set( 'posts_per_page', 24 );

    // Sorting
    $sort = $_GET['sort'] ?? 'default';

    switch ( $sort ) {
        case 'harga_asc':
            $q->set( 'meta_key', 'harga_jual' );
            $q->set( 'orderby', 'meta_value_num' );
            $q->set( 'order', 'ASC' );
            break;
        case 'harga_desc':
            $q->set( 'meta_key', 'harga_jual' );
            $q->set( 'orderby', 'meta_value_num' );
            $q->set( 'order', 'DESC' );
            break;
        case 'nama_asc':
            $q->set( 'orderby', 'title' );
            $q->set( 'order', 'ASC' );
            break;
        case 'nama_desc':
            $q->set( 'orderby', 'title' );
            $q->set( 'order', 'DESC' );
            break;
        case 'terbaru':
        default:
            $q->set( 'orderby', 'date' );
            $q->set( 'order', 'DESC' );
            break;
    }

    // Filter harga
    $min = isset( $_GET['min'] ) ? (int) $_GET['min'] : 0;
    $max = isset( $_GET['max'] ) ? (int) $_GET['max'] : 0;

    if ( $min > 0 || $max > 0 ) {
        $meta_query = (array) $q->get( 'meta_query' );
        $range = [ 'key' => 'harga_jual', 'type' => 'NUMERIC' ];

        if ( $min > 0 && $max > 0 ) {
            $range['value']   = [ $min, $max ];
            $range['compare'] = 'BETWEEN';
        } elseif ( $min > 0 ) {
            $range['value']   = $min;
            $range['compare'] = '>=';
        } else {
            $range['value']   = $max;
            $range['compare'] = '<=';
        }

        $meta_query[] = $range;
        $q->set( 'meta_query', $meta_query );
    }
}

/* ============================================================
   HELPER: Ambil range harga produk
   ============================================================ */
function merc_get_price_range() {
    $cached = get_transient( 'merc_price_range' );
    if ( $cached !== false ) return $cached;

    global $wpdb;

    $row = $wpdb->get_row( "
        SELECT MIN(CAST(pm.meta_value AS UNSIGNED)) AS min_price,
               MAX(CAST(pm.meta_value AS UNSIGNED)) AS max_price
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'harga_jual'
          AND pm.meta_value > 0
          AND p.post_status = 'publish'
          AND p.post_type = 'produk'
    " );

    $range = [
        'min' => (int) ( $row->min_price ?? 0 ),
        'max' => (int) ( $row->max_price ?? 1000000 ),
    ];

    set_transient( 'merc_price_range', $range, HOUR_IN_SECONDS );
    return $range;
}

/* Invalidate cache saat produk disimpan/dihapus */
add_action( 'save_post_produk', function () { delete_transient( 'merc_price_range' ); } );
add_action( 'deleted_post',    function () { delete_transient( 'merc_price_range' ); } );