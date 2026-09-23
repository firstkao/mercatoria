<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'mercatoria_register_taxonomies', 6 );
function mercatoria_register_taxonomies() {

    register_taxonomy( 'kategori_produk', [ 'produk' ], [
        'labels' => [ 'name' => 'Kategori Produk', 'singular_name' => 'Kategori', 'menu_name' => 'Kategori' ],
        'public'             => true,
        'hierarchical'       => true,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'rewrite'            => [ 'slug' => 'kategori', 'with_front' => false, 'hierarchical' => true ],
    ] );

    register_taxonomy( 'developer_produk', [ 'produk' ], [
        'labels' => [ 'name' => 'Developer', 'singular_name' => 'Developer', 'menu_name' => 'Developer' ],
        'public'             => true,
        'hierarchical'       => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'rewrite'            => [ 'slug' => 'developer', 'with_front' => false ],
    ] );

    register_taxonomy( 'status_produk', [ 'produk' ], [
        'labels' => [ 'name' => 'Status Produk', 'singular_name' => 'Status', 'menu_name' => 'Status' ],
        'public'             => false,
        'hierarchical'       => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'rewrite'            => false,
    ] );
}

add_action( 'after_switch_theme', 'mercatoria_seed_status_terms' );
function mercatoria_seed_status_terms() {
    $defaults = [
        'limited' => 'LIMITED',
        'presale' => 'PRESALE',
    ];
    foreach ( $defaults as $slug => $name ) {
        if ( ! term_exists( $slug, 'status_produk' ) ) {
            wp_insert_term( $name, 'status_produk', [ 'slug' => $slug ] );
        }
    }
}

// ---- Term image ----
add_action( 'kategori_produk_add_form_fields', function () { ?>
    <div class="form-field">
        <label>Gambar Kategori</label>
        <input type="hidden" name="kategori_thumb_id" id="kategori_thumb_id" value="">
        <button type="button" class="button" id="kategori_thumb_btn">Pilih Gambar</button>
        <div id="kategori_thumb_preview" style="margin-top:10px;"></div>
    </div>
<?php } );

add_action( 'kategori_produk_edit_form_fields', function ( $term ) {
    $thumb_id = get_term_meta( $term->term_id, 'kategori_thumb_id', true ); ?>
    <tr class="form-field">
        <th><label>Gambar Kategori</label></th>
        <td>
            <input type="hidden" name="kategori_thumb_id" id="kategori_thumb_id" value="<?php echo esc_attr( $thumb_id ); ?>">
            <button type="button" class="button" id="kategori_thumb_btn">Pilih Gambar</button>
            <button type="button" class="button" id="kategori_thumb_remove">Hapus</button>
            <div id="kategori_thumb_preview" style="margin-top:10px;">
                <?php if ( $thumb_id ) echo wp_get_attachment_image( $thumb_id, 'medium' ); ?>
            </div>
        </td>
    </tr>
<?php } );

add_action( 'created_kategori_produk', 'mercatoria_save_kategori_thumb' );
add_action( 'edited_kategori_produk',  'mercatoria_save_kategori_thumb' );
function mercatoria_save_kategori_thumb( $term_id ) {
    if ( isset( $_POST['kategori_thumb_id'] ) ) {
        update_term_meta( $term_id, 'kategori_thumb_id', absint( $_POST['kategori_thumb_id'] ) );
    }
}