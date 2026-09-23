<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();

    $post_id      = get_the_ID();
    $tipe         = get_post_meta( $post_id, '_tipe_produk', true ) ?: 'single';
    $variations   = get_post_meta( $post_id, '_merc_variations', true );
    if ( ! is_array( $variations ) ) $variations = [];
    $has_variasi  = $tipe === 'multi' && ! empty( $variations );

    $sku          = get_post_meta( $post_id, 'sku', true );
    $status_stok  = get_post_meta( $post_id, 'status_stok', true ) ?: 'instock';
    $is_oos       = $status_stok === 'outofstock';
    $harga_jual   = (int) get_post_meta( $post_id, 'harga_jual', true );
    $harga_coret  = (int) get_post_meta( $post_id, 'harga_coret', true );
    $min          = (int) get_post_meta( $post_id, 'harga_jual_min', true );
    $max          = (int) get_post_meta( $post_id, 'harga_jual_max', true );

    $badge        = merc_get_badge( $post_id );

    // Koin
    $koin_enabled = merc_koin_enabled();
    $koin_base    = 0;
    if ( $koin_enabled ) {
        $base_harga = ( $has_variasi && $min ) ? $min : $harga_jual;
        $koin_base  = merc_calc_koin( $base_harga );
    }

    // Diskon
    $diskon_pct = 0;
    if ( $harga_coret && $harga_coret > $harga_jual && $harga_jual ) {
        $diskon_pct = (int) round( ( 1 - $harga_jual / $harga_coret ) * 100 );
    }

    $cats      = get_the_terms( $post_id, 'kategori_produk' );
    $cat_links = [];
    if ( $cats && ! is_wp_error( $cats ) ) {
        foreach ( $cats as $t ) {
            $cat_links[] = '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
        }
    }
    $devs      = get_the_terms( $post_id, 'developer_produk' );
    $dev_links = [];
    if ( $devs && ! is_wp_error( $devs ) ) {
        foreach ( $devs as $t ) {
            $dev_links[] = '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
        }
    }
    $tags      = get_the_terms( $post_id, 'status_produk' );
    $tag_links = [];
    if ( $tags && ! is_wp_error( $tags ) ) {
        foreach ( $tags as $t ) {
            $tag_links[] = '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
        }
    }

    $attributes = get_post_meta( $post_id, '_merc_attributes', true );
    if ( ! is_array( $attributes ) ) $attributes = [];
    ?>

    <div class="m-container single-produk-m">

        <?php merc_breadcrumb(); ?>

        <div class="single-gallery-m">
            <?php if ( $badge ) : ?>
                <span class="gallery-status-badge <?php echo esc_attr( $badge['class'] ); ?>">
                    <?php echo esc_html( $badge['label'] ); ?>
                </span>
            <?php endif; ?>

            <?php if ( $is_oos ) : ?>
                <span class="gallery-oos-label">Stok Habis</span>
            <?php endif; ?>

            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'produk-large', [ 'class' => 'gallery-main-img-m' ] ); ?>
            <?php else : ?>
                <div class="gallery-placeholder">🖼</div>
            <?php endif; ?>

            <button type="button" class="gallery-zoom-m" data-gallery-zoom aria-label="Perbesar">
                <?php echo merc_icon( 'search', 18 ); ?>
            </button>
        </div>

        <div class="single-info-m">

            <h1 class="single-title-m"><?php echo esc_html( merc_format_product_title( $post_id ) ); ?></h1>

            <div class="single-price-block-m" data-single-price-block>
                <?php if ( $harga_coret && $harga_coret > $harga_jual ) : ?>
                    <div class="single-price-row-top">
                        <span class="single-price-coret" data-single-coret><?php echo esc_html( merc_format_rupiah( $harga_coret ) ); ?></span>
                        <?php if ( $diskon_pct > 0 ) : ?>
                            <span class="single-discount-chip" data-single-discount>-<?php echo esc_html( $diskon_pct ); ?>%</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="single-price-m" data-single-price>
                    <?php
                    if ( $has_variasi && $min && $max && $min !== $max ) {
                        echo esc_html( merc_format_rupiah( $min ) ) . ' – ' . esc_html( merc_format_rupiah( $max ) );
                    } elseif ( $harga_jual ) {
                        echo esc_html( merc_format_rupiah( $harga_jual ) );
                    } else {
                        echo 'Hubungi Admin';
                    }
                    ?>
                </div>
            </div>

            <?php if ( $has_variasi && ! empty( $attributes ) ) : ?>
                <div class="single-variations-m">
                    <?php foreach ( $attributes as $attr ) : ?>
                        <div class="variation-group" data-attr-group="<?php echo esc_attr( $attr['nama'] ); ?>">
                            <div class="variation-options">
                                <?php foreach ( $attr['values'] as $val ) : ?>
                                    <button type="button"
                                            class="variation-pill"
                                            data-attr-name="<?php echo esc_attr( $attr['nama'] ); ?>"
                                            data-value="<?php echo esc_attr( $val ); ?>">
                                        <?php echo esc_html( $val ); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="variation-stock" data-var-stock></div>
                </div>
            <?php endif; ?>

            <?php if ( $koin_enabled && $koin_base > 0 ) : ?>
                <div class="single-koin-m" data-koin-info>
                    <span class="koin-icon">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><circle cx="12" cy="12" r="10" fill="#f59e0b"/><text x="12" y="16" font-size="11" font-weight="800" text-anchor="middle" fill="#fff">K</text></svg>
                    </span>
                    <span class="koin-text">
                        Dapatkan <strong data-koin-value><?php echo esc_html( number_format( $koin_base, 0, ',', '.' ) ); ?></strong> Koin dari produk ini.
                    </span>
                </div>
            <?php endif; ?>

            <div class="single-meta-m">
                <div class="single-meta-grid">
                    <?php if ( $sku ) : ?>
                        <div class="meta-cell">
                            <span class="meta-label">SKU</span>
                            <span class="meta-value"><?php echo esc_html( $sku ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $cat_links ) : ?>
                        <div class="meta-cell">
                            <span class="meta-label">Kategori</span>
                            <span class="meta-value"><?php echo implode( ', ', $cat_links ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $tag_links ) : ?>
                        <div class="meta-cell">
                            <span class="meta-label">Tag</span>
                            <span class="meta-value"><?php echo implode( ', ', $tag_links ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $dev_links ) : ?>
                        <div class="meta-cell">
                            <span class="meta-label">Brand</span>
                            <span class="meta-value"><?php echo implode( ', ', $dev_links ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="single-payment-box-m">
                <div class="payment-title">Jaminan Pembayaran Aman</div>
                <div class="payment-icons">
                    <span class="pay-icon">BCA</span>
                    <span class="pay-icon">QRIS</span>
                    <span class="pay-icon">Mandiri</span>
                    <span class="pay-icon">GoPay</span>
                </div>
            </div>

        </div>

        <div class="single-tabs" data-single-tabs>
            <ul class="single-tabs-nav">
                <li class="single-tab-item is-active" data-single-tab="deskripsi">Deskripsi</li>
                <li class="single-tab-item" data-single-tab="ulasan">Ulasan (0)</li>
            </ul>
            <div class="single-tabs-panels">
                <div class="single-tab-panel is-active" data-single-panel="deskripsi">
                    <?php if ( get_the_content() ) : ?>
                        <div class="single-desc-content"><?php the_content(); ?></div>
                    <?php else : ?>
                        <p class="single-empty">Belum ada deskripsi.</p>
                    <?php endif; ?>
                </div>
                <div class="single-tab-panel" data-single-panel="ulasan">
                    <p class="single-empty">Belum ada ulasan.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- STICKY BAR -->
    <div class="single-sticky-bar">
        <div class="qty-control qty-control-m<?php echo $is_oos ? ' is-disabled' : ''; ?>">
            <button type="button" data-qty-minus <?php disabled( $is_oos ); ?>>−</button>
            <input type="number" value="1" min="1" data-qty-input <?php disabled( $is_oos ); ?>>
            <button type="button" data-qty-plus <?php disabled( $is_oos ); ?>>+</button>
        </div>

        <?php if ( $is_oos ) : ?>
            <button type="button" class="btn single-add-btn-m is-oos" disabled>
                <span>Stok Habis</span>
            </button>
        <?php else : ?>
            <button type="button"
                    class="btn single-add-btn-m"
                    data-add-to-cart
                    data-product-id="<?php echo (int) $post_id; ?>">
                <span>Tambah ke Keranjang</span>
            </button>
        <?php endif; ?>
    </div>

    <?php
    if ( $cats && ! is_wp_error( $cats ) ) :
        $cat_ids = wp_list_pluck( $cats, 'term_id' );
        $related = new WP_Query( [
            'post_type'      => 'produk',
            'posts_per_page' => 4,
            'post__not_in'   => [ $post_id ],
            'no_found_rows'  => true,
            'tax_query'      => [[
                'taxonomy' => 'kategori_produk',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
            ]],
        ] );
        if ( $related->have_posts() ) : ?>
            <section class="single-related-m">
                <div class="m-container">
                    <div class="section-header-m">
                        <span class="section-eyebrow">Kamu Mungkin Suka</span>
                        <h2 class="section-title-m">Produk Serupa</h2>
                    </div>
                    <div class="product-grid">
                        <?php while ( $related->have_posts() ) : $related->the_post();
                            merc_product_card();
                        endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
        <?php endif;
    endif; ?>

    <script>
    window.MERC_VARIATIONS = <?php echo wp_json_encode( array_values( array_map( function( $v ) {
        return [
            'kombinasi'        => $v['kombinasi'] ?? [],
            'label'            => $v['kombinasi_cache'] ?? '',
            'harga_modal_yuan' => (float) ( $v['harga_modal_yuan'] ?? 0 ),
            'berat_gram'       => (float) ( $v['berat_gram'] ?? 0 ),
            'sku'              => $v['sku'] ?? '',
            'gambar_id'        => (int) ( $v['gambar_id'] ?? 0 ),
            'gambar_url'       => ! empty( $v['gambar_id'] ) ? wp_get_attachment_image_url( $v['gambar_id'], 'produk-large' ) : '',
            'stok'             => $v['stok'] ?? '',
            'status'           => $v['status'] ?? 'active',
        ];
    }, $variations ) ) ); ?>;
    window.MERC_HAS_VARIASI = <?php echo $has_variasi ? 'true' : 'false'; ?>;
    window.MERC_KOIN_RATE = <?php echo (int) merc_get_koin_rate(); ?>;
    window.MERC_KOIN_ENABLED = <?php echo merc_koin_enabled() ? 'true' : 'false'; ?>;
    </script>

<?php endwhile; ?>

<?php get_footer(); ?>