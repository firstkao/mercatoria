<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   TAB: ATRIBUT — RENDER
   ============================================================ */
function merc_render_tab_atribut( $post ) {

    $attributes = get_post_meta( $post->ID, '_merc_attributes', true );
    if ( ! is_array( $attributes ) ) $attributes = [];
    ?>

    <div class="merc-box-section">
        <h3 class="merc-section-title">
            🏷 Atribut Produk
            <span class="merc-tab-badge" data-merc-attr-count><?php echo count( $attributes ); ?></span>
        </h3>

        <p class="merc-variant-desc">
            Tambah atribut buat generate varian otomatis. Contoh: <strong>Warna</strong> dengan value <code>Merah|Biru</code>,
            atau <strong>Ukuran</strong> dengan <code>S|M|L</code>.
            Pisahkan value dengan <strong>tanda pipa ( | )</strong>.
        </p>

        <div class="merc-attrs" data-merc-attrs>
            <?php
            if ( ! empty( $attributes ) ) {
                foreach ( $attributes as $i => $attr ) {
                    merc_render_attr_row( $i, $attr );
                }
            }
            ?>
        </div>

        <button type="button" class="merc-variant-add" data-merc-attr-add>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z"/></svg>
            Tambah Atribut
        </button>
    </div>

    <div class="merc-box-section">
        <h3 class="merc-section-title">⚡ Generate Variasi</h3>
        <p class="merc-variant-desc">
            Setelah atribut di atas diisi, klik tombol di bawah untuk bikin semua kombinasi varian otomatis.
            <br><strong>Catatan:</strong> varian existing dengan kombinasi yang sama akan dipertahankan datanya.
        </p>
        <button type="button" class="merc-btn-primary" data-merc-generate-variasi>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M7.5 5.6 10 7 8.6 4.5 10 2 7.5 3.4 5 2l1.4 2.5L5 7zm12 9.8L17 14l1.4 2.5L17 19l2.5-1.4L22 19l-1.4-2.5L22 14zM22 2l-2.5 1.4L17 2l1.4 2.5L17 7l2.5-1.4L22 7l-1.4-2.5zm-7.63 5.29a.9959.9959 0 0 0-1.41 0L1.29 18.96c-.39.39-.39 1.02 0 1.41l2.34 2.34c.39.39 1.02.39 1.41 0L16.7 11.05c.39-.39.39-1.02 0-1.41l-2.33-2.35zm-1.03 5.49-2.12-2.12 2.44-2.44 2.12 2.12-2.44 2.44z"/></svg>
            Generate Variasi dari Atribut
        </button>
    </div>

    <?php
}

function merc_render_attr_row( $index, $data = [] ) {
    $nama   = $data['nama'] ?? '';
    $values = $data['values'] ?? [];
    $values_str = is_array( $values ) ? implode( ' | ', $values ) : (string) $values;
    ?>
    <div class="merc-attr-row" data-attr-row>
        <div class="merc-attr-fields">
            <input type="text"
                   name="_merc_attributes[<?php echo (int) $index; ?>][nama]"
                   value="<?php echo esc_attr( $nama ); ?>"
                   placeholder="Nama atribut (contoh: Warna)"
                   class="merc-attr-nama">
            <input type="text"
                   name="_merc_attributes[<?php echo (int) $index; ?>][values]"
                   value="<?php echo esc_attr( $values_str ); ?>"
                   placeholder="Value, pisah pakai | (contoh: Merah | Biru | Hijau)"
                   class="merc-attr-values">
        </div>
        <button type="button" class="merc-variant-remove" data-merc-attr-remove aria-label="Hapus atribut">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM19 4h-3.5l-1-1h-5l-1 1H5v2h14z"/></svg>
        </button>
    </div>
    <?php
}

/* ============================================================
   TAB: VARIASI — RENDER
   ============================================================ */
function merc_render_tab_variasi( $post ) {

    $variations = get_post_meta( $post->ID, '_merc_variations', true );
    if ( ! is_array( $variations ) ) $variations = [];

    $settings = merc_get_pricing();
    ?>
    <div class="merc-box-section">
        <div class="merc-variasi-toolbar">
            <h3 class="merc-section-title" style="margin:0;">
                🧬 Daftar Variasi
                <span class="merc-tab-badge" data-merc-var-count><?php echo count( $variations ); ?></span>
            </h3>

            <?php if ( ! empty( $variations ) ) : ?>
                <button type="button" class="merc-btn-danger" data-merc-clear-variasi>
                    Hapus Semua Variasi
                </button>
            <?php endif; ?>
        </div>

        <?php if ( empty( $variations ) ) : ?>
            <div class="merc-empty-variasi" data-merc-variasi-empty>
                <svg viewBox="0 0 24 24" width="40" height="40" fill="#c7c9d6"><path d="M4 4h6v6H4zm10 0h6v6h-6zm0 10h6v6h-6zm-10 0h6v6H4z"/></svg>
                <p>Belum ada variasi.</p>
                <p class="hint">Isi atribut di tab <strong>Atribut</strong>, terus klik <strong>Generate Variasi</strong>.</p>
            </div>
        <?php else : ?>

            <!-- BULK EDIT -->
            <div class="merc-bulk-edit" data-merc-bulk-edit>
                <div class="merc-bulk-title">⚡ Bulk Edit</div>
                <div class="merc-bulk-grid">
                    <div>
                        <label>Set Harga Modal (¥)</label>
                        <input type="number" step="0.01" min="0" placeholder="0.00" data-merc-bulk-yuan>
                    </div>
                    <div>
                        <label>Set Berat (gram)</label>
                        <input type="number" step="1" min="0" placeholder="0" data-merc-bulk-berat>
                    </div>
                    <div>
                        <label>Set SKU Prefix</label>
                        <input type="text" placeholder="MRC-001-" data-merc-bulk-sku>
                    </div>
                    <div>
                        <label>Set Stok</label>
                        <input type="number" step="1" min="0" placeholder="0" data-merc-bulk-stok>
                    </div>
                </div>
                <div class="merc-bulk-actions">
                    <button type="button" class="merc-btn-primary" data-merc-bulk-apply>Terapkan ke Semua</button>
                    <button type="button" class="merc-btn-ghost" data-merc-bulk-reset>Reset Field</button>
                </div>
            </div>

            <!-- VARIAN LIST -->
            <div class="merc-variations" data-merc-variations>
                <?php foreach ( $variations as $i => $v ) : ?>
                    <?php merc_render_variasi_row( $i, $v ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="merc-box-section" data-merc-range-section>
        <h3 class="merc-section-title">👁 Preview Range Harga Jual</h3>
        <div class="merc-variant-preview">
            <span class="vp-label">Range harga jual:</span>
            <span class="vp-value" data-merc-variant-range>—</span>
        </div>
    </div>
    <?php
}

function merc_render_variasi_row( $index, $data = [] ) {

    $kombinasi   = $data['kombinasi'] ?? [];
    $kombinasi_c = $data['kombinasi_cache'] ?? '';
    $yuan        = $data['harga_modal_yuan'] ?? '';
    $berat       = $data['berat_gram'] ?? '';
    $sku         = $data['sku'] ?? '';
    $gambar_id   = (int) ( $data['gambar_id'] ?? 0 );
    $stok        = $data['stok'] ?? '';
    $status      = $data['status'] ?? 'active';

    // Label kombinasi
    $label_parts = [];
    foreach ( $kombinasi as $k => $v ) {
        $label_parts[] = $k . ': ' . $v;
    }
    $label = implode( ' · ', $label_parts );

    $gambar_url = $gambar_id ? wp_get_attachment_image_url( $gambar_id, 'thumbnail' ) : '';
    ?>

    <div class="merc-variation-row" data-variation-row data-status="<?php echo esc_attr( $status ); ?>">

        <div class="merc-var-header">
            <div class="merc-var-label">
                <span class="merc-var-dot <?php echo $status === 'active' ? 'is-on' : 'is-off'; ?>" data-merc-var-status-toggle title="Klik buat aktif/nonaktif"></span>
                <strong><?php echo esc_html( $label ?: 'Varian #' . ( $index + 1 ) ); ?></strong>
            </div>

            <div class="merc-var-actions">
                <button type="button" class="merc-var-toggle-details" data-merc-var-toggle-details aria-label="Expand">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                </button>
                <button type="button" class="merc-variant-remove" data-merc-var-remove aria-label="Hapus">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM19 4h-3.5l-1-1h-5l-1 1H5v2h14z"/></svg>
                </button>
            </div>
        </div>

        <div class="merc-var-body">

            <!-- hidden status -->
            <input type="hidden"
                   name="_merc_variations[<?php echo (int) $index; ?>][status]"
                   value="<?php echo esc_attr( $status ); ?>"
                   data-merc-var-status-input>

            <!-- kombinasi: serialize as JSON -->
            <input type="hidden"
                   name="_merc_variations[<?php echo (int) $index; ?>][kombinasi]"
                   value="<?php echo esc_attr( wp_json_encode( $kombinasi ) ); ?>">
            <input type="hidden"
                   name="_merc_variations[<?php echo (int) $index; ?>][kombinasi_cache]"
                   value="<?php echo esc_attr( $label ); ?>">

            <!-- Fields -->
            <div class="merc-var-grid">

                <!-- Gambar -->
                <div class="merc-var-field merc-var-field-img">
                    <label>Gambar</label>
                    <div class="merc-var-img-box">
                        <div class="merc-var-img-preview" data-merc-var-img-preview>
                            <?php if ( $gambar_url ) : ?>
                                <img src="<?php echo esc_url( $gambar_url ); ?>" alt="">
                            <?php else : ?>
                                <span class="merc-img-placeholder">🖼</span>
                            <?php endif; ?>
                        </div>
                        <input type="hidden"
                               name="_merc_variations[<?php echo (int) $index; ?>][gambar_id]"
                               value="<?php echo esc_attr( $gambar_id ); ?>"
                               data-merc-var-img-id>
                        <div class="merc-var-img-actions">
                            <button type="button" class="merc-btn-mini" data-merc-var-img-pick>Pilih</button>
                            <button type="button" class="merc-btn-mini merc-btn-mini-danger" data-merc-var-img-remove>×</button>
                        </div>
                    </div>
                </div>

                <!-- Harga Modal Yuan -->
                <div class="merc-var-field">
                    <label>Harga Modal (¥)</label>
                    <div class="input-prefix">
                        <span>¥</span>
                        <input type="number"
                               name="_merc_variations[<?php echo (int) $index; ?>][harga_modal_yuan]"
                               value="<?php echo esc_attr( $yuan ); ?>"
                               step="0.01" min="0" placeholder="0.00"
                               class="merc-var-yuan">
                    </div>
                    <div class="merc-var-auto-price" data-merc-var-price-preview>
                        <?php
                        $auto = $yuan ? merc_calc_price_from( $yuan, $berat ) : 0;
                        echo $auto ? '→ ' . esc_html( merc_format_rupiah( $auto ) ) : '→ —';
                        ?>
                    </div>
                </div>

                <!-- Berat -->
                <div class="merc-var-field">
                    <label>Berat</label>
                    <div class="input-suffix">
                        <input type="number"
                               name="_merc_variations[<?php echo (int) $index; ?>][berat_gram]"
                               value="<?php echo esc_attr( $berat ); ?>"
                               step="1" min="0" placeholder="0"
                               class="merc-var-berat">
                        <span>gram</span>
                    </div>
                </div>

                <!-- SKU -->
                <div class="merc-var-field">
                    <label>SKU</label>
                    <input type="text"
                           name="_merc_variations[<?php echo (int) $index; ?>][sku]"
                           value="<?php echo esc_attr( $sku ); ?>"
                           placeholder="MRC-001-RED"
                           class="merc-var-sku">
                </div>

                <!-- Stok -->
                <div class="merc-var-field">
                    <label>Stok</label>
                    <input type="number"
                           name="_merc_variations[<?php echo (int) $index; ?>][stok]"
                           value="<?php echo esc_attr( $stok ); ?>"
                           step="1" min="0" placeholder="∞"
                           class="merc-var-stok">
                </div>

            </div>

        </div>

    </div>
    <?php
}

/* ============================================================
   SAVE — ATRIBUT & VARIASI
   ============================================================ */
function merc_save_atribut( $post_id ) {

    $attrs = [];

    if ( isset( $_POST['_merc_attributes'] ) && is_array( $_POST['_merc_attributes'] ) ) {
        foreach ( $_POST['_merc_attributes'] as $row ) {

            $nama = sanitize_text_field( $row['nama'] ?? '' );
            if ( ! $nama ) continue;

            // Values = pipe-separated string
            $raw    = $row['values'] ?? '';
            $vals   = array_map( 'trim', explode( '|', $raw ) );
            $vals   = array_filter( $vals, fn( $v ) => $v !== '' );
            $vals   = array_values( array_unique( $vals ) );

            if ( empty( $vals ) ) continue;

            $attrs[] = [
                'nama'   => $nama,
                'values' => $vals,
            ];
        }
    }

    update_post_meta( $post_id, '_merc_attributes', $attrs );
}

function merc_save_variasi( $post_id ) {

    $vars = [];

    if ( isset( $_POST['_merc_variations'] ) && is_array( $_POST['_merc_variations'] ) ) {
        foreach ( $_POST['_merc_variations'] as $row ) {

            $kombinasi = [];
            if ( ! empty( $row['kombinasi'] ) ) {
                $decoded = json_decode( wp_unslash( $row['kombinasi'] ), true );
                if ( is_array( $decoded ) ) {
                    $kombinasi = array_map( 'sanitize_text_field', $decoded );
                }
            }

            $vars[] = [
                'kombinasi'        => $kombinasi,
                'kombinasi_cache'  => sanitize_text_field( $row['kombinasi_cache'] ?? '' ),
                'harga_modal_yuan' => (float) ( $row['harga_modal_yuan'] ?? 0 ),
                'berat_gram'       => (float) ( $row['berat_gram'] ?? 0 ),
                'sku'              => sanitize_text_field( $row['sku'] ?? '' ),
                'gambar_id'        => absint( $row['gambar_id'] ?? 0 ),
                'stok'             => ( $row['stok'] === '' ? '' : (int) $row['stok'] ),
                'status'           => in_array( $row['status'] ?? '', [ 'active', 'inactive' ], true )
                                        ? $row['status']
                                        : 'active',
            ];
        }
    }

    update_post_meta( $post_id, '_merc_variations', $vars );
}

/**
 * Hitung range harga dari varian aktif.
 */
function merc_calc_variasi_range( $variations ) {

    if ( ! is_array( $variations ) ) return [ 0, 0 ];

    $prices = [];

    foreach ( $variations as $v ) {
        if ( ( $v['status'] ?? 'active' ) !== 'active' ) continue;
        $yuan  = (float) ( $v['harga_modal_yuan'] ?? 0 );
        $berat = (float) ( $v['berat_gram'] ?? 0 );
        if ( ! $yuan ) continue;
        $prices[] = merc_calc_price_from( $yuan, $berat );
    }

    if ( empty( $prices ) ) return [ 0, 0 ];

    return [ min( $prices ), max( $prices ) ];
}