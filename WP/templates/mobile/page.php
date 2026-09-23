<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="m-container content-area">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="single-page">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>