<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</main>

<footer class="m-footer">
    <div class="m-footer-brand"><?php bloginfo( 'name' ); ?></div>
    <div class="m-footer-links">
        <a href="<?php echo esc_url( home_url( '/tentang-kami/' ) ); ?>">Tentang</a>
        <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a>
        <a href="<?php echo esc_url( home_url( '/kebijakan-privasi/' ) ); ?>">Privasi</a>
    </div>
    <small>© <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></small>
</footer>

<nav class="m-bottom-nav" aria-label="Navigasi utama">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <?php echo merc_icon( 'home', 22, 'm-nav-icon' ); ?>
        <span>Home</span>
    </a>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>">
        <?php echo merc_icon( 'package', 22, 'm-nav-icon' ); ?>
        <span>Produk</span>
    </a>
    <a href="<?php echo esc_url( merc_url( 'cart' ) ); ?>">
        <?php echo merc_icon( 'cart', 22, 'm-nav-icon' ); ?>
        <span>Keranjang</span>
    </a>
    <a href="<?php echo esc_url( merc_url( 'akun' ) ); ?>">
        <?php echo merc_icon( 'user', 22, 'm-nav-icon' ); ?>
        <span>Akun</span>
    </a>
</nav>

<?php wp_footer(); ?>
</body>
</html>