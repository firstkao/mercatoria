<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function merc_get_pricing() {
    return wp_parse_args( get_option( 'merc_pricing', [] ), [
        'kurs_yuan'      => 2200,
        'markup_persen'  => 30,
        'biaya_per_gram' => 5,
        'pembulatan'     => 1000,
        'koin_enabled'   => 1,
        'koin_rate'      => 100,
    ] );
}

/* ============================================================
   KOIN
   ============================================================ */
function merc_koin_enabled() {
    $s = merc_get_pricing();
    return ! empty( $s['koin_enabled'] );
}

function merc_get_koin_rate() {
    $s = merc_get_pricing();
    return max( 1, (int) ( $s['koin_rate'] ?? 100 ) );
}

/**
 * Hitung koin yang didapat dari sebuah harga.
 * Contoh: harga Rp258.000, rate 100 → 2.580 koin.
 */
function merc_calc_koin( $harga ) {
    if ( ! merc_koin_enabled() ) return 0;
    $rate = merc_get_koin_rate();
    return (int) floor( (int) $harga / $rate );
}

/* ============================================================
   HARGA
   ============================================================ */
function merc_calc_price_from( $yuan, $berat ) {
    $yuan  = (float) $yuan;
    $berat = (float) $berat;
    if ( ! $yuan ) return 0;

    $s = merc_get_pricing();
    $total = ( $yuan * $s['kurs_yuan'] ) * ( 1 + $s['markup_persen'] / 100 ) + ( $berat * $s['biaya_per_gram'] );
    $step = max( 1, (int) $s['pembulatan'] );

    return (int) ( ceil( $total / $step ) * $step );
}

function merc_calc_price( $post_id ) {
    $yuan  = (float) get_post_meta( $post_id, 'harga_modal_yuan', true );
    $berat = (float) get_post_meta( $post_id, 'berat_gram', true );
    return merc_calc_price_from( $yuan, $berat );
}

function merc_calc_and_save( $post_id ) {

    $variasi = get_post_meta( $post_id, '_merc_variations', true );

    delete_post_meta( $post_id, 'harga_jual_min' );
    delete_post_meta( $post_id, 'harga_jual_max' );

    if ( is_array( $variasi ) && ! empty( $variasi ) ) {
        list( $min, $max ) = merc_calc_variasi_range( $variasi );
        if ( $min ) {
            update_post_meta( $post_id, 'harga_jual_min', $min );
            update_post_meta( $post_id, 'harga_jual_max', $max );
            update_post_meta( $post_id, 'harga_jual', $min );
        } else {
            delete_post_meta( $post_id, 'harga_jual' );
        }
    } else {
        $auto = merc_calc_price( $post_id );
        if ( $auto ) {
            update_post_meta( $post_id, 'harga_jual', $auto );
        } else {
            delete_post_meta( $post_id, 'harga_jual' );
        }
    }

    $coret_yuan = (float) get_post_meta( $post_id, 'harga_coret_yuan', true );
    if ( $coret_yuan > 0 ) {
        $berat = (float) get_post_meta( $post_id, 'berat_gram', true );
        $coret_rp = merc_calc_price_from( $coret_yuan, $berat );
        if ( $coret_rp ) {
            update_post_meta( $post_id, 'harga_coret', $coret_rp );
        } else {
            delete_post_meta( $post_id, 'harga_coret' );
        }
    } else {
        delete_post_meta( $post_id, 'harga_coret' );
    }

    update_post_meta( $post_id, '_harga_last_calc', current_time( 'mysql' ) );
}

add_action( 'save_post_produk', function ( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    merc_calc_and_save( $post_id );
}, 30 );

/* ============================================================
   ADMIN COLUMNS
   ============================================================ */
add_filter( 'manage_produk_posts_columns', function ( $cols ) {
    $new = [];
    foreach ( $cols as $k => $v ) {
        $new[ $k ] = $v;
        if ( $k === 'title' ) {
            $new['tipe']       = 'Tipe';
            $new['harga_jual'] = 'Harga Jual';
        }
    }
    return $new;
} );

add_action( 'manage_produk_posts_custom_column', function ( $col, $post_id ) {

    if ( $col === 'tipe' ) {
        $variasi = get_post_meta( $post_id, '_merc_variations', true );
        if ( is_array( $variasi ) && count( $variasi ) > 0 ) {
            echo '<span style="background:#eae7ff;color:#6c63ff;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:600;">Variasi (' . count( $variasi ) . ')</span>';
        } else {
            echo '<span style="color:#8b90a3;font-size:12px;">Single</span>';
        }
    }

    if ( $col === 'harga_jual' ) {
        $min  = (int) get_post_meta( $post_id, 'harga_jual_min', true );
        $max  = (int) get_post_meta( $post_id, 'harga_jual_max', true );
        $auto = (int) get_post_meta( $post_id, 'harga_jual', true );

        if ( $min && $max && $min !== $max ) {
            echo '<strong style="color:#6c63ff;font-size:12px;">' . esc_html( merc_format_rupiah( $min ) ) . ' – ' . esc_html( merc_format_rupiah( $max ) ) . '</strong>';
        } elseif ( $auto ) {
            echo '<strong style="color:#6c63ff;">' . esc_html( merc_format_rupiah( $auto ) ) . '</strong>';
        } else {
            echo '—';
        }
    }
}, 10, 2 );