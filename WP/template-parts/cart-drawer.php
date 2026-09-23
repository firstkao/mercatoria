<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="cart-drawer-backdrop" data-cart-backdrop></div>

<aside class="cart-drawer" data-cart-drawer aria-hidden="true" aria-label="Keranjang belanja">
    <div class="cart-drawer-header">
        <h2>Keranjang Belanja</h2>
        <button type="button" class="cart-drawer-close" data-cart-close aria-label="Tutup">
            <?php echo merc_icon( 'close', 20 ); ?>
        </button>
    </div>

    <div class="cart-drawer-body" data-cart-items>
        <div class="cart-empty">
            <p>Keranjang lo masih kosong.</p>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>" class="btn btn-accent mt-3">Mulai belanja</a>
        </div>
    </div>

    <div class="cart-drawer-footer" data-cart-footer hidden>
        <div class="subtotal-row">
            <span>Subtotal</span>
            <span class="amount" data-cart-subtotal-footer>Rp0</span>
        </div>
        <div class="drawer-actions">
            <a href="<?php echo esc_url( merc_url( 'cart' ) ); ?>" class="btn btn-outline btn-block">Lihat Keranjang</a>
            <a href="<?php echo esc_url( merc_url( 'checkout' ) ); ?>" class="btn btn-accent btn-block">Lanjut ke Pembayaran</a>
        </div>
    </div>
</aside>