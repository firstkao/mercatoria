<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="m-container content-area">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="single-post">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="entry-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>
            <div class="entry-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>