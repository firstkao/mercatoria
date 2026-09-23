<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="container content-area">
    <?php if ( have_posts() ) : ?>
        <header class="page-header">
            <h1 class="page-title">
                <?php
                if ( is_post_type_archive( 'produk' ) ) echo 'Semua Produk';
                elseif ( is_tax() ) single_term_title();
                elseif ( is_search() ) printf( 'Hasil: %s', get_search_query() );
                else echo 'Terbaru';
                ?>
            </h1>
        </header>

        <div class="product-grid">
            <?php while ( have_posts() ) : the_post(); merc_product_card(); endwhile; ?>
        </div>

        <?php the_posts_pagination( [ 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ] ); ?>

    <?php else : ?>
        <section class="no-results">
            <h1>Tidak ada hasil</h1>
            <p>Coba cari yang lain.</p>
            <?php get_search_form(); ?>
        </section>
    <?php endif; ?>
</div>

<?php get_footer(); ?>