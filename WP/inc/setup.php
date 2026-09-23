<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   THEME SETUP
   ============================================================ */
add_action( 'after_setup_theme', 'mercatoria_setup' );
function mercatoria_setup() {

    load_theme_textdomain( 'mercatoria', MERCATORIA_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [ 'height' => 60, 'width' => 240, 'flex-height' => true, 'flex-width' => true ] );

    register_nav_menus( [
        'primary' => __( 'Menu Utama', 'mercatoria' ),
        'footer'  => __( 'Menu Footer', 'mercatoria' ),
    ] );

    add_image_size( 'produk-thumb', 400, 400, true );
    add_image_size( 'produk-large', 800, 800, true );
    add_image_size( 'kategori-tile', 600, 600, true );
}

/* ============================================================
   ENQUEUE — FRONTEND
   ============================================================ */
add_action( 'wp_enqueue_scripts', 'mercatoria_enqueue_assets' );
function mercatoria_enqueue_assets() {

    $device = MERCATORIA_DEVICE;

    wp_enqueue_style( 'mercatoria-base', MERCATORIA_URI . '/assets/css/base.css', [], MERCATORIA_VERSION );
    wp_enqueue_style( 'mercatoria-device', MERCATORIA_URI . "/assets/css/{$device}.css", [ 'mercatoria-base' ], MERCATORIA_VERSION );
    wp_enqueue_style( 'mercatoria-cart', MERCATORIA_URI . '/assets/css/cart.css', [ 'mercatoria-base' ], MERCATORIA_VERSION );
    wp_enqueue_style( 'mercatoria-checkout', MERCATORIA_URI . '/assets/css/checkout.css', [ 'mercatoria-base' ], MERCATORIA_VERSION );
    wp_enqueue_style( 'mercatoria-modal', MERCATORIA_URI . '/assets/css/modal.css', [], MERCATORIA_VERSION );

    wp_enqueue_script( 'mercatoria-device', MERCATORIA_URI . "/assets/js/{$device}.js", [], MERCATORIA_VERSION, true );

    wp_enqueue_script( 'mercatoria-modal', MERCATORIA_URI . '/assets/js/modal.js', [], MERCATORIA_VERSION, true );

    wp_enqueue_script( 'mercatoria-cart', MERCATORIA_URI . '/assets/js/cart.js', [ 'jquery', 'mercatoria-device', 'mercatoria-modal' ], MERCATORIA_VERSION, true );

    wp_enqueue_script( 'mercatoria-checkout', MERCATORIA_URI . '/assets/js/checkout.js', [ 'jquery', 'mercatoria-modal' ], MERCATORIA_VERSION, true );

    wp_enqueue_script( 'mercatoria-protect', MERCATORIA_URI . '/assets/js/protect-image.js', [], MERCATORIA_VERSION, true );

    wp_localize_script( 'mercatoria-device', 'MERCATORIA', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'homeUrl' => home_url( '/' ),
        'device'  => $device,
        'nonce'   => wp_create_nonce( 'mercatoria_nonce' ),
    ] );
}

/* ============================================================
   ENQUEUE — SINGLE PRODUK
   ============================================================ */
add_action( 'wp_enqueue_scripts', 'mercatoria_enqueue_single_produk', 20 );
function mercatoria_enqueue_single_produk() {
    if ( ! is_singular( 'produk' ) ) return;

    wp_enqueue_script(
        'mercatoria-single-produk',
        MERCATORIA_URI . '/assets/js/single-produk.js',
        [ 'mercatoria-device', 'jquery', 'mercatoria-modal' ],
        MERCATORIA_VERSION,
        true
    );

    wp_localize_script( 'mercatoria-single-produk', 'MERCATORIA_PRICING', merc_get_pricing() );
}

/* ============================================================
   ENQUEUE — ADMIN
   ============================================================ */
add_action( 'admin_enqueue_scripts', 'mercatoria_enqueue_admin_assets' );
function mercatoria_enqueue_admin_assets() {

    $screen = get_current_screen();
    if ( ! $screen ) return;

    $is_term_screen = in_array( $screen->base, [ 'edit-tags', 'term' ], true );
    $is_produk_edit = $screen->post_type === 'produk';
    $is_merc_page   = strpos( $screen->id, 'mercatoria' ) !== false;

    if ( ! $is_term_screen && ! $is_produk_edit && ! $is_merc_page ) return;

    wp_enqueue_media();

    wp_enqueue_style( 'mercatoria-admin', MERCATORIA_URI . '/assets/css/admin.css', [], MERCATORIA_VERSION );

    wp_enqueue_script( 'mercatoria-admin', MERCATORIA_URI . '/assets/js/admin.js', [ 'jquery' ], MERCATORIA_VERSION, true );

    wp_enqueue_script( 'mercatoria-admin-variasi', MERCATORIA_URI . '/assets/js/admin-variasi.js', [ 'jquery', 'mercatoria-admin' ], MERCATORIA_VERSION, true );

    if ( function_exists( 'merc_get_pricing' ) ) {
        wp_localize_script( 'mercatoria-admin', 'MERCATORIA_PRICING', merc_get_pricing() );
        wp_localize_script( 'mercatoria-admin-variasi', 'MERCATORIA_PRICING', merc_get_pricing() );
    }
}

/* ============================================================
   FONT PRECONNECT
   ============================================================ */
add_action( 'wp_head', 'mercatoria_font_preconnect', 1 );
function mercatoria_font_preconnect() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}

/* ============================================================
   CUSTOMIZER — HERO SLIDER
   ============================================================ */
add_action( 'customize_register', 'mercatoria_customize_register' );
function mercatoria_customize_register( $wp_customize ) {

    $wp_customize->add_section( 'mercatoria_hero', [
        'title'       => 'Hero Slider',
        'priority'    => 30,
        'description' => 'Upload 1-5 gambar. Slot yang kosong otomatis di-skip.',
    ] );

    $wp_customize->add_setting( 'mercatoria_hero_autoplay', [
        'default'           => 1,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'mercatoria_hero_autoplay', [
        'label'   => 'Autoplay',
        'section' => 'mercatoria_hero',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'mercatoria_hero_interval', [
        'default'           => 5000,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'mercatoria_hero_interval', [
        'label'       => 'Interval Autoplay (ms)',
        'section'     => 'mercatoria_hero',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 2000, 'max' => 15000, 'step' => 500 ],
    ] );

    for ( $i = 1; $i <= 5; $i++ ) {

        $wp_customize->add_setting( "mercatoria_hero_slide_{$i}_image", [
            'default'           => 0,
            'sanitize_callback' => 'absint',
        ] );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, "mercatoria_hero_slide_{$i}_image", [
            'label'     => "Slide {$i} — Gambar",
            'section'   => 'mercatoria_hero',
            'mime_type' => 'image',
        ] ) );

        $wp_customize->add_setting( "mercatoria_hero_slide_{$i}_link", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        $wp_customize->add_control( "mercatoria_hero_slide_{$i}_link", [
            'label'   => "Slide {$i} — Link (opsional)",
            'section' => 'mercatoria_hero',
            'type'    => 'url',
        ] );
    }
}