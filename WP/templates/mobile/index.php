<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="m-container content-area">
    <?php if ( have_posts() ) : ?>
        <h1 class="m-page-title">
            <?php
            if ( is_post_type_archive( 'produk' ) ) echo 'Semua Produk';
            elseif ( is_tax() ) single_term_title();
            elseif ( is_search() ) printf( 'Hasil: %s', get_search_query() );
            else echo 'Terbaru';
            ?>
        </h1>

        <div class="product-grid">
            <?php while ( have_posts() ) : the_post(); merc_product_card(); endwhile; ?>
        </div>

        <?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] ); ?>

    <?php else : ?>
        <div class="no-results"><p>Tidak ada hasil.</p></div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>