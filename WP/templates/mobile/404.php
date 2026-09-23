<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="m-container content-area not-found">
    <h1 class="error-code">404</h1>
    <p>Halaman gak ada.</p>
    <a class="btn btn-block mt-3" href="<?php echo esc_url( home_url( '/' ) ); ?>">Kembali</a>
</div>

<?php get_footer(); ?>