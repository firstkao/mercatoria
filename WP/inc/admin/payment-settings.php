<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   REGISTER SUBMENU — PAYMENT METHODS
   ============================================================ */
add_action( 'admin_menu', 'merc_payment_register_menu', 20 );
function merc_payment_register_menu() {
    add_submenu_page(
        'mercatoria',
        'Payment Methods',
        'Payment Methods',
        'manage_options',
        'mercatoria-payment',
        'merc_render_payment_page'
    );
}

/* ============================================================
   REGISTER SETTING
   ============================================================ */
add_action( 'admin_init', 'merc_register_payment_setting' );
function merc_register_payment_setting() {
    register_setting( 'merc_payment_group', 'merc_payment_methods', [
        'sanitize_callback' => 'merc_sanitize_payment_methods',
    ] );
}

function merc_sanitize_payment_methods( $input ) {
    $out = [];
    if ( is_array( $input ) ) {
        foreach ( $input as $row ) {
            if ( empty( $row['label'] ) ) continue;

            $out[] = [
                'id'             => ! empty( $row['id'] ) ? sanitize_key( $row['id'] ) : 'method_' . wp_rand( 1000, 9999 ),
                'label'          => sanitize_text_field( $row['label'] ),
                'type'           => in_array( $row['type'] ?? '', [ 'bank', 'qris', 'ewallet' ], true ) ? $row['type'] : 'bank',
                'enabled'        => empty( $row['enabled'] ) ? 0 : 1,
                'account_number' => sanitize_text_field( $row['account_number'] ?? '' ),
                'account_name'   => sanitize_text_field( $row['account_name'] ?? '' ),
                'qris_image_id'  => absint( $row['qris_image_id'] ?? 0 ),
                'instructions'   => sanitize_textarea_field( $row['instructions'] ?? '' ),
            ];
        }
    }
    return $out;
}

/* ============================================================
   RENDER PAGE
   ============================================================ */
function merc_render_payment_page() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    wp_enqueue_media();

    $methods = merc_get_payment_methods();
    ?>
    <div class="wrap merc-admin-wrap">

        <h1>Payment Methods</h1>
        <p class="merc-admin-lead">
            Atur metode pembayaran manual yang muncul di halaman checkout. Customer akan transfer manual ke rekening / scan QRIS, lalu konfirmasi.
        </p>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'merc_payment_group' ); ?>

            <div class="merc-admin-card">
                <div id="merc-payment-list">
                    <?php if ( ! empty( $methods ) ) : ?>
                        <?php foreach ( $methods as $i => $m ) : ?>
                            <?php merc_render_payment_row( $i, $m ); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" class="button" id="merc-payment-add" style="margin-top:12px;">
                    + Tambah Metode Pembayaran
                </button>
            </div>

            <?php submit_button( 'Simpan Payment Methods' ); ?>
        </form>

    </div>

    <!-- Template row (hidden, dipakai oleh JS) -->
    <script type="text/template" id="merc-payment-row-tpl">
        <?php
        ob_start();
        merc_render_payment_row( '__IDX__', [
            'id'             => '',
            'label'          => '',
            'type'           => 'bank',
            'enabled'        => 1,
            'account_number' => '',
            'account_name'   => '',
            'qris_image_id'  => 0,
            'instructions'   => '',
        ] );
        echo ob_get_clean();
        ?>
    </script>

    <script>
    (function(){
        var list = document.getElementById('merc-payment-list');
        var addBtn = document.getElementById('merc-payment-add');
        var tpl = document.getElementById('merc-payment-row-tpl').innerHTML;

        var idxCounter = list.querySelectorAll('.merc-payment-row').length;

        addBtn.addEventListener('click', function(){
            var html = tpl.replace(/__IDX__/g, idxCounter);
            var div = document.createElement('div');
            div.innerHTML = html.trim();
            list.appendChild(div.firstChild);
            idxCounter++;
        });

        list.addEventListener('click', function(e){
            var removeBtn = e.target.closest('.merc-payment-remove');
            if (removeBtn) {
                if (!confirm('Hapus metode ini?')) return;
                removeBtn.closest('.merc-payment-row').remove();
            }

            var pickBtn = e.target.closest('.merc-payment-pick-qris');
            if (pickBtn) {
                e.preventDefault();
                var row = pickBtn.closest('.merc-payment-row');
                var frame = wp.media({ title: 'Pilih QRIS Image', button: { text: 'Pakai' }, multiple: false, library: { type: 'image' } });
                frame.on('select', function(){
                    var att = frame.state().get('selection').first().toJSON();
                    row.querySelector('.merc-payment-qris-id').value = att.id;
                    var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
                    row.querySelector('.merc-payment-qris-preview').innerHTML = '<img src="' + url + '" style="max-width:200px;border-radius:6px;">';
                });
                frame.open();
            }

            var removeQris = e.target.closest('.merc-payment-remove-qris');
            if (removeQris) {
                e.preventDefault();
                var row = removeQris.closest('.merc-payment-row');
                row.querySelector('.merc-payment-qris-id').value = '0';
                row.querySelector('.merc-payment-qris-preview').innerHTML = '';
            }
        });
    })();
    </script>
    <?php
}

/* ============================================================
   RENDER 1 ROW
   ============================================================ */
function merc_render_payment_row( $i, $m ) {

    $id             = $m['id'] ?? '';
    $label          = $m['label'] ?? '';
    $type           = $m['type'] ?? 'bank';
    $enabled        = ! empty( $m['enabled'] );
    $acc            = $m['account_number'] ?? '';
    $acc_name       = $m['account_name'] ?? '';
    $qris_id        = (int) ( $m['qris_image_id'] ?? 0 );
    $instructions   = $m['instructions'] ?? '';
    ?>
    <div class="merc-payment-row" data-idx="<?php echo esc_attr( $i ); ?>">

        <div class="merc-payment-row-head">
            <span class="merc-payment-row-title">Metode #<span class="merc-payment-row-num"><?php echo (int) $i + 1; ?></span></span>
            <button type="button" class="merc-btn-danger merc-payment-remove">Hapus</button>
        </div>

        <input type="hidden" name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][id]" value="<?php echo esc_attr( $id ); ?>">

        <div class="merc-payment-grid">

            <div class="merc-payment-field">
                <label>Nama Metode</label>
                <input type="text"
                       name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][label]"
                       value="<?php echo esc_attr( $label ); ?>"
                       placeholder="Transfer BCA">
            </div>

            <div class="merc-payment-field">
                <label>Tipe</label>
                <select name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][type]">
                    <option value="bank"    <?php selected( $type, 'bank' ); ?>>Transfer Bank</option>
                    <option value="qris"    <?php selected( $type, 'qris' ); ?>>QRIS</option>
                    <option value="ewallet" <?php selected( $type, 'ewallet' ); ?>>E-Wallet</option>
                </select>
            </div>

            <div class="merc-payment-field">
                <label>Status</label>
                <label style="display:flex;align-items:center;gap:6px;font-weight:500;margin-top:8px;">
                    <input type="checkbox"
                           name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][enabled]"
                           value="1"
                           <?php checked( $enabled ); ?>>
                    Aktif
                </label>
            </div>

        </div>

        <div class="merc-payment-fields-bank">
            <div class="merc-payment-grid">
                <div class="merc-payment-field">
                    <label>Nomor Rekening / No. HP</label>
                    <input type="text"
                           name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][account_number]"
                           value="<?php echo esc_attr( $acc ); ?>"
                           placeholder="1234567890">
                </div>
                <div class="merc-payment-field">
                    <label>Atas Nama</label>
                    <input type="text"
                           name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][account_name]"
                           value="<?php echo esc_attr( $acc_name ); ?>"
                           placeholder="PT Mercatoria">
                </div>
            </div>
        </div>

        <div class="merc-payment-fields-qris">
            <div class="merc-payment-field">
                <label>Gambar QRIS</label>
                <input type="hidden"
                       class="merc-payment-qris-id"
                       name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][qris_image_id]"
                       value="<?php echo esc_attr( $qris_id ); ?>">
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <button type="button" class="button merc-payment-pick-qris">Pilih Gambar</button>
                    <button type="button" class="button merc-payment-remove-qris">Hapus</button>
                </div>
                <div class="merc-payment-qris-preview" style="margin-top:8px;">
                    <?php if ( $qris_id ) echo wp_get_attachment_image( $qris_id, 'medium', false, [ 'style' => 'max-width:200px;border-radius:6px;' ] ); ?>
                </div>
            </div>
        </div>

        <div class="merc-payment-field">
            <label>Instruksi</label>
            <textarea name="merc_payment_methods[<?php echo esc_attr( $i ); ?>][instructions]"
                      rows="2"
                      placeholder="Transfer sesuai total ke rekening di atas."><?php echo esc_textarea( $instructions ); ?></textarea>
        </div>

    </div>
    <?php
}