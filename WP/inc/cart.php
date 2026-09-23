<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   TABLE SETUP
   ============================================================ */
function merc_cart_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'merc_carts';
}

function merc_cart_create_table() {
    global $wpdb;

    $table   = merc_cart_table_name();
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        cart_token VARCHAR(64) NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        items LONGTEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY cart_token (cart_token),
        KEY user_id (user_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

add_action( 'after_switch_theme', 'merc_cart_create_table' );
add_action( 'init', function () {
    if ( get_option( 'merc_cart_db_version' ) !== MERCATORIA_VERSION ) {
        merc_cart_create_table();
        update_option( 'merc_cart_db_version', MERCATORIA_VERSION );
    }
}, 5 );

/* ============================================================
   TOKEN
   ============================================================ */
function merc_cart_get_token() {
    if ( is_user_logged_in() ) {
        // Untuk user login: token pakai user_id, konsisten
        return 'user_' . get_current_user_id();
    }

    if ( ! empty( $_COOKIE['merc_cart_token'] ) ) {
        $token = sanitize_text_field( $_COOKIE['merc_cart_token'] );
        if ( preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
            return $token;
        }
    }

    // Bikin token baru
    $token = bin2hex( random_bytes( 16 ) );
    if ( ! headers_sent() ) {
        setcookie( 'merc_cart_token', $token, time() + MONTH_IN_SECONDS, '/', '', is_ssl(), true );
    }
    $_COOKIE['merc_cart_token'] = $token;

    return $token;
}

function merc_cart_get_current( $create = true ) {
    global $wpdb;

    $token = merc_cart_get_token();
    $table = merc_cart_table_name();

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE cart_token = %s LIMIT 1",
        $token
    ) );

    if ( ! $row && $create ) {
        $now = current_time( 'mysql' );
        $wpdb->insert( $table, [
            'cart_token' => $token,
            'user_id'    => is_user_logged_in() ? get_current_user_id() : null,
            'items'      => wp_json_encode( [] ),
            'created_at' => $now,
            'updated_at' => $now,
        ] );
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE cart_token = %s LIMIT 1",
            $token
        ) );
    }

    return $row;
}

/* ============================================================
   ITEMS
   ============================================================ */
function merc_cart_get_items() {
    $cart = merc_cart_get_current( false );
    if ( ! $cart ) return [];

    $items = json_decode( $cart->items, true );
    if ( ! is_array( $items ) ) return [];

    return $items;
}

function merc_cart_save_items( $items ) {
    global $wpdb;

    $cart = merc_cart_get_current( true );
    if ( ! $cart ) return false;

    $wpdb->update(
        merc_cart_table_name(),
        [
            'items'      => wp_json_encode( array_values( $items ) ),
            'updated_at' => current_time( 'mysql' ),
            'user_id'    => is_user_logged_in() ? get_current_user_id() : $cart->user_id,
        ],
        [ 'id' => $cart->id ]
    );

    return true;
}

/**
 * Bikin unique key untuk 1 cart line.
 * Product + variation = 1 line.
 */
function merc_cart_line_key( $product_id, $variation_index ) {
    return $product_id . ':' . ( $variation_index === null ? 'x' : (int) $variation_index );
}

function merc_cart_add( $product_id, $qty = 1, $variation_index = null ) {
    $product_id = (int) $product_id;
    $qty        = max( 1, (int) $qty );

    if ( ! $product_id ) return new WP_Error( 'invalid_product', 'Produk gak valid.' );

    $product = get_post( $product_id );
    if ( ! $product || $product->post_type !== 'produk' || $product->post_status !== 'publish' ) {
        return new WP_Error( 'product_not_found', 'Produk gak ditemukan.' );
    }

    // Cek OOS
    $status_stok = get_post_meta( $product_id, 'status_stok', true ) ?: 'instock';
    if ( $status_stok === 'outofstock' ) {
        return new WP_Error( 'oos', 'Stok produk ini habis.' );
    }

    // Cek varian
    $tipe = get_post_meta( $product_id, '_tipe_produk', true ) ?: 'single';

    if ( $tipe === 'multi' ) {
        $variations = get_post_meta( $product_id, '_merc_variations', true );
        if ( ! is_array( $variations ) || empty( $variations ) ) {
            return new WP_Error( 'no_variation', 'Produk ini butuh pilihan varian.' );
        }
        if ( $variation_index === null || ! isset( $variations[ $variation_index ] ) ) {
            return new WP_Error( 'invalid_variation', 'Varian yang dipilih gak valid.' );
        }

        $v = $variations[ $variation_index ];
        if ( ( $v['status'] ?? 'active' ) !== 'active' ) {
            return new WP_Error( 'var_inactive', 'Varian ini sedang tidak dijual.' );
        }
        if ( ! empty( $v['stok'] ) && (int) $v['stok'] === 0 ) {
            return new WP_Error( 'var_oos', 'Stok varian ini habis.' );
        }
        if ( ! empty( $v['stok'] ) && (int) $v['stok'] < $qty ) {
            return new WP_Error( 'var_stok_kurang', 'Stok varian gak cukup. Sisa: ' . (int) $v['stok'] );
        }
    } else {
        $variation_index = null;
    }

    $items = merc_cart_get_items();
    $key   = merc_cart_line_key( $product_id, $variation_index );

    // Cari line existing
    $found = false;
    foreach ( $items as &$item ) {
        if ( $item['key'] === $key ) {
            $item['qty'] = (int) $item['qty'] + $qty;
            $found = true;
            break;
        }
    }
    unset( $item );

    if ( ! $found ) {
        $items[] = [
            'key'             => $key,
            'product_id'      => $product_id,
            'variation_index' => $variation_index,
            'qty'             => $qty,
            'added_at'        => current_time( 'mysql' ),
        ];
    }

    merc_cart_save_items( $items );

    return true;
}

function merc_cart_update_qty( $line_key, $qty ) {
    $qty   = max( 1, (int) $qty );
    $items = merc_cart_get_items();

    foreach ( $items as &$item ) {
        if ( $item['key'] === $line_key ) {
            $item['qty'] = $qty;
            merc_cart_save_items( $items );
            return true;
        }
    }

    return new WP_Error( 'line_not_found', 'Line gak ditemukan.' );
}

function merc_cart_remove( $line_key ) {
    $items = merc_cart_get_items();
    $new   = array_values( array_filter( $items, fn( $i ) => $i['key'] !== $line_key ) );

    if ( count( $new ) === count( $items ) ) {
        return new WP_Error( 'line_not_found', 'Line gak ditemukan.' );
    }

    merc_cart_save_items( $new );
    return true;
}

function merc_cart_clear() {
    merc_cart_save_items( [] );
    return true;
}

/* ============================================================
   TOTALS
   ============================================================ */
/**
 * Ambil detail 1 cart line (live data dari product).
 */
function merc_cart_resolve_line( $item ) {

    $pid = (int) $item['product_id'];
    $product = get_post( $pid );

    if ( ! $product || $product->post_status !== 'publish' ) {
        return null;
    }

    $tipe         = get_post_meta( $pid, '_tipe_produk', true ) ?: 'single';
    $variations   = get_post_meta( $pid, '_merc_variations', true );
    $harga        = 0;
    $label_varian = '';
    $gambar_url   = has_post_thumbnail( $pid ) ? get_the_post_thumbnail_url( $pid, 'produk-thumb' ) : '';

    if ( $tipe === 'multi' && is_array( $variations ) && isset( $variations[ $item['variation_index'] ] ) ) {
        $v = $variations[ $item['variation_index'] ];
        $harga        = merc_calc_price_from( $v['harga_modal_yuan'] ?? 0, $v['berat_gram'] ?? 0 );
        $label_varian = $v['kombinasi_cache'] ?? '';
        if ( ! empty( $v['gambar_id'] ) ) {
            $gambar_url = wp_get_attachment_image_url( (int) $v['gambar_id'], 'produk-thumb' );
        }
    } else {
        $harga = (int) get_post_meta( $pid, 'harga_jual', true );
    }

    $status_stok = get_post_meta( $pid, 'status_stok', true ) ?: 'instock';

    return [
        'key'             => $item['key'],
        'product_id'      => $pid,
        'permalink'       => get_permalink( $pid ),
        'title'           => get_the_title( $pid ),
        'display_title'   => merc_format_product_title( $pid ),
        'variation_index' => $item['variation_index'],
        'variation_label' => $label_varian,
        'qty'             => (int) $item['qty'],
        'price'           => (int) $harga,
        'subtotal'        => (int) $harga * (int) $item['qty'],
        'image'           => $gambar_url,
        'is_oos'          => $status_stok === 'outofstock',
    ];
}

function merc_cart_get_full() {
    $items = merc_cart_get_items();
    $lines = [];

    foreach ( $items as $item ) {
        $line = merc_cart_resolve_line( $item );
        if ( $line ) $lines[] = $line;
    }

    $subtotal = 0;
    $count    = 0;
    foreach ( $lines as $l ) {
        $subtotal += $l['subtotal'];
        $count    += $l['qty'];
    }

    return [
        'items'    => $lines,
        'subtotal' => $subtotal,
        'count'    => $count,
    ];
}

/**
 * Ambil ongkir tertinggi dari semua line produk di cart.
 * Logic: pilih level dengan min_belanja tertinggi yang <= subtotal.
 */
function merc_cart_get_ongkir() {
    $cart   = merc_cart_get_full();
    $subtotal = $cart['subtotal'];

    if ( empty( $cart['items'] ) ) {
        return [ 'ongkir' => 0, 'label' => '' ];
    }

    $levels = merc_get_ongkir_levels();
    if ( empty( $levels ) ) {
        return [ 'ongkir' => 0, 'label' => '' ];
    }

    // Sort by min_belanja DESC
    usort( $levels, fn( $a, $b ) => $b['min_belanja'] <=> $a['min_belanja'] );

    foreach ( $levels as $lvl ) {
        if ( $subtotal >= (float) $lvl['min_belanja'] ) {
            return [
                'ongkir' => (int) $lvl['ongkir'],
                'label'  => $lvl['nama'],
            ];
        }
    }

    // Fallback: level pertama (paling murah min_belanja)
    $first = end( $levels );
    return [
        'ongkir' => (int) $first['ongkir'],
        'label'  => $first['nama'],
    ];
}

function merc_cart_get_totals() {
    $cart    = merc_cart_get_full();
    $ongkir  = merc_cart_get_ongkir();

    $total = $cart['subtotal'] + $ongkir['ongkir'];

    return [
        'items'    => $cart['items'],
        'count'    => $cart['count'],
        'subtotal' => $cart['subtotal'],
        'ongkir'   => $ongkir['ongkir'],
        'ongkir_label' => $ongkir['label'],
        'total'    => $total,
        'total_formatted' => merc_format_rupiah( $total ),
        'subtotal_formatted' => merc_format_rupiah( $cart['subtotal'] ),
    ];
}