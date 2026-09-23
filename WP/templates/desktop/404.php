<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="container content-area not-found">
    <h1 class="error-code">404</h1>
    <p>Halaman yang lo cari gak ada.</p>
    <?php get_search_form(); ?>
    <p><a class="btn mt-3" href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>">Lihat semua produk</a></p>
</div>

<?php get_footer(); ?>