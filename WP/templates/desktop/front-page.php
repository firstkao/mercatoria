<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="home-wrapper">

    <!-- ============ HERO SLIDER ============ -->
    <?php $hero_slides = mercatoria_get_hero_slides(); ?>
    <section class="hero">
        <div class="container">
            <?php if ( ! empty( $hero_slides ) ) : ?>

                <div class="hero-slider" data-hero-slider
                     data-autoplay="<?php echo get_theme_mod( 'mercatoria_hero_autoplay', 1 ) ? '1' : '0'; ?>"
                     data-interval="<?php echo (int) get_theme_mod( 'mercatoria_hero_interval', 5000 ); ?>">

                    <div class="hero-slider-track">
                        <?php foreach ( $hero_slides as $idx => $s ) :
                            $is_active = $idx === 0 ? ' is-active' : '';
                            $img_html = wp_get_attachment_image( $s['image_id'], 'full', false, [
                                'class'   => 'hero-img',
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

                        <button type="button" class="hero-arrow hero-prev" data-hero-prev aria-label="Slide sebelumnya">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                        </button>
                        <button type="button" class="hero-arrow hero-next" data-hero-next aria-label="Slide berikutnya">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8.59 16.59 10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
                        </button>

                        <div class="hero-dots" role="tablist" aria-label="Slide navigation">
                            <?php foreach ( $hero_slides as $idx => $s ) : ?>
                                <button type="button"
                                        class="hero-dot<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                                        data-hero-dot="<?php echo (int) $idx; ?>"
                                        role="tab"
                                        aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                                        aria-label="Slide <?php echo (int) ( $idx + 1 ); ?>"></button>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>

                </div>

            <?php else : ?>

                <div class="hero-inner">
                    <div class="hero-placeholder">
                        <div>
                            <div class="hero-eyebrow">Selamat Datang</div>
                            <h1 class="hero-title"><?php bloginfo( 'name' ); ?></h1>
                            <p class="hero-sub"><?php bloginfo( 'description' ); ?></p>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-accent btn-lg mt-3">Belanja Sekarang</a>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </section>

    <!-- ============ MERCH BARU ============ -->
    <section class="home-section">
        <div class="container">

            <div class="section-header">
                <span class="section-eyebrow">Terbaru</span>
                <h2 class="section-title">Merch Baru</h2>
                <p class="section-subtitle">Koleksi terbaru dari game-game favorit lo.</p>
            </div>

            <?php
            $newest = new WP_Query( [
                'post_type'      => 'produk',
                'posts_per_page' => 12,
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

                <div class="section-cta">
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-outline">
                        Lihat Semua Produk
                    </a>
                </div>
            <?php else : ?>
                <div class="empty-state">
                    <p>Belum ada produk.</p>
                    <p class="empty-hint">Buka <strong>WP Admin → Produk → Tambah Baru</strong> buat mulai.</p>
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
        'number'     => 12,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );

    if ( ! is_wp_error( $top_cats ) && $top_cats ) : ?>
    <section class="home-section">
        <div class="container">

            <div class="section-header">
                <span class="section-eyebrow">Kategori</span>
                <h2 class="section-title">Jelajahi Game Favoritmu</h2>
                <p class="section-subtitle">Temukan merchandise eksklusif dari setiap game.</p>
            </div>

            <div class="category-grid">
                <?php foreach ( $top_cats as $cat ) :
                    $thumb_id = (int) get_term_meta( $cat->term_id, 'kategori_thumb_id', true );
                    ?>
                    <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="category-tile">
                        <?php if ( $thumb_id ) : ?>
                            <?php echo wp_get_attachment_image( $thumb_id, 'kategori-tile', false, [ 'loading' => 'lazy' ] ); ?>
                        <?php else : ?>
                            <div class="tile-fallback">
                                <span><?php echo esc_html( mb_substr( $cat->name, 0, 1 ) ); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="tile-overlay">
                            <div class="tile-name"><?php echo esc_html( $cat->name ); ?></div>
                            <div class="tile-count"><?php echo (int) $cat->count; ?> produk</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ============ TERLARIS ============ -->
    <?php
    $bestsellers = merc_get_bestsellers( 6 );
    if ( $bestsellers ) : ?>
    <section class="home-section">
        <div class="container">

            <div class="section-header">
                <span class="section-eyebrow">Favorit</span>
                <h2 class="section-title">Paling Laris</h2>
                <p class="section-subtitle">Yang paling banyak dipesan pembeli.</p>
            </div>

            <div class="product-grid">
                <?php foreach ( $bestsellers as $p ) : ?>
                    <?php merc_product_card( $p->ID ); ?>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
    <?php endif; ?>

</div><!-- .home-wrapper -->

<?php get_footer(); ?>