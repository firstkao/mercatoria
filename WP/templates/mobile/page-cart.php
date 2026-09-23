<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<div class="m-container cart-page-wrapper">

    <h1 class="cart-page-title">Keranjang Belanja</h1>

    <div class="cart-page" data-cart-page>

        <div data-cart-page-items>
            <div class="cart-page-loading">Memuat keranjang…</div>
        </div>

        <div class="cart-page-totals" data-cart-page-totals hidden>
            <div class="cart-total-row">
                <span>Subtotal</span>
                <span data-cart-page-subtotal>Rp0</span>
            </div>
            <div class="cart-total-row">
                <span>Ongkir <small data-cart-page-ongkir-label></small></span>
                <span data-cart-page-ongkir>Rp0</span>
            </div>
            <div class="cart-total-row cart-total-grand">
                <span>Total</span>
                <span data-cart-page-total>Rp0</span>
            </div>

            <div class="cart-page-actions">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-outline">Lanjut Belanja</a>
                <a href="<?php echo esc_url( merc_url( 'checkout' ) ); ?>" class="btn btn-accent">Checkout</a>
            </div>
        </div>

    </div>

</div>

<?php get_footer(); ?>