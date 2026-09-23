<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="home-wrapper">

    <!-- ============ HERO SLIDER ============ -->
    <?php $hero_slides = mercatoria_get_hero_slides(); ?>
    <section class="hero-m">
        <?php if ( ! empty( $hero_slides ) ) : ?>

            <div class="hero-slider-m" data-hero-slider
                 data-autoplay="<?php echo get_theme_mod( 'mercatoria_hero_autoplay', 1 ) ? '1' : '0'; ?>"
                 data-interval="<?php echo (int) get_theme_mod( 'mercatoria_hero_interval', 5000 ); ?>">

                <div class="hero-slider-track">
                    <?php foreach ( $hero_slides as $idx => $s ) :
                        $is_active = $idx === 0 ? ' is-active' : '';
                        $img_html = wp_get_attachment_image( $s['image_id'], 'full', false, [
                            'class'   => 'hero-img-m',
                            'loading' => $idx === 0 ? 'eager' : 'lazy',
                        ] );
                        ?>
                        <div class="hero-slide<?php echo esc_attr( $is_active ); ?>" data-slide="<?php echo (int) $idx; ?>" aria-hidden="<?php echo $idx === 0 ? 'false' : 'true'; ?>">
                            <?php if ( $s['link'] ) : ?>
                                <a href="<?php echo esc_url( $s['link'] ); ?>" class="hero-link"><?php echo $img_html; ?></a>
                            <?php else : ?>
                                <div class="hero-link"><?php echo $img_html; ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( count( $hero_slides ) > 1 ) : ?>
                    <div class="hero-dots-m" role="tablist" aria-label="Slide navigation">
                        <?php foreach ( $hero_slides as $idx => $s ) : ?>
                            <button type="button"
                                    class="hero-dot-m<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                                    data-hero-dot="<?php echo (int) $idx; ?>"
                                    role="tab"
                                    aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                                    aria-label="Slide <?php echo (int) ( $idx + 1 ); ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        <?php else : ?>

            <div class="hero-placeholder-m">
                <div class="hero-eyebrow-m">Selamat Datang</div>
                <h1 class="hero-title-m"><?php bloginfo( 'name' ); ?></h1>
                <p class="hero-sub-m"><?php bloginfo( 'description' ); ?></p>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-accent btn-block mt-3">Belanja Sekarang</a>
            </div>

        <?php endif; ?>
    </section>

    <!-- ============ MERCH BARU ============ -->
    <section class="home-section-m">
        <div class="m-container">

            <div class="section-header-m">
                <span class="section-eyebrow">Terbaru</span>
                <h2 class="section-title-m">Merch Baru</h2>
            </div>

            <?php
            $newest = new WP_Query( [
                'post_type'      => 'produk',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ] );

            if ( $newest->have_posts() ) : ?>
                <div class="product-grid">
                    <?php while ( $newest->have_posts() ) : $newest->the_post();
                        merc_product_card();
                    endwhile; wp_reset_postdata(); ?>
                </div>

                <div class="section-cta-m">
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-outline btn-block">
                        Lihat Semua
                    </a>
                </div>
            <?php else : ?>
                <div class="empty-state">
                    <p>Belum ada produk.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- ============ JELAJAHI GAME ============ -->
    <?php
    $top_cats = get_terms( [
        'taxonomy'   => 'kategori_produk',
        'hide_empty' => true,
        'parent'     => 0,
        'number'     => 9,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );

    if ( ! is_wp_error( $top_cats ) && $top_cats ) : ?>
    <section class="home-section-m">
        <div class="m-container">

            <div class="section-header-m">
                <span class="section-eyebrow">Kategori</span>
                <h2 class="section-title-m">Jelajahi Game</h2>
            </div>

            <div class="category-grid-m">
                <?php foreach ( $top_cats as $cat ) :
                    $thumb_id = (int) get_term_meta( $cat->term_id, 'kategori_thumb_id', true );
                    ?>
                    <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="category-tile-m">
                        <?php if ( $thumb_id ) : ?>
                            <?php echo wp_get_attachment_image( $thumb_id, 'kategori-tile', false, [ 'loading' => 'lazy' ] ); ?>
                        <?php else : ?>
                            <div class="tile-fallback">
                                <span><?php echo esc_html( mb_substr( $cat->name, 0, 1 ) ); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="tile-overlay-m">
                            <div class="tile-name-m"><?php echo esc_html( $cat->name ); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ============ TERLARIS ============ -->
    <?php
    $bestsellers = merc_get_bestsellers( 4 );
    if ( $bestsellers ) : ?>
    <section class="home-section-m">
        <div class="m-container">

            <div class="section-header-m">
                <span class="section-eyebrow">Favorit</span>
                <h2 class="section-title-m">Paling Laris</h2>
            </div>

            <div class="product-grid">
                <?php foreach ( $bestsellers as $p ) : ?>
                    <?php merc_product_card( $p->ID ); ?>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>