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

<div class="container checkout-wrapper">

    <h1 class="checkout-title">Checkout</h1>

    <form id="merc-checkout-form" class="checkout-layout" novalidate>

        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">

        <div class="checkout-main">

            <section class="checkout-section">
                <h2 class="checkout-section-title">Detail Pengiriman</h2>

                <div class="checkout-grid">
                    <div class="checkout-field">
                        <label for="customer_name">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required autocomplete="name" placeholder="Nama lengkap">
                    </div>

                    <div class="checkout-field">
                        <label for="customer_phone">No. HP / WhatsApp <span class="req">*</span></label>
                        <input type="tel" id="customer_phone" name="customer_phone" required autocomplete="tel" placeholder="08123456789">
                    </div>

                    <div class="checkout-field checkout-field-full">
                        <label for="customer_email">Email <span class="req">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required autocomplete="email" placeholder="nama@email.com">
                        <div class="checkout-hint">Konfirmasi order bakal dikirim ke email ini.</div>
                    </div>
                </div>
            </section>

            <section class="checkout-section">
                <h2 class="checkout-section-title">Alamat Pengiriman</h2>

                <div class="checkout-grid">
                    <div class="checkout-field checkout-field-full">
                        <label for="address_line1">Alamat Lengkap <span class="req">*</span></label>
                        <input type="text" id="address_line1" name="address_line1" required placeholder="Nama jalan, nomor rumah, RT/RW">
                    </div>

                    <div class="checkout-field checkout-field-full">
                        <label for="address_line2">Detail Tambahan</label>
                        <input type="text" id="address_line2" name="address_line2" placeholder="Apartemen, suite, unit, patokan (opsional)">
                    </div>

                    <div class="checkout-field">
                        <label for="city">Kota / Kabupaten <span class="req">*</span></label>
                        <input type="text" id="city" name="city" required placeholder="Jakarta Selatan">
                    </div>

                    <div class="checkout-field">
                        <label for="province">Provinsi <span class="req">*</span></label>
                        <input type="text" id="province" name="province" required placeholder="DKI Jakarta">
                    </div>

                    <div class="checkout-field">
                        <label for="postal_code">Kode Pos</label>
                        <input type="text" id="postal_code" name="postal_code" placeholder="12345">
                    </div>
                </div>
            </section>

            <section class="checkout-section">
                <h2 class="checkout-section-title">Catatan (Opsional)</h2>
                <div class="checkout-field">
                    <textarea id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk order ini…"></textarea>
                </div>
            </section>

            <section class="checkout-section">
                <h2 class="checkout-section-title">Metode Pembayaran</h2>

                <?php if ( empty( $methods ) ) : ?>
                    <div class="notice notice-error">Belum ada metode pembayaran aktif. Hubungi admin.</div>
                <?php else : ?>
                    <div class="payment-methods">
                        <?php foreach ( $methods as $i => $m ) : ?>
                            <label class="payment-method">
                                <input type="radio"
                                       name="payment_method"
                                       value="<?php echo esc_attr( $m['id'] ); ?>"
                                       <?php echo $i === array_key_first( $methods ) ? 'checked' : ''; ?>>
                                <span class="payment-method-body">
                                    <span class="payment-method-label"><?php echo esc_html( $m['label'] ); ?></span>
                                    <?php if ( ! empty( $m['instructions'] ) ) : ?>
                                        <span class="payment-method-desc"><?php echo esc_html( $m['instructions'] ); ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>

        <aside class="checkout-summary">

            <h2 class="checkout-section-title">Order Lo</h2>

            <div class="summary-items">
                <?php foreach ( $cart['items'] as $item ) : ?>
                    <div class="summary-item">
                        <div class="summary-item-img">
                            <?php if ( $item['image'] ) : ?>
                                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="">
                            <?php else : ?>
                                <div class="summary-item-img-placeholder"></div>
                            <?php endif; ?>
                            <span class="summary-item-qty"><?php echo (int) $item['qty']; ?></span>
                        </div>
                        <div class="summary-item-body">
                            <div class="summary-item-title"><?php echo esc_html( $item['display_title'] ); ?></div>
                            <?php if ( $item['variation_label'] ) : ?>
                                <div class="summary-item-variant"><?php echo esc_html( $item['variation_label'] ); ?></div>
                            <?php endif; ?>
                            <div class="summary-item-price"><?php echo esc_html( merc_format_rupiah( $item['subtotal'] ) ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-totals">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo esc_html( merc_format_rupiah( $cart['subtotal'] ) ); ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkir <small><?php echo esc_html( $cart['ongkir_label'] ); ?></small></span>
                    <span><?php echo esc_html( merc_format_rupiah( $cart['ongkir'] ) ); ?></span>
                </div>
                <div class="summary-row summary-row-grand">
                    <span>Total</span>
                    <span><?php echo esc_html( merc_format_rupiah( $cart['total'] ) ); ?></span>
                </div>
            </div>

            <button type="submit" class="btn btn-accent btn-lg btn-block checkout-submit-btn">
                <span>Buat Order</span>
            </button>

            <div class="checkout-notice" data-checkout-notice hidden></div>

            <a href="<?php echo esc_url( merc_url( 'cart' ) ); ?>" class="checkout-back">← Kembali ke keranjang</a>

        </aside>

    </form>

</div>

<?php get_footer(); ?>