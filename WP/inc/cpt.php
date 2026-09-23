<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'mercatoria_register_cpt_produk', 5 );
function mercatoria_register_cpt_produk() {
    register_post_type( 'produk', [
        'labels' => [
            'name'          => 'Produk',
            'singular_name' => 'Produk',
            'menu_name'     => 'Produk',
            'add_new'       => 'Tambah Baru',
            'add_new_item'  => 'Tambah Produk Baru',
            'edit_item'     => 'Edit Produk',
            'all_items'     => 'Semua Produk',
            'search_items'  => 'Cari Produk',
            'not_found'     => 'Produk tidak ditemukan',
        ],
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'has_archive'        => 'produk',
        'rewrite'            => [ 'slug' => 'produk', 'with_front' => false ],
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-cart',
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'taxonomies'         => [ 'kategori_produk', 'status_produk', 'developer_produk' ],
    ] );
}