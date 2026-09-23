<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   URL HELPER
   ============================================================ */
function merc_url( $key ) {
    $slugs = [
        'cart'         => 'keranjang',
        'checkout'     => 'lanjut-ke-pembayaran',
        'order'        => 'order-received',
        'konfirmasi'   => 'konfirmasi-pembayaran',
        'akun'         => 'akun-saya',
    ];
    $slug = $slugs[ $key ] ?? $key;
    return home_url( '/' . $slug . '/' );
}

/* ============================================================
   FORMAT
   ============================================================ */
function merc_format_rupiah( $n ) {
    return 'Rp' . number_format( (float) $n, 0, ',', '.' );
}

function merc_get_social_links() {
    return [
        'instagram' => [ 'label' => 'Instagram', 'url' => get_option( 'merc_social_ig', 'https://instagram.com/' ) ],
        'facebook'  => [ 'label' => 'Facebook',  'url' => get_option( 'merc_social_fb', 'https://facebook.com/' ) ],
        'twitter'   => [ 'label' => 'Twitter',   'url' => get_option( 'merc_social_tw', 'https://twitter.com/' ) ],
        'whatsapp'  => [ 'label' => 'WhatsApp',  'url' => get_option( 'merc_social_wa', 'https://wa.me/6281234567890' ) ],
    ];
}

function merc_get_badge( $post_id ) {
    $terms = get_the_terms( $post_id, 'status_produk' );
    if ( ! $terms || is_wp_error( $terms ) ) return null;

    $priority = [ 'limited' => 1, 'presale' => 2 ];
    $valid    = [];

    foreach ( $terms as $t ) {
        if ( isset( $priority[ $t->slug ] ) ) {
            $valid[] = $t;
        }
    }

    if ( empty( $valid ) ) return null;

    usort( $valid, fn( $a, $b ) => $priority[ $a->slug ] <=> $priority[ $b->slug ] );
    $t = $valid[0];

    $classes = [
        'limited' => 'badge-limited',
        'presale' => 'badge-presale',
    ];

    return [
        'label' => strtoupper( $t->name ),
        'class' => $classes[ $t->slug ] ?? 'badge-presale',
    ];
}

function merc_get_price_html( $post_id ) {
    $tipe = get_post_meta( $post_id, '_tipe_produk', true ) ?: 'single';

    if ( $tipe === 'multi' ) {
        $min = (int) get_post_meta( $post_id, 'harga_jual_min', true );
        $max = (int) get_post_meta( $post_id, 'harga_jual_max', true );

        if ( ! $min ) return '<span class="price-empty">Hubungi Admin</span>';

        if ( $min === $max ) {
            return '<span class="price-now">' . esc_html( merc_format_rupiah( $min ) ) . '</span>';
        }
        return '<span class="price-now">' . esc_html( merc_format_rupiah( $min ) ) . ' – ' . esc_html( merc_format_rupiah( $max ) ) . '</span>';
    }

    $harga = get_post_meta( $post_id, 'harga_jual', true );
    $coret = get_post_meta( $post_id, 'harga_coret', true );

    if ( ! $harga ) return '<span class="price-empty">Hubungi Admin</span>';

    $out = '';
    if ( $coret && (int) $coret > (int) $harga ) {
        $out .= '<span class="price-old">' . esc_html( merc_format_rupiah( $coret ) ) . '</span>';
    }
    $out .= '<span class="price-now">' . esc_html( merc_format_rupiah( $harga ) ) . '</span>';
    return $out;
}

function merc_format_product_title( $post_id ) {
    $title = get_the_title( $post_id );
    if ( strpos( $title, '[' ) === 0 ) return $title;
    $terms = get_the_terms( $post_id, 'kategori_produk' );
    if ( ! $terms || is_wp_error( $terms ) ) return $title;
    usort( $terms, fn( $a, $b ) => $a->parent <=> $b->parent );
    return '[' . strtoupper( $terms[0]->name ) . '] ' . $title;
}

function merc_product_card( $post_id = null, $args = [] ) {
    $post_id = $post_id ?: get_the_ID();
    $args = wp_parse_args( $args, [ 'thumb_size' => 'produk-thumb', 'show_badge' => true ] );

    $badge       = $args['show_badge'] ? merc_get_badge( $post_id ) : null;
    $status_stok = get_post_meta( $post_id, 'status_stok', true ) ?: 'instock';
    $is_oos      = $status_stok === 'outofstock';
    $card_class  = 'product-card' . ( $is_oos ? ' is-oos' : '' );
    ?>
    <article class="<?php echo esc_attr( $card_class ); ?>">
        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="product-thumb">
            <?php if ( $badge && ! $is_oos ) : ?>
                <span class="product-badge <?php echo esc_attr( $badge['class'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
            <?php endif; ?>

            <?php if ( $is_oos ) : ?>
                <span class="product-oos-overlay">Stok Habis</span>
            <?php endif; ?>

            <?php
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, $args['thumb_size'], [ 'loading' => 'lazy' ] );
            } else {
                echo '<div class="thumb-placeholder"></div>';
            }
            ?>
        </a>

        <h3 class="product-title">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                <?php echo esc_html( merc_format_product_title( $post_id ) ); ?>
            </a>
        </h3>

        <div class="product-price"><?php echo wp_kses_post( merc_get_price_html( $post_id ) ); ?></div>
    </article>
    <?php
}

function merc_breadcrumb() {
    echo '<nav class="breadcrumb">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">Beranda</a>';
    if ( is_tax( 'kategori_produk' ) ) {
        echo ' / <a href="' . esc_url( get_post_type_archive_link( 'produk' ) ) . '">Produk</a>';
        $t = get_queried_object();
        if ( $t && $t->parent ) {
            $p = get_term( $t->parent );
            echo ' / <a href="' . esc_url( get_term_link( $p ) ) . '">' . esc_html( $p->name ) . '</a>';
        }
        echo ' / <span>' . esc_html( $t->name ) . '</span>';
    } elseif ( is_singular( 'produk' ) ) {
        echo ' / <a href="' . esc_url( get_post_type_archive_link( 'produk' ) ) . '">Produk</a>';
        $terms = get_the_terms( get_the_ID(), 'kategori_produk' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            echo ' / <a href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
        }
    } elseif ( is_post_type_archive( 'produk' ) ) {
        echo ' / <span>Produk</span>';
    } elseif ( is_page() ) {
        echo ' / <span>' . esc_html( get_the_title() ) . '</span>';
    }
    echo '</nav>';
}

function merc_get_popular_products( $limit = 4 ) {
    $q = new WP_Query( [
        'post_type' => 'produk', 'posts_per_page' => $limit,
        'meta_key' => '_order_count', 'orderby' => 'meta_value_num', 'order' => 'DESC',
        'no_found_rows' => true,
    ] );
    if ( ! $q->have_posts() ) {
        $q = new WP_Query( [ 'post_type' => 'produk', 'posts_per_page' => $limit, 'orderby' => 'date', 'no_found_rows' => true ] );
    }
    return $q->posts;
}

function merc_get_presale_products( $limit = 6 ) {
    $q = new WP_Query( [
        'post_type'      => 'produk',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'tax_query'      => [[
            'taxonomy' => 'status_produk',
            'field'    => 'slug',
            'terms'    => 'presale',
        ]],
    ] );
    return $q->posts;
}

function merc_get_bestsellers( $limit = 6 ) {
    $q = new WP_Query( [
        'post_type'      => 'produk',
        'posts_per_page' => $limit,
        'meta_key'       => '_order_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => [[
            'key'     => '_order_count',
            'value'   => 1,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ]],
    ] );

    if ( $q->have_posts() ) return $q->posts;

    $q = new WP_Query( [
        'post_type'      => 'produk',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ] );
    return $q->posts;
}

function mercatoria_default_menu() {
    echo '<ul class="menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Beranda</a></li>';

    $games = get_terms( [
        'taxonomy'   => 'kategori_produk',
        'hide_empty' => false,
        'parent'     => 0,
        'number'     => 8,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );

    if ( ! is_wp_error( $games ) && $games ) {
        echo '<li class="menu-item-has-children"><a href="' . esc_url( get_post_type_archive_link( 'produk' ) ) . '">Game</a><ul class="sub-menu">';
        foreach ( $games as $t ) {
            printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
        }
        echo '</ul></li>';
    }

    $devs = get_terms( [
        'taxonomy'   => 'developer_produk',
        'hide_empty' => false,
        'number'     => 8,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );

    if ( ! is_wp_error( $devs ) && $devs ) {
        echo '<li class="menu-item-has-children"><a href="#">Developer</a><ul class="sub-menu">';
        foreach ( $devs as $t ) {
            printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
        }
        echo '</ul></li>';
    }

    echo '<li><a href="' . esc_url( merc_url( 'konfirmasi' ) ) . '">Konfirmasi Pembayaran</a></li>';
    echo '</ul>';
}

/* ============================================================
   HERO SLIDER
   ============================================================ */
function mercatoria_get_hero_slides() {
    $slides = [];

    for ( $i = 1; $i <= 5; $i++ ) {
        $img_id = (int) get_theme_mod( "mercatoria_hero_slide_{$i}_image", 0 );
        if ( ! $img_id ) continue;

        $slides[] = [
            'image_id' => $img_id,
            'link'     => get_theme_mod( "mercatoria_hero_slide_{$i}_link", '' ),
        ];
    }

    if ( empty( $slides ) ) {
        $old_id = (int) get_theme_mod( 'mercatoria_hero_image', 0 );
        if ( $old_id ) {
            $slides[] = [
                'image_id' => $old_id,
                'link'     => get_theme_mod( 'mercatoria_hero_link', '' ),
            ];
        }
    }

    return $slides;
}