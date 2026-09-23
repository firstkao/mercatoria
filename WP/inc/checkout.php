<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   AJAX — SUBMIT CHECKOUT
   ============================================================ */
add_action( 'wp_ajax_merc_checkout_submit', 'merc_ajax_checkout_submit' );
add_action( 'wp_ajax_nopriv_merc_checkout_submit', 'merc_ajax_checkout_submit' );
function merc_ajax_checkout_submit() {

    // Nonce check
    $nonce = $_POST['nonce'] ?? '';
    if ( ! wp_verify_nonce( $nonce, 'mercatoria_nonce' ) ) {
        wp_send_json_error( [ 'message' => 'Nonce gak valid. Refresh halaman.' ], 403 );
    }

    // Rate limit: max 5 order per IP per jam
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ( $ip ) {
        $key = 'merc_checkout_' . md5( $ip );
        $count = (int) get_transient( $key );
        if ( $count >= 5 ) {
            wp_send_json_error( [ 'message' => 'Terlalu banyak order. Coba lagi nanti.' ] );
        }
        set_transient( $key, $count + 1, HOUR_IN_SECONDS );
    }

    // Honeypot
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_error( [ 'message' => 'Bot detected.' ] );
    }

    // Data
    $data = [
        'customer_name'  => sanitize_text_field( $_POST['customer_name'] ?? '' ),
        'customer_email' => sanitize_email( $_POST['customer_email'] ?? '' ),
        'customer_phone' => sanitize_text_field( $_POST['customer_phone'] ?? '' ),
        'address_line1'  => sanitize_text_field( $_POST['address_line1'] ?? '' ),
        'address_line2'  => sanitize_text_field( $_POST['address_line2'] ?? '' ),
        'city'           => sanitize_text_field( $_POST['city'] ?? '' ),
        'province'       => sanitize_text_field( $_POST['province'] ?? '' ),
        'postal_code'    => sanitize_text_field( $_POST['postal_code'] ?? '' ),
        'notes'          => sanitize_textarea_field( $_POST['notes'] ?? '' ),
        'payment_method' => sanitize_text_field( $_POST['payment_method'] ?? '' ),
    ];

    // Validate required
    if ( empty( $data['customer_name'] ) ) {
        wp_send_json_error( [ 'message' => 'Nama lengkap wajib diisi.' ] );
    }
    if ( empty( $data['customer_email'] ) || ! is_email( $data['customer_email'] ) ) {
        wp_send_json_error( [ 'message' => 'Email tidak valid.' ] );
    }
    if ( empty( $data['customer_phone'] ) ) {
        wp_send_json_error( [ 'message' => 'No. HP wajib diisi.' ] );
    }
    if ( empty( $data['address_line1'] ) ) {
        wp_send_json_error( [ 'message' => 'Alamat wajib diisi.' ] );
    }
    if ( empty( $data['city'] ) ) {
        wp_send_json_error( [ 'message' => 'Kota wajib diisi.' ] );
    }
    if ( empty( $data['province'] ) ) {
        wp_send_json_error( [ 'message' => 'Provinsi wajib diisi.' ] );
    }
    if ( empty( $data['payment_method'] ) ) {
        wp_send_json_error( [ 'message' => 'Pilih metode pembayaran.' ] );
    }

    // Create order
    $result = merc_order_create( $data );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    // Kirim email ke admin (opsional)
    merc_send_order_admin_email( $result['order_id'] );

    wp_send_json_success( [
        'message'       => 'Order berhasil dibuat.',
        'order_id'      => $result['order_id'],
        'order_number'  => $result['order_number'],
        'order_token'   => $result['order_token'],
        'redirect'      => add_query_arg(
            [
                'order' => $result['order_number'],
                'token' => $result['order_token'],
            ],
            home_url( '/order-received/' )
        ),
    ] );
}

/* ============================================================
   EMAIL ADMIN (simple)
   ============================================================ */
function merc_send_order_admin_email( $order_id ) {
    $order = merc_order_get( $order_id );
    if ( ! $order ) return;

    $admin_email = get_option( 'admin_email' );
    $subject = '[Order Baru] ' . $order['order_number'] . ' — ' . merc_format_rupiah( $order['total'] );

    $lines = [
        'Order baru masuk:',
        '',
        'Nomor: ' . $order['order_number'],
        'Nama: ' . $order['customer_name'],
        'Email: ' . $order['customer_email'],
        'HP: ' . $order['customer_phone'],
        'Alamat: ' . $order['address_line1'] . ( $order['address_line2'] ? ', ' . $order['address_line2'] : '' ),
        'Kota: ' . $order['city'],
        'Provinsi: ' . $order['province'],
        '',
        'Total: ' . merc_format_rupiah( $order['total'] ),
        'Metode: ' . $order['payment_method'],
        '',
        'Detail admin: ' . admin_url( 'admin.php?page=mercatoria-orders&order=' . $order_id ),
    ];

    wp_mail( $admin_email, $subject, implode( "\n", $lines ) );
}

/* ============================================================
   CUSTOMER EMAIL (opsional)
   ============================================================ */
add_action( 'merc_order_created', 'merc_send_order_customer_email', 10, 2 );
function merc_send_order_customer_email( $order_id, $order_row ) {
    $order = merc_order_get( $order_id );
    if ( ! $order ) return;

    $subject = 'Order ' . $order['order_number'] . ' diterima';

    $lines = [
        'Halo ' . $order['customer_name'] . ',',
        '',
        'Terima kasih, order lo udah kami terima.',
        '',
        'Nomor Order: ' . $order['order_number'],
        'Total: ' . merc_format_rupiah( $order['total'] ),
        '',
        'Lihat detail & instruksi pembayaran di sini:',
        add_query_arg(
            [
                'order' => $order['order_number'],
                'token' => $order['order_token'],
            ],
            home_url( '/order-received/' )
        ),
        '',
        'Setelah transfer, jangan lupa konfirmasi lewat halaman di atas ya.',
    ];

    wp_mail( $order['customer_email'], $subject, implode( "\n", $lines ) );
}