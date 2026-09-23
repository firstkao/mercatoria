<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$cart = merc_cart_get_totals();
if ( empty( $cart['items'] ) ) {
    wp_safe_redirect( merc_url( 'cart' ) );
    exit;
}

$methods = merc_get_payment_methods();
$methods = array_filter( $methods, fn( $m ) => ! empty( $m['enabled'] ) );
?>

<div class="m-container checkout-wrapper-m">

    <h1 class="checkout-title-m">Checkout</h1>

    <form id="merc-checkout-form" class="checkout-form-m" novalidate>

        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">

        <section class="checkout-section-m">
            <h2 class="checkout-section-title-m">Ringkasan Order</h2>

            <div class="summary-items-m">
                <?php foreach ( $cart['items'] as $item ) : ?>
                    <div class="summary-item-m">
                        <div class="summary-item-img-m">
                            <?php if ( $item['image'] ) : ?>
                                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="">
                            <?php endif; ?>
                            <span class="summary-item-qty-m"><?php echo (int) $item['qty']; ?></span>
                        </div>
                        <div class="summary-item-body-m">
                            <div class="summary-item-title-m"><?php echo esc_html( $item['display_title'] ); ?></div>
                            <?php if ( $item['variation_label'] ) : ?>
                                <div class="summary-item-variant-m"><?php echo esc_html( $item['variation_label'] ); ?></div>
                            <?php endif; ?>
                            <div class="summary-item-price-m"><?php echo esc_html( merc_format_rupiah( $item['subtotal'] ) ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-totals-m">
                    <div class="summary-row-m">
                        <span>Subtotal</span>
                        <span><?php echo esc_html( merc_format_rupiah( $cart['subtotal'] ) ); ?></span>
                    </div>
                    <div class="summary-row-m">
                        <span>Ongkir <small><?php echo esc_html( $cart['ongkir_label'] ); ?></small></span>
                        <span><?php echo esc_html( merc_format_rupiah( $cart['ongkir'] ) ); ?></span>
                    </div>
                    <div class="summary-row-m summary-row-grand-m">
                        <span>Total</span>
                        <span><?php echo esc_html( merc_format_rupiah( $cart['total'] ) ); ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="checkout-section-m">
            <h2 class="checkout-section-title-m">Detail Pengiriman</h2>

            <div class="checkout-field-m">
                <label for="customer_name">Nama Lengkap <span class="req">*</span></label>
                <input type="text" id="customer_name" name="customer_name" required autocomplete="name" placeholder="Nama lengkap">
            </div>

            <div class="checkout-field-m">
                <label for="customer_phone">No. HP / WhatsApp <span class="req">*</span></label>
                <input type="tel" id="customer_phone" name="customer_phone" required autocomplete="tel" placeholder="08123456789">
            </div>

            <div class="checkout-field-m">
                <label for="customer_email">Email <span class="req">*</span></label>
                <input type="email" id="customer_email" name="customer_email" required autocomplete="email" placeholder="nama@email.com">
                <div class="checkout-hint">Konfirmasi order dikirim ke email ini.</div>
            </div>

            <div class="checkout-field-m">
                <label for="address_line1">Alamat Lengkap <span class="req">*</span></label>
                <input type="text" id="address_line1" name="address_line1" required placeholder="Nama jalan, nomor rumah">
            </div>

            <div class="checkout-field-m">
                <label for="address_line2">Detail Tambahan</label>
                <input type="text" id="address_line2" name="address_line2" placeholder="Apartemen, patokan (opsional)">
            </div>

            <div class="checkout-grid-m">
                <div class="checkout-field-m">
                    <label for="city">Kota <span class="req">*</span></label>
                    <input type="text" id="city" name="city" required placeholder="Jakarta Selatan">
                </div>
                <div class="checkout-field-m">
                    <label for="province">Provinsi <span class="req">*</span></label>
                    <input type="text" id="province" name="province" required placeholder="DKI Jakarta">
                </div>
            </div>

            <div class="checkout-field-m">
                <label for="postal_code">Kode Pos</label>
                <input type="text" id="postal_code" name="postal_code" placeholder="12345">
            </div>

            <div class="checkout-field-m">
                <label for="notes">Catatan (Opsional)</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Catatan tambahan…"></textarea>
            </div>
        </section>

        <section class="checkout-section-m">
            <h2 class="checkout-section-title-m">Metode Pembayaran</h2>

            <?php if ( empty( $methods ) ) : ?>
                <div class="notice notice-error">Belum ada metode pembayaran aktif.</div>
            <?php else : ?>
                <div class="payment-methods-m">
                    <?php foreach ( $methods as $i => $m ) : ?>
                        <label class="payment-method-m">
                            <input type="radio"
                                   name="payment_method"
                                   value="<?php echo esc_attr( $m['id'] ); ?>"
                                   <?php echo $i === array_key_first( $methods ) ? 'checked' : ''; ?>>
                            <span class="payment-method-body-m">
                                <span class="payment-method-label-m"><?php echo esc_html( $m['label'] ); ?></span>
                                <?php if ( ! empty( $m['instructions'] ) ) : ?>
                                    <span class="payment-method-desc-m"><?php echo esc_html( $m['instructions'] ); ?></span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="checkout-notice-m" data-checkout-notice hidden></div>

        <button type="submit" class="btn btn-accent btn-block checkout-submit-btn-m">
            <span>Buat Order — <?php echo esc_html( merc_format_rupiah( $cart['total'] ) ); ?></span>
        </button>

        <a href="<?php echo esc_url( merc_url( 'cart' ) ); ?>" class="checkout-back-m">← Kembali ke keranjang</a>

    </form>

</div>

<?php get_footer(); ?>