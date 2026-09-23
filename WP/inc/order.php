<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   TABLE NAMES
   ============================================================ */
function merc_orders_table() {
    global $wpdb;
    return $wpdb->prefix . 'merc_orders';
}

function merc_order_items_table() {
    global $wpdb;
    return $wpdb->prefix . 'merc_order_items';
}

/* ============================================================
   CREATE TABLES
   ============================================================ */
function merc_orders_create_tables() {
    global $wpdb;

    $charset = $wpdb->get_charset_collate();
    $orders  = merc_orders_table();
    $items   = merc_order_items_table();

    $sql1 = "CREATE TABLE $orders (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_number VARCHAR(32) NOT NULL,
        order_token VARCHAR(64) NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        customer_name VARCHAR(120) NOT NULL,
        customer_email VARCHAR(160) NOT NULL,
        customer_phone VARCHAR(32) NOT NULL,
        address_line1 VARCHAR(255) NOT NULL,
        address_line2 VARCHAR(255) DEFAULT '',
        city VARCHAR(80) NOT NULL,
        province VARCHAR(80) NOT NULL,
        postal_code VARCHAR(16) DEFAULT '',
        notes TEXT,
        subtotal BIGINT UNSIGNED NOT NULL DEFAULT 0,
        ongkir BIGINT UNSIGNED NOT NULL DEFAULT 0,
        ongkir_label VARCHAR(80) DEFAULT '',
        total BIGINT UNSIGNED NOT NULL DEFAULT 0,
        payment_method VARCHAR(32) DEFAULT '',
        status VARCHAR(32) NOT NULL DEFAULT 'pending_payment',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY order_number (order_number),
        UNIQUE KEY order_token (order_token),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset;";

    $sql2 = "CREATE TABLE $items (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        variation_index INT DEFAULT NULL,
        product_title VARCHAR(255) NOT NULL,
        variation_label VARCHAR(255) DEFAULT '',
        price BIGINT UNSIGNED NOT NULL DEFAULT 0,
        qty INT UNSIGNED NOT NULL DEFAULT 1,
        subtotal BIGINT UNSIGNED NOT NULL DEFAULT 0,
        image_url VARCHAR(500) DEFAULT '',
        sku VARCHAR(80) DEFAULT '',
        PRIMARY KEY (id),
        KEY order_id (order_id),
        KEY product_id (product_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql1 );
    dbDelta( $sql2 );
}

add_action( 'after_switch_theme', 'merc_orders_create_tables' );
add_action( 'init', function () {
    if ( get_option( 'merc_orders_db_version' ) !== MERCATORIA_VERSION ) {
        merc_orders_create_tables();
        update_option( 'merc_orders_db_version', MERCATORIA_VERSION );
    }
}, 6 );

/* ============================================================
   GENERATE ORDER NUMBER & TOKEN
   ============================================================ */
function merc_order_generate_number() {
    $date = current_time( 'Ymd' );
    $rand = wp_rand( 1000, 9999 );

    return 'MRC-' . $date . '-' . $rand;
}

function merc_order_generate_token() {
    return bin2hex( random_bytes( 16 ) );
}

/* ============================================================
   CREATE ORDER
   ============================================================ */
function merc_order_create( $data ) {
    global $wpdb;

    // Validasi
    $required = [ 'customer_name', 'customer_email', 'customer_phone', 'address_line1', 'city', 'province' ];
    foreach ( $required as $key ) {
        if ( empty( $data[ $key ] ) ) {
            return new WP_Error( 'missing_field', 'Field wajib belum diisi: ' . $key );
        }
    }

    if ( ! is_email( $data['customer_email'] ) ) {
        return new WP_Error( 'invalid_email', 'Email tidak valid.' );
    }

    // Ambil cart
    $cart = merc_cart_get_totals();
    if ( empty( $cart['items'] ) ) {
        return new WP_Error( 'empty_cart', 'Keranjang kosong.' );
    }

    // Cek payment method
    $payment_method = sanitize_text_field( $data['payment_method'] ?? '' );
    $methods = merc_get_payment_methods();
    $method_valid = false;
    foreach ( $methods as $m ) {
        if ( $m['id'] === $payment_method && ! empty( $m['enabled'] ) ) {
            $method_valid = true;
            break;
        }
    }
    if ( ! $method_valid ) {
        return new WP_Error( 'invalid_payment', 'Metode pembayaran belum dipilih.' );
    }

    // Generate unique order number
    $attempts = 0;
    do {
        $order_number = merc_order_generate_number();
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . merc_orders_table() . " WHERE order_number = %s LIMIT 1",
            $order_number
        ) );
        $attempts++;
    } while ( $exists && $attempts < 10 );

    if ( $exists ) {
        return new WP_Error( 'order_number_fail', 'Gagal generate nomor order. Coba lagi.' );
    }

    $token = merc_order_generate_token();
    $now   = current_time( 'mysql' );

    // Insert order
    $order_row = [
        'order_number'   => $order_number,
        'order_token'    => $token,
        'user_id'        => is_user_logged_in() ? get_current_user_id() : null,
        'customer_name'  => sanitize_text_field( $data['customer_name'] ),
        'customer_email' => sanitize_email( $data['customer_email'] ),
        'customer_phone' => sanitize_text_field( $data['customer_phone'] ),
        'address_line1'  => sanitize_text_field( $data['address_line1'] ),
        'address_line2'  => sanitize_text_field( $data['address_line2'] ?? '' ),
        'city'           => sanitize_text_field( $data['city'] ),
        'province'       => sanitize_text_field( $data['province'] ),
        'postal_code'    => sanitize_text_field( $data['postal_code'] ?? '' ),
        'notes'          => sanitize_textarea_field( $data['notes'] ?? '' ),
        'subtotal'       => (int) $cart['subtotal'],
        'ongkir'         => (int) $cart['ongkir'],
        'ongkir_label'   => sanitize_text_field( $cart['ongkir_label'] ),
        'total'          => (int) $cart['total'],
        'payment_method' => $payment_method,
        'status'         => 'pending_payment',
        'created_at'     => $now,
        'updated_at'     => $now,
    ];

    $ok = $wpdb->insert( merc_orders_table(), $order_row );
    if ( ! $ok ) {
        return new WP_Error( 'db_error', 'Gagal menyimpan order.' );
    }

    $order_id = (int) $wpdb->insert_id;

    // Insert order items
    foreach ( $cart['items'] as $item ) {
        $wpdb->insert( merc_order_items_table(), [
            'order_id'        => $order_id,
            'product_id'      => (int) $item['product_id'],
            'variation_index' => $item['variation_index'],
            'product_title'   => $item['display_title'],
            'variation_label' => $item['variation_label'],
            'price'           => (int) $item['price'],
            'qty'             => (int) $item['qty'],
            'subtotal'        => (int) $item['subtotal'],
            'image_url'       => $item['image'],
            'sku'             => '',
        ] );
    }

    // Kosongin cart
    merc_cart_clear();

    // Hook: aksi setelah order dibuat
    do_action( 'merc_order_created', $order_id, $order_row );

    return [
        'order_id'     => $order_id,
        'order_number' => $order_number,
        'order_token'  => $token,
    ];
}

/* ============================================================
   GET ORDER
   ============================================================ */
function merc_order_get( $order_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM " . merc_orders_table() . " WHERE id = %d LIMIT 1",
        $order_id
    ), ARRAY_A );

    if ( ! $row ) return null;

    $row['items'] = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM " . merc_order_items_table() . " WHERE order_id = %d ORDER BY id ASC",
        $order_id
    ), ARRAY_A );

    return $row;
}

function merc_order_get_by_number_token( $order_number, $token ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM " . merc_orders_table() . " WHERE order_number = %s AND order_token = %s LIMIT 1",
        $order_number,
        $token
    ), ARRAY_A );

    if ( ! $row ) return null;

    $row['items'] = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM " . merc_order_items_table() . " WHERE order_id = %d ORDER BY id ASC",
        $row['id']
    ), ARRAY_A );

    return $row;
}

/* ============================================================
   UPDATE STATUS
   ============================================================ */
function merc_order_update_status( $order_id, $new_status ) {
    global $wpdb;

    $valid = [ 'pending_payment', 'waiting_verify', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'refunded' ];
    if ( ! in_array( $new_status, $valid, true ) ) {
        return new WP_Error( 'invalid_status', 'Status gak valid.' );
    }

    $wpdb->update(
        merc_orders_table(),
        [ 'status' => $new_status, 'updated_at' => current_time( 'mysql' ) ],
        [ 'id' => $order_id ]
    );

    do_action( 'merc_order_status_changed', $order_id, $new_status );

    return true;
}

/* ============================================================
   LABELS
   ============================================================ */
function merc_order_status_label( $status ) {
    $labels = [
        'pending_payment' => 'Menunggu Pembayaran',
        'waiting_verify'  => 'Menunggu Verifikasi',
        'paid'            => 'Sudah Dibayar',
        'processing'      => 'Diproses',
        'shipped'         => 'Dikirim',
        'completed'       => 'Selesai',
        'cancelled'       => 'Dibatalkan',
        'refunded'        => 'Direfund',
    ];
    return $labels[ $status ] ?? $status;
}

function merc_order_status_color( $status ) {
    $colors = [
        'pending_payment' => '#f59e0b',
        'waiting_verify'  => '#3b82f6',
        'paid'            => '#10b981',
        'processing'      => '#8b5cf6',
        'shipped'         => '#06b6d4',
        'completed'       => '#10b981',
        'cancelled'       => '#ef4444',
        'refunded'        => '#6b7280',
    ];
    return $colors[ $status ] ?? '#6b7280';
}

/* ============================================================
   PAYMENT METHODS
   ============================================================ */
function merc_get_payment_methods() {
    $methods = get_option( 'merc_payment_methods', null );

    if ( ! is_array( $methods ) || empty( $methods ) ) {
        $methods = [
            [
                'id'             => 'bca',
                'label'          => 'Transfer BCA',
                'type'           => 'bank',
                'enabled'        => 1,
                'account_number' => '1234567890',
                'account_name'   => 'PT Mercatoria',
                'instructions'   => 'Transfer sesuai total ke rekening BCA di atas. Simpan bukti transfer.',
            ],
            [
                'id'             => 'qris',
                'label'          => 'QRIS',
                'type'           => 'qris',
                'enabled'        => 1,
                'qris_image_id'  => 0,
                'instructions'   => 'Scan QRIS pakai aplikasi bank atau e-wallet apapun.',
            ],
        ];
    }

    return $methods;
}

function merc_get_payment_method( $id ) {
    foreach ( merc_get_payment_methods() as $m ) {
        if ( $m['id'] === $id ) return $m;
    }
    return null;
}