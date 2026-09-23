<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$order_number = sanitize_text_field( $_GET['order'] ?? '' );
$token        = sanitize_text_field( $_GET['token'] ?? '' );

$order = null;
if ( $order_number && $token ) {
    $order = merc_order_get_by_number_token( $order_number, $token );
}
?>

<div class="container order-received-wrapper">

<?php if ( ! $order ) : ?>

    <div class="order-received-error">
        <div class="order-received-error-icon">⚠️</div>
        <h1>Order Gak Ditemukan</h1>
        <p>Pastikan link yang lo buka bener, atau cek email konfirmasi dari kami.</p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-accent mt-3">Kembali ke Beranda</a>
    </div>

<?php else : ?>

    <?php
    $status       = $order['status'];
    $status_label = merc_order_status_label( $status );
    $status_color = merc_order_status_color( $status );
    $method       = merc_get_payment_method( $order['payment_method'] );
    ?>

    <section class="order-received-header">
        <div class="order-received-icon">✓</div>
        <h1 class="order-received-title">Order Berhasil Dibuat</h1>
        <p class="order-received-sub">Terima kasih, <?php echo esc_html( $order['customer_name'] ); ?>. Order lo udah masuk.</p>

        <div class="order-number-box">
            <div class="order-number-label">Nomor Order</div>
            <div class="order-number-value" data-copy-text="<?php echo esc_attr( $order['order_number'] ); ?>">
                <?php echo esc_html( $order['order_number'] ); ?>
                <button type="button" class="copy-btn" data-copy-btn>Copy</button>
            </div>
        </div>

        <div class="order-status-badge" style="background:<?php echo esc_attr( $status_color ); ?>22;color:<?php echo esc_attr( $status_color ); ?>;">
            <?php echo esc_html( $status_label ); ?>
        </div>
    </section>

    <div class="order-received-layout">

        <div class="order-received-main">

            <section class="order-instruction">
                <h2 class="order-section-title">Instruksi Pembayaran</h2>

                <?php if ( ! $method ) : ?>
                    <div class="notice notice-error">Metode pembayaran tidak dikenal. Hubungi admin.</div>
                <?php else : ?>

                    <?php if ( $method['type'] === 'bank' ) : ?>

                        <div class="payment-detail-box">
                            <div class="payment-detail-row">
                                <span class="payment-detail-label">Bank</span>
                                <span class="payment-detail-value"><?php echo esc_html( $method['label'] ); ?></span>
                            </div>
                            <div class="payment-detail-row">
                                <span class="payment-detail-label">Nomor Rekening</span>
                                <span class="payment-detail-value" data-copy-text="<?php echo esc_attr( $method['account_number'] ); ?>">
                                    <?php echo esc_html( $method['account_number'] ); ?>
                                    <button type="button" class="copy-btn" data-copy-btn>Copy</button>
                                </span>
                            </div>
                            <div class="payment-detail-row">
                                <span class="payment-detail-label">Atas Nama</span>
                                <span class="payment-detail-value"><?php echo esc_html( $method['account_name'] ); ?></span>
                            </div>
                            <div class="payment-detail-row payment-detail-total">
                                <span class="payment-detail-label">Jumlah Transfer</span>
                                <span class="payment-detail-value total" data-copy-text="<?php echo esc_attr( $order['total'] ); ?>">
                                    <?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?>
                                    <button type="button" class="copy-btn" data-copy-btn>Copy</button>
                                </span>
                            </div>
                        </div>

                    <?php elseif ( $method['type'] === 'qris' ) : ?>

                        <div class="qris-box">
                            <?php if ( ! empty( $method['qris_image_id'] ) ) : ?>
                                <?php echo wp_get_attachment_image( $method['qris_image_id'], 'medium', false, [ 'class' => 'qris-img' ] ); ?>
                            <?php else : ?>
                                <div class="qris-placeholder">QRIS belum diatur admin</div>
                            <?php endif; ?>

                            <div class="qris-total">
                                Total: <strong><?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?></strong>
                                <button type="button" class="copy-btn" data-copy-text="<?php echo esc_attr( $order['total'] ); ?>" data-copy-btn>Copy</button>
                            </div>
                        </div>

                    <?php endif; ?>

                    <?php if ( ! empty( $method['instructions'] ) ) : ?>
                        <div class="payment-instructions">
                            <?php echo wp_kses_post( wpautop( $method['instructions'] ) ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="payment-warning">
                        <strong>⚠️ Penting:</strong> Transfer sesuai jumlah <strong>tepat</strong> agar admin mudah verifikasi. Setelah transfer, klik tombol konfirmasi di bawah.
                    </div>

                <?php endif; ?>
            </section>

            <section class="order-confirm">
                <h2 class="order-section-title">Konfirmasi Pembayaran</h2>
                <p class="order-confirm-desc">
                    Setelah transfer, lo bisa konfirmasi lewat salah satu cara di bawah:
                </p>

                <div class="order-confirm-actions">
                    <a href="<?php echo esc_url( add_query_arg( [
                        'order' => $order['order_number'],
                        'token' => $order['order_token'],
                    ], merc_url( 'konfirmasi' ) ) ); ?>"
                       class="btn btn-accent btn-lg">
                        Isi Form Konfirmasi
                    </a>

                    <a href="https://wa.me/6281234567890?text=<?php echo rawurlencode( 'Halo, saya mau konfirmasi order ' . $order['order_number'] . ' dengan total ' . merc_format_rupiah( $order['total'] ) ); ?>"
                       target="_blank" rel="noopener"
                       class="btn btn-outline btn-lg">
                        Konfirmasi via WhatsApp
                    </a>
                </div>
            </section>

        </div>

        <aside class="order-received-summary">

            <h2 class="order-section-title">Detail Order</h2>

            <div class="order-summary-items">
                <?php foreach ( $order['items'] as $item ) : ?>
                    <div class="order-summary-item">
                        <div class="order-summary-item-img">
                            <?php if ( ! empty( $item['image_url'] ) ) : ?>
                                <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="">
                            <?php endif; ?>
                            <span class="order-summary-item-qty"><?php echo (int) $item['qty']; ?></span>
                        </div>
                        <div class="order-summary-item-body">
                            <div class="order-summary-item-title"><?php echo esc_html( $item['product_title'] ); ?></div>
                            <?php if ( ! empty( $item['variation_label'] ) ) : ?>
                                <div class="order-summary-item-variant"><?php echo esc_html( $item['variation_label'] ); ?></div>
                            <?php endif; ?>
                            <div class="order-summary-item-price"><?php echo esc_html( merc_format_rupiah( $item['subtotal'] ) ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary-totals">
                <div class="order-summary-row">
                    <span>Subtotal</span>
                    <span><?php echo esc_html( merc_format_rupiah( $order['subtotal'] ) ); ?></span>
                </div>
                <div class="order-summary-row">
                    <span>Ongkir <small><?php echo esc_html( $order['ongkir_label'] ); ?></small></span>
                    <span><?php echo esc_html( merc_format_rupiah( $order['ongkir'] ) ); ?></span>
                </div>
                <div class="order-summary-row order-summary-row-grand">
                    <span>Total</span>
                    <span><?php echo esc_html( merc_format_rupiah( $order['total'] ) ); ?></span>
                </div>
            </div>

            <div class="order-shipping-info">
                <h3>Dikirim ke:</h3>
                <p>
                    <strong><?php echo esc_html( $order['customer_name'] ); ?></strong><br>
                    <?php echo esc_html( $order['customer_phone'] ); ?><br>
                    <?php echo esc_html( $order['address_line1'] ); ?><br>
                    <?php if ( $order['address_line2'] ) echo esc_html( $order['address_line2'] ) . '<br>'; ?>
                    <?php echo esc_html( $order['city'] ); ?>, <?php echo esc_html( $order['province'] ); ?>
                    <?php if ( $order['postal_code'] ) echo ' ' . esc_html( $order['postal_code'] ); ?>
                </p>
            </div>

        </aside>

    </div>

<?php endif; ?>

</div>

<?php get_footer(); ?>