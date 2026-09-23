<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo-text"><?php bloginfo( 'name' ); ?></div>
                <p class="footer-desc"><?php bloginfo( 'description' ); ?></p>
                <div class="footer-social">
                    <?php foreach ( merc_get_social_links() as $key => $s ) : ?>
                        <a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
                            <?php echo merc_icon( $key, 18 ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="footer-col">
                <h4>Game</h4>
                <div class="footer-links">
                    <?php
                    $terms = get_terms( [ 'taxonomy' => 'kategori_produk', 'hide_empty' => true, 'parent' => 0, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC' ] );
                    if ( ! is_wp_error( $terms ) ) foreach ( $terms as $t ) {
                        printf( '<a href="%s">%s</a>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
                    }
                    ?>
                </div>
            </div>

            <div class="footer-col">
                <h4>Jelajahi</h4>
                <div class="footer-links">
                    <a href="<?php echo esc_url( home_url( '/cara-belanja/' ) ); ?>">Cara Belanja</a>
                    <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a>
                    <a href="<?php echo esc_url( home_url( '/kebijakan-privasi/' ) ); ?>">Kebijakan Privasi</a>
                    <a href="<?php echo esc_url( home_url( '/tentang-kami/' ) ); ?>">Tentang Kami</a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Produk Terpopuler</h4>
                <div class="footer-popular">
                    <?php foreach ( merc_get_popular_products( 4 ) as $p ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="footer-popular-item">
                            <?php echo get_the_post_thumbnail( $p, 'produk-thumb' ); ?>
                            <div>
                                <div class="pop-title"><?php echo esc_html( get_the_title( $p ) ); ?></div>
                                <div class="pop-price"><?php echo wp_kses_post( merc_get_price_html( $p->ID ) ); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>© <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></div>
            <div>Powered by <?php bloginfo( 'name' ); ?></div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>