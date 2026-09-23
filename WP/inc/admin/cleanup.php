<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1. MATIIN GUTENBERG buat CPT produk
   ============================================================ */
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
    if ( $post_type === 'produk' ) return false;
    return $use;
}, 10, 2 );

/* ============================================================
   2. MATIIN ELEMENTOR buat CPT produk
   ============================================================ */
add_filter( 'elementor/utils/get_public_post_types', function ( $types ) {
    if ( is_array( $types ) ) unset( $types['produk'] );
    return $types;
} );

add_action( 'init', function () {
    $cpt = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );
    if ( is_array( $cpt ) && in_array( 'produk', $cpt, true ) ) {
        update_option( 'elementor_cpt_support', array_diff( $cpt, [ 'produk' ] ) );
    }
}, 20 );

add_action( 'admin_head-post.php', 'merc_hide_elementor_ui' );
add_action( 'admin_head-post-new.php', 'merc_hide_elementor_ui' );
function merc_hide_elementor_ui() {
    global $post_type;
    if ( $post_type !== 'produk' ) return;
    ?>
    <style>
        #elementor-editor-button,
        #elementor-switch-mode,
        #elementor-switch-mode-button,
        .elementor-edit-area,
        .elementor-edit-area-active,
        .elementor-message,
        #elementor-notice-bar,
        [class*="elementor-"] .notice { display: none !important; }
    </style>
    <?php
}

/* ============================================================
   3. BERSIHIN META BOX
   ============================================================ */
add_action( 'add_meta_boxes', function () {
    remove_meta_box( 'commentsdiv',      'produk', 'normal' );
    remove_meta_box( 'commentstatusdiv', 'produk', 'normal' );
    remove_meta_box( 'trackbacksdiv',    'produk', 'normal' );
    remove_meta_box( 'slugdiv',          'produk', 'normal' );
    remove_meta_box( 'authordiv',        'produk', 'normal' );
    remove_meta_box( 'postcustom',       'produk', 'normal' );
    remove_meta_box( 'revisionsdiv',     'produk', 'normal' );
    remove_meta_box( 'postexcerpt',      'produk', 'normal' ); // Deskripsi Singkat — gak dipakai
}, 99 );

/* ============================================================
   4. URUTAN META BOX
   ============================================================ */
add_filter( 'get_user_option_meta-box-order_produk', 'merc_metabox_order' );
function merc_metabox_order() {
    return [
        'normal'   => 'merc_detail',
        'side'     => 'submitdiv,postimagediv,merc_harga,taxonomy-kategori_produk,taxonomy-status_produk,taxonomy-developer_produk',
        'advanced' => '',
    ];
}

/* ============================================================
   5. GANTI LABEL "Konten" jadi "Deskripsi Produk"
   ============================================================ */
add_action( 'admin_head-post.php', 'merc_rename_content_label' );
add_action( 'admin_head-post-new.php', 'merc_rename_content_label' );
function merc_rename_content_label() {
    global $post_type;
    if ( $post_type !== 'produk' ) return;
    ?>
    <style>
        #titlediv + #postdivrich #wp-content-editor-tools::before,
        #wp-content-editor-container::before { display: none; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        // Ganti label default editor
        var tools = document.getElementById('wp-content-editor-tools');
        // Gak perlu ubah label, cuma visual
    });
    </script>
    <?php
}