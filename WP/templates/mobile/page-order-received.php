<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$order_number = sanitize_text_field( $_GET['order'] ?? '' );
$token        = sanitize_text_field( $_GET['token'] ?? '' );

$order = null;
if ( $order_number && $token ) {
    $order = merc_order_get_by_number_token( $order_number, $token );
}
?>

<div class="m-container order-received-wrapper-m">

<?php if ( ! $order ) : ?>

    <div class="order-received-error-m">
        <div class="order-received-error-icon">⚠️</div>
        <h1>Order Gak Ditemukan</h1>
        <p>Pastikan link yang lo buka bener.</p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-accent btn-block mt-3">Kembali</a>
    </div>

<?php else : ?>

    <?php
    $status       = $order['status'];
    $status_label = merc_order_status_label( $status );
    $status_color = merc_order_status_color( $status );
    $method       = merc_get_payment_method( $order['payment_method'] );
    ?>

    <section class="order-received-header-m">
        <div class="order-received-icon-m">✓</div>
        <h1 class="order-received-title-m">Order Berhasil</h1>
        <p class="order-received-sub-m">Terima kasih, <?php echo esc_html( $order['customer_name'] ); ?>.</p>

        <div class="order-number-box-m">
            <div class="order-number-label-m">Nomor Order</div>
            <div class="order-number-value-m" data-copy-text="<?php echo esc_attr( $order['order_number'] ); ?>">
                <?php echo esc_html( $order['order_number'] ); ?>
                <button type="button" class="copy-btn" data-copy-btn>Copy</button>
            </div>
        </div>

        <div class="order-status-badge-m" style="background:<?php echo esc_attr( $status_color ); ?>22;color:<?php echo esc_attr( $status_color ); ?>;">
            <?php echo esc_html( $status_label ); ?>
        </div>
    </section>

    <section class="order-instruction-m">
        <h2 class="order-section-title-m">Instruksi Pembayaran</h2>

        <?php if ( ! $method ) : ?>
            <div class="notice notice-error">Metode pembayaran tidak dikenal.</div>
        <?php else : ?>

            <?php if ( $method['type'] === 'bank' ) : ?>

                <div class="payment-detail-box-m">
                    <div class="payment-detail-row-m">
                        <span>Bank</span>
                        <span><?php echo esc_html( $method['label'] ); ?></span>
                    </div>
                    <div class="payment-detail-row-m">
                        <span>No. Rek</span>
                        <span data-copy-text="<?php echo esc_attr( $method['account_number'] ); ?>">
                            <?php echo esc_html( $method['account_number'] ); ?>
                            <button type="button" class="copy-btn" data-copy-btn>Copy</button>
                        </span>
                    </div>
                    <div class="payment-detail-row-m">
                        <span>Atas Nama</span>
                        <span><?php echo esc_html( $method['account_name'] ); ?></span>
                    </div>
                    <div class="payment-detail-row-m payment-detail-total-m">
                        <span>Jumlah</span>
                        <span class="total" data-copy-text="<?php echo esc_attr( $order['total'] ); ?>">
                            <?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?>
                            <button type="button" class="copy-btn" data-copy-btn>Copy</button>
                        </span>
                    </div>
                </div>

            <?php elseif ( $method['type'] === 'qris' ) : ?>

                <div class="qris-box-m">
                    <?php if ( ! empty( $method['qris_image_id'] ) ) : ?>
                        <?php echo wp_get_attachment_image( $method['qris_image_id'], 'medium', false, [ 'class' => 'qris-img-m' ] ); ?>
                    <?php else : ?>
                        <div class="qris-placeholder-m">QRIS belum diatur admin</div>
                    <?php endif; ?>

                    <div class="qris-total-m">
                        Total: <strong><?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?></strong>
                        <button type="button" class="copy-btn" data-copy-text="<?php echo esc_attr( $order['total'] ); ?>" data-copy-btn>Copy</button>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ( ! empty( $method['instructions'] ) ) : ?>
                <div class="payment-instructions-m">
                    <?php echo wp_kses_post( wpautop( $method['instructions'] ) ); ?>
                </div>
            <?php endif; ?>

            <div class="payment-warning-m">
                ⚠️ Transfer sesuai jumlah <strong>tepat</strong> agar admin mudah verifikasi.
            </div>

        <?php endif; ?>
    </section>

    <section class="order-confirm-m">
        <h2 class="order-section-title-m">Konfirmasi Pembayaran</h2>
        <p class="order-confirm-desc-m">Setelah transfer, konfirmasi lewat:</p>

        <a href="<?php echo esc_url( add_query_arg( [
            'order' => $order['order_number'],
            'token' => $order['order_token'],
        ], merc_url( 'konfirmasi' ) ) ); ?>"
           class="btn btn-accent btn-block">
            Isi Form Konfirmasi
        </a>

        <a href="https://wa.me/6281234567890?text=<?php echo rawurlencode( 'Halo, saya mau konfirmasi order ' . $order['order_number'] . ' total ' . merc_format_rupiah( $order['total'] ) ); ?>"
           target="_blank" rel="noopener"
           class="btn btn-outline btn-block mt-1">
            Konfirmasi via WhatsApp
        </a>
    </section>

    <section class="order-summary-m">
        <h2 class="order-section-title-m">Detail Order</h2>

        <?php foreach ( $order['items'] as $item ) : ?>
            <div class="order-summary-item-m">
                <div class="order-summary-item-img-m">
                    <?php if ( ! empty( $item['image_url'] ) ) : ?>
                        <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="">
                    <?php endif; ?>
                    <span class="order-summary-item-qty-m"><?php echo (int) $item['qty']; ?></span>
                </div>
                <div class="order-summary-item-body-m">
                    <div class="order-summary-item-title-m"><?php echo esc_html( $item['product_title'] ); ?></div>
                    <?php if ( ! empty( $item['variation_label'] ) ) : ?>
                        <div class="order-summary-item-variant-m"><?php echo esc_html( $item['variation_label'] ); ?></div>
                    <?php endif; ?>
                    <div class="order-summary-item-price-m"><?php echo esc_html( merc_format_rupiah( $item['subtotal'] ) ); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="order-summary-totals-m">
            <div class="order-summary-row-m">
                <span>Subtotal</span>
                <span><?php echo esc_html( merc_format_rupiah( $order['subtotal'] ) ); ?></span>
            </div>
            <div class="order-summary-row-m">
                <span>Ongkir</span>
                <span><?php echo esc_html( merc_format_rupiah( $order['ongkir'] ) ); ?></span>
            </div>
            <div class="order-summary-row-m order-summary-row-grand-m">
                <span>Total</span>
                <span><?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?></span>
            </div>
        </div>

        <div class="order-shipping-info-m">
            <h3>Dikirim ke:</h3>
            <p>
                <strong><?php echo esc_html( $order['customer_name'] ); ?></strong><br>
                <?php echo esc_html( $order['customer_phone'] ); ?><br>
                <?php echo esc_html( $order['address_line1'] ); ?><br>
                <?php if ( $order['address_line2'] ) echo esc_html( $order['address_line2'] ) . '<br>'; ?>
                <?php echo esc_html( $order['city'] ); ?>, <?php echo esc_html( $order['province'] ); ?>
            </p>
        </div>
    </section>

<?php endif; ?>

</div>

<?php get_footer(); ?>