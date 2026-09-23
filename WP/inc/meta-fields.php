<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once MERCATORIA_DIR . '/inc/meta-variasi.php';

add_action( 'add_meta_boxes', 'merc_add_produk_metabox' );
function merc_add_produk_metabox() {
    add_meta_box( 'merc_detail', 'Data Produk', 'merc_render_detail_box', 'produk', 'normal', 'high' );
    add_meta_box( 'merc_harga', '💰 Harga Jual', 'merc_render_harga_box', 'produk', 'side', 'high' );
}

function merc_render_detail_box( $post ) {

    wp_nonce_field( 'merc_save_produk', 'merc_produk_nonce' );

    $harga_coret_yuan = get_post_meta( $post->ID, 'harga_coret_yuan', true );
    $harga_coret_rp   = get_post_meta( $post->ID, 'harga_coret', true );
    $sale_start       = get_post_meta( $post->ID, 'sale_start', true );
    $sale_end         = get_post_meta( $post->ID, 'sale_end', true );
    $sku              = get_post_meta( $post->ID, 'sku', true );
    $status_stok      = get_post_meta( $post->ID, 'status_stok', true ) ?: 'instock';
    $yuan             = get_post_meta( $post->ID, 'harga_modal_yuan', true );
    $berat            = get_post_meta( $post->ID, 'berat_gram', true );
    $level_ongkir     = get_post_meta( $post->ID, 'level_ongkir', true );

    $attributes       = get_post_meta( $post->ID, '_merc_attributes', true );
    if ( ! is_array( $attributes ) ) $attributes = [];
    $variations       = get_post_meta( $post->ID, '_merc_variations', true );
    if ( ! is_array( $variations ) ) $variations = [];

    $settings         = merc_get_pricing();
    $levels           = merc_get_ongkir_levels();
    $auto_price       = (int) get_post_meta( $post->ID, 'harga_jual', true );
    ?>

    <div class="merc-box">
        <div class="merc-body">

            <ul class="merc-tab-nav">
                <li class="merc-tab-item is-active" data-merc-tab="harga">Harga & Berat</li>
                <li class="merc-tab-item" data-merc-tab="inventaris">Inventaris</li>
                <li class="merc-tab-item" data-merc-tab="atribut">
                    Atribut
                    <span class="merc-tab-badge" data-merc-attr-count><?php echo count( $attributes ); ?></span>
                </li>
                <li class="merc-tab-item" data-merc-tab="variasi">
                    Variasi
                    <span class="merc-tab-badge" data-merc-var-count><?php echo count( $variations ); ?></span>
                </li>
            </ul>

            <div class="merc-tab-panels">

                <!-- PANEL: HARGA & BERAT -->
                <div class="merc-panel is-active" data-merc-panel="harga">

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">Harga Modal & Berat</h3>
                        <div class="merc-grid">
                            <div class="merc-field">
                                <label for="harga_modal_yuan">Harga Modal <span class="req">*</span></label>
                                <div class="input-prefix">
                                    <span>¥</span>
                                    <input type="number" id="harga_modal_yuan" name="harga_modal_yuan"
                                           value="<?php echo esc_attr( $yuan ); ?>"
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="hint">Harga dari supplier dalam Yuan (CNY).</div>
                            </div>

                            <div class="merc-field">
                                <label for="berat_gram">Berat <span class="req">*</span></label>
                                <div class="input-suffix">
                                    <input type="number" id="berat_gram" name="berat_gram"
                                           value="<?php echo esc_attr( $berat ); ?>"
                                           step="1" min="0" placeholder="0">
                                    <span>gram</span>
                                </div>
                                <div class="hint">Buat hitung harga + estimasi ongkir.</div>
                            </div>
                        </div>
                    </div>

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">Harga Coret (Promo)</h3>
                        <div class="merc-field">
                            <label for="harga_coret_yuan">Harga Coret <span class="hint-inline">(dalam Yuan)</span></label>
                            <div class="input-prefix">
                                <span>¥</span>
                                <input type="number" id="harga_coret_yuan" name="harga_coret_yuan"
                                       value="<?php echo esc_attr( $harga_coret_yuan ); ?>"
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="hint">
                                Dihitung pakai rumus yang sama (Yuan × Kurs × Markup + Biaya Gram).
                                <?php if ( $harga_coret_rp ) : ?>
                                    <br>Nilai Rp saat ini: <strong><?php echo esc_html( merc_format_rupiah( $harga_coret_rp ) ); ?></strong>
                                <?php endif; ?>
                                <br>Kosongin kalau gak ada promo.
                            </div>
                        </div>
                    </div>

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">📅 Jadwal Sale</h3>
                        <div class="merc-grid">
                            <div class="merc-field">
                                <label for="sale_start">Mulai</label>
                                <input type="date" id="sale_start" name="sale_start" value="<?php echo esc_attr( $sale_start ); ?>">
                            </div>
                            <div class="merc-field">
                                <label for="sale_end">Berakhir</label>
                                <input type="date" id="sale_end" name="sale_end" value="<?php echo esc_attr( $sale_end ); ?>">
                                <div class="hint">Setelah tanggal ini, harga coret otomatis hilang.</div>
                            </div>
                        </div>
                    </div>

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">📦 Level Ongkir</h3>
                        <div class="merc-field">
                            <select name="level_ongkir">
                                <option value="">— Pilih Level —</option>
                                <?php foreach ( $levels as $i => $lvl ) : ?>
                                    <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $level_ongkir, (string) $i ); ?>>
                                        <?php echo esc_html( $lvl['nama'] ); ?>
                                        <?php if ( $lvl['keterangan'] ) echo ' — ' . esc_html( $lvl['keterangan'] ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">
                                Level ini nentuin ongkir di cart.
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mercatoria-ongkir' ) ); ?>" target="_blank">Atur level ongkir →</a>
                            </div>
                        </div>
                    </div>

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">👁 Preview Harga Jual</h3>

                        <div class="merc-price-preview" data-merc-preview data-saved="<?php echo (int) $auto_price; ?>">
                            <div class="preview-label">Harga Jual Otomatis</div>
                            <div class="preview-value" data-merc-preview-value>
                                <?php echo $auto_price ? esc_html( merc_format_rupiah( $auto_price ) ) : 'Rp0'; ?>
                            </div>

                            <div class="merc-breakdown">
                                <div class="row"><span>Modal × Kurs</span><span data-mb-base>—</span></div>
                                <div class="row"><span>Markup <?php echo (float) $settings['markup_persen']; ?>%</span><span data-mb-markup>—</span></div>
                                <div class="row"><span>Berat × Biaya/g</span><span data-mb-ship>—</span></div>
                                <div class="row total"><span>Total</span><span data-mb-total>—</span></div>
                            </div>

                            <div class="preview-note" data-merc-preview-note style="margin-top:12px;">
                                <?php echo $auto_price ? 'Tersimpan.' : 'Isi Harga Modal & Berat dulu.'; ?>
                            </div>
                        </div>

                        <div class="merc-setting-info">
                            <div class="row"><span>Kurs Yuan</span><span>Rp<?php echo number_format( $settings['kurs_yuan'], 0, ',', '.' ); ?> / ¥1</span></div>
                            <div class="row"><span>Markup</span><span><?php echo (float) $settings['markup_persen']; ?>%</span></div>
                            <div class="row"><span>Biaya per gram</span><span>Rp<?php echo number_format( $settings['biaya_per_gram'], 0, ',', '.' ); ?></span></div>
                            <div class="row"><span>Pembulatan</span><span>ke Rp<?php echo number_format( $settings['pembulatan'], 0, ',', '.' ); ?></span></div>
                        </div>
                    </div>

                </div>

                <!-- PANEL: INVENTARIS -->
                <div class="merc-panel" data-merc-panel="inventaris">

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">SKU</h3>
                        <div class="merc-field">
                            <input type="text" name="sku" value="<?php echo esc_attr( $sku ); ?>" placeholder="MRC-0001">
                            <div class="hint">Kode internal produk. Boleh dikosongin.</div>
                        </div>
                    </div>

                    <div class="merc-box-section">
                        <h3 class="merc-section-title">Status Stok</h3>
                        <div class="merc-field">
                            <select name="status_stok">
                                <option value="instock"    <?php selected( $status_stok, 'instock' ); ?>>Tersedia</option>
                                <option value="outofstock" <?php selected( $status_stok, 'outofstock' ); ?>>Stok Habis (OOS)</option>
                            </select>
                            <div class="hint">
                                Kalau <strong>Stok Habis</strong>, card jadi abu-abu di katalog & tombol beli otomatis nonaktif.
                                Varian tetap bisa dipilih untuk lihat detail.
                            </div>
                        </div>
                    </div>

                </div>

                <!-- PANEL: ATRIBUT -->
                <div class="merc-panel" data-merc-panel="atribut">
                    <?php merc_render_tab_atribut( $post ); ?>
                </div>

                <!-- PANEL: VARIASI -->
                <div class="merc-panel" data-merc-panel="variasi">
                    <?php merc_render_tab_variasi( $post ); ?>
                </div>

            </div>

        </div>
    </div>

    <?php
}

/* ============================================================
   META BOX — HARGA JUAL SIDEBAR
   ============================================================ */
function merc_render_harga_box( $post ) {
    $auto_price  = (int) get_post_meta( $post->ID, 'harga_jual', true );
    $min         = (int) get_post_meta( $post->ID, 'harga_jual_min', true );
    $max         = (int) get_post_meta( $post->ID, 'harga_jual_max', true );
    $last_calc   = get_post_meta( $post->ID, '_harga_last_calc', true );
    $variations  = get_post_meta( $post->ID, '_merc_variations', true );
    $has_variasi = is_array( $variations ) && count( $variations ) > 0;
    $status_stok = get_post_meta( $post->ID, 'status_stok', true ) ?: 'instock';

    if ( $has_variasi && $min && $max ) {
        $display = $min === $max
            ? merc_format_rupiah( $min )
            : merc_format_rupiah( $min ) . ' – ' . merc_format_rupiah( $max );
        $badge = 'VARIASI';
    } elseif ( $auto_price ) {
        $display = merc_format_rupiah( $auto_price );
        $badge   = '';
    } else {
        $display = '—';
        $badge   = '';
    }
    ?>

    <div style="padding:16px;">
        <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#6b7384;margin-bottom:8px;">Harga Jual Aktif</div>
        <div style="font-size:24px;font-weight:800;letter-spacing:-.03em;color:#6c63ff;line-height:1.1;margin-bottom:8px;">
            <?php echo esc_html( $display ); ?>
        </div>

        <?php if ( $badge ) : ?>
            <div style="display:inline-block;background:#fff8e6;color:#92400e;font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;">
                <?php echo esc_html( $badge ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $status_stok === 'outofstock' ) : ?>
            <div style="display:inline-block;background:#fef2f2;color:#991b1b;font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;margin-left:6px;">
                STOK HABIS
            </div>
        <?php endif; ?>

        <?php if ( $has_variasi ) : ?>
            <div style="font-size:11px;color:#6b7384;margin-top:12px;">
                Total <?php echo count( $variations ); ?> variasi
            </div>
        <?php endif; ?>

        <?php if ( $last_calc ) : ?>
            <div style="font-size:11px;color:#6b7384;margin-top:12px;padding-top:12px;border-top:1px solid #e9ebf0;">
                Recalc: <?php echo esc_html( mysql2date( 'd M Y, H:i', $last_calc ) ); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
}

/* ============================================================
   SAVE
   ============================================================ */
add_action( 'save_post_produk', 'merc_save_produk_meta', 10, 2 );
function merc_save_produk_meta( $post_id, $post ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( ! isset( $_POST['merc_produk_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['merc_produk_nonce'], 'merc_save_produk' ) ) return;

    foreach ( [ 'sku', 'status_stok', 'sale_start', 'sale_end' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
        }
    }

    if ( isset( $_POST['level_ongkir'] ) ) {
        update_post_meta( $post_id, 'level_ongkir', sanitize_text_field( $_POST['level_ongkir'] ) );
    }

    foreach ( [ 'harga_coret_yuan', 'harga_modal_yuan', 'berat_gram' ] as $key ) {
        if ( isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ) {
            update_post_meta( $post_id, $key, (float) $_POST[ $key ] );
        } else {
            delete_post_meta( $post_id, $key );
        }
    }

    delete_post_meta( $post_id, 'harga_jual_manual' );
    delete_post_meta( $post_id, 'harga_coret' );

    merc_save_atribut( $post_id );
    merc_save_variasi( $post_id );

    if ( function_exists( 'merc_calc_and_save' ) ) {
        merc_calc_and_save( $post_id );
    }
}