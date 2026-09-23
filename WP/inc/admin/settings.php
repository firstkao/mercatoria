<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   MENU
   ============================================================ */
add_action( 'admin_menu', 'merc_admin_menu' );
function merc_admin_menu() {

    add_menu_page(
        'Mercatoria',
        'Mercatoria',
        'manage_options',
        'mercatoria',
        'merc_render_pricing_page',
        'dashicons-store',
        3
    );

    add_submenu_page( 'mercatoria', 'Pricing',      'Pricing',      'manage_options', 'mercatoria',         'merc_render_pricing_page' );
    add_submenu_page( 'mercatoria', 'Level Ongkir', 'Level Ongkir', 'manage_options', 'mercatoria-ongkir',  'merc_render_ongkir_page' );
}

/* ============================================================
   REGISTER SETTINGS
   ============================================================ */
add_action( 'admin_init', 'merc_register_settings' );
function merc_register_settings() {

    register_setting( 'merc_pricing_group', 'merc_pricing', [
        'sanitize_callback' => 'merc_sanitize_pricing',
    ] );

    register_setting( 'merc_ongkir_group', 'merc_ongkir_levels', [
        'sanitize_callback' => 'merc_sanitize_ongkir',
    ] );
}

function merc_sanitize_pricing( $input ) {
    return [
        'kurs_yuan'      => (float) ( $input['kurs_yuan']      ?? 2200 ),
        'markup_persen'  => (float) ( $input['markup_persen']  ?? 30 ),
        'biaya_per_gram' => (float) ( $input['biaya_per_gram'] ?? 5 ),
        'pembulatan'     => absint( $input['pembulatan']       ?? 1000 ),
        'koin_enabled'   => empty( $input['koin_enabled'] ) ? 0 : 1,
        'koin_rate'      => max( 1, absint( $input['koin_rate'] ?? 100 ) ),
    ];
}

function merc_sanitize_ongkir( $input ) {
    $levels = [];
    if ( is_array( $input ) ) {
        foreach ( $input as $row ) {
            if ( empty( $row['nama'] ) ) continue;
            $levels[] = [
                'nama'        => sanitize_text_field( $row['nama'] ),
                'min_belanja' => (float) ( $row['min_belanja'] ?? 0 ),
                'ongkir'      => (float) ( $row['ongkir'] ?? 0 ),
                'keterangan'  => sanitize_text_field( $row['keterangan'] ?? '' ),
            ];
        }
    }
    return $levels;
}

function merc_get_ongkir_levels() {
    $levels = get_option( 'merc_ongkir_levels', null );
    if ( ! is_array( $levels ) || empty( $levels ) ) {
        $levels = [
            [ 'nama' => 'Level 1', 'min_belanja' => 0,      'ongkir' => 15000, 'keterangan' => 'Ongkir penuh' ],
            [ 'nama' => 'Level 2', 'min_belanja' => 100000, 'ongkir' => 10000, 'keterangan' => 'Ongkir setengah' ],
            [ 'nama' => 'Level 3', 'min_belanja' => 300000, 'ongkir' => 0,     'keterangan' => 'Gratis ongkir' ],
        ];
    }
    return $levels;
}

/* ============================================================
   PAGE: PRICING
   ============================================================ */
function merc_render_pricing_page() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    $settings = merc_get_pricing();
    ?>
    <div class="wrap merc-admin-wrap">

        <h1>Pricing & Koin</h1>
        <p class="merc-admin-lead">
            Setting ini dipakai untuk hitung harga jual otomatis semua produk.
            Rumus: <code>((Yuan × Kurs) × (1 + Markup%)) + (Berat × Biaya/gram)</code>, dibulatkan ke atas.
        </p>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'merc_pricing_group' ); ?>

            <div class="merc-admin-card">
                <h2>Parameter Harga</h2>

                <table class="form-table">
                    <tr>
                        <th><label for="kurs_yuan">Kurs 1 Yuan (¥)</label></th>
                        <td>
                            <span class="merc-input-prefix">
                                <span>Rp</span>
                                <input type="number" id="kurs_yuan" name="merc_pricing[kurs_yuan]"
                                       value="<?php echo esc_attr( $settings['kurs_yuan'] ); ?>"
                                       step="1" min="0" class="regular-text">
                            </span>
                            <p class="description">Contoh: 2200 → 1 Yuan = Rp2.200</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="markup_persen">Markup (%)</label></th>
                        <td>
                            <input type="number" id="markup_persen" name="merc_pricing[markup_persen]"
                                   value="<?php echo esc_attr( $settings['markup_persen'] ); ?>"
                                   step="0.1" min="0" class="small-text">
                            <span>%</span>
                            <p class="description">Berapa persen keuntungan di atas modal.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="biaya_per_gram">Biaya per Gram</label></th>
                        <td>
                            <span class="merc-input-prefix">
                                <span>Rp</span>
                                <input type="number" id="biaya_per_gram" name="merc_pricing[biaya_per_gram]"
                                       value="<?php echo esc_attr( $settings['biaya_per_gram'] ); ?>"
                                       step="0.1" min="0" class="small-text">
                            </span>
                            <p class="description">Ditambahkan ke harga berdasarkan berat produk.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="pembulatan">Pembulatan</label></th>
                        <td>
                            <span class="merc-input-prefix">
                                <span>Rp</span>
                                <input type="number" id="pembulatan" name="merc_pricing[pembulatan]"
                                       value="<?php echo esc_attr( $settings['pembulatan'] ); ?>"
                                       step="100" min="1" class="small-text">
                            </span>
                            <p class="description">Bulatkan harga ke atas. Contoh: 1000 → Rp72.250 jadi Rp73.000</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="merc-admin-card">
                <h2>🪙 Sistem Koin</h2>

                <table class="form-table">
                    <tr>
                        <th><label for="koin_enabled">Aktifkan Koin</label></th>
                        <td>
                            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                                <input type="checkbox" id="koin_enabled" name="merc_pricing[koin_enabled]" value="1"
                                       <?php checked( ! empty( $settings['koin_enabled'] ) ); ?>>
                                <span>Tampilkan info koin di halaman produk</span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="koin_rate">Rate Koin</label></th>
                        <td>
                            <span class="merc-input-prefix">
                                <span>Rp</span>
                                <input type="number" id="koin_rate" name="merc_pricing[koin_rate]"
                                       value="<?php echo esc_attr( $settings['koin_rate'] ); ?>"
                                       step="1" min="1" class="small-text">
                            </span>
                            <span>= 1 Koin</span>
                            <p class="description">
                                Contoh: rate 100 → Rp258.000 = <strong>2.580 Koin</strong>.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="merc-admin-card">
                <h2>🧮 Simulasi</h2>
                <p class="description">Coba hitung cepat dengan parameter di atas:</p>

                <div class="merc-sim">
                    <div>
                        <label>Yuan</label>
                        <input type="number" id="sim_yuan" value="25" step="0.01">
                    </div>
                    <div>
                        <label>Berat (gram)</label>
                        <input type="number" id="sim_berat" value="150" step="1">
                    </div>
                    <div class="merc-sim-result">
                        <label>Harga Jual</label>
                        <div id="sim_result">—</div>
                    </div>
                    <div class="merc-sim-result merc-sim-koin">
                        <label>Koin</label>
                        <div id="sim_koin">—</div>
                    </div>
                </div>
            </div>

            <?php submit_button( 'Simpan Setting' ); ?>
        </form>

    </div>
    <?php
}

/* ============================================================
   PAGE: LEVEL ONGKIR
   ============================================================ */
function merc_render_ongkir_page() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    $levels = merc_get_ongkir_levels();
    ?>
    <div class="wrap merc-admin-wrap">

        <h1>Level Ongkir</h1>
        <p class="merc-admin-lead">
            Level ini dipakai untuk atur ongkir di cart/checkout. Setiap produk punya "Level" yang di-set di edit produk.
            Level dengan harga tertinggi sesuai subtotal cart yang bakal dipakai.
        </p>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'merc_ongkir_group' ); ?>

            <div class="merc-admin-card">
                <table class="widefat merc-repeater-table" id="merc-ongkir-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Nama Level</th>
                            <th style="width:160px;">Min. Belanja</th>
                            <th style="width:140px;">Ongkir</th>
                            <th>Keterangan</th>
                            <th style="width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $levels as $i => $row ) : ?>
                            <tr>
                                <td>
                                    <input type="text" name="merc_ongkir_levels[<?php echo (int) $i; ?>][nama]"
                                           value="<?php echo esc_attr( $row['nama'] ); ?>" placeholder="Level 1">
                                </td>
                                <td>
                                    <span class="merc-input-prefix">
                                        <span>Rp</span>
                                        <input type="number" name="merc_ongkir_levels[<?php echo (int) $i; ?>][min_belanja]"
                                               value="<?php echo esc_attr( $row['min_belanja'] ); ?>" step="1000" min="0">
                                    </span>
                                </td>
                                <td>
                                    <span class="merc-input-prefix">
                                        <span>Rp</span>
                                        <input type="number" name="merc_ongkir_levels[<?php echo (int) $i; ?>][ongkir]"
                                               value="<?php echo esc_attr( $row['ongkir'] ); ?>" step="1000" min="0">
                                    </span>
                                </td>
                                <td>
                                    <input type="text" name="merc_ongkir_levels[<?php echo (int) $i; ?>][keterangan]"
                                           value="<?php echo esc_attr( $row['keterangan'] ); ?>" placeholder="Gratis ongkir">
                                </td>
                                <td>
                                    <button type="button" class="merc-repeater-remove button-link" aria-label="Hapus">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="button" class="button" id="merc-ongkir-add" style="margin-top:12px;">
                    + Tambah Level
                </button>
            </div>

            <?php submit_button( 'Simpan Level Ongkir' ); ?>
        </form>

    </div>

    <script>
    (function(){
        var table = document.getElementById('merc-ongkir-table');
        var tbody = table.querySelector('tbody');
        var addBtn = document.getElementById('merc-ongkir-add');

        addBtn.addEventListener('click', function(){
            var idx = tbody.children.length;
            var tr = document.createElement('tr');
            tr.innerHTML = '<td><input type="text" name="merc_ongkir_levels[' + idx + '][nama]" placeholder="Level ' + (idx + 1) + '"></td>' +
                '<td><span class="merc-input-prefix"><span>Rp</span><input type="number" name="merc_ongkir_levels[' + idx + '][min_belanja]" value="0" step="1000" min="0"></span></td>' +
                '<td><span class="merc-input-prefix"><span>Rp</span><input type="number" name="merc_ongkir_levels[' + idx + '][ongkir]" value="0" step="1000" min="0"></span></td>' +
                '<td><input type="text" name="merc_ongkir_levels[' + idx + '][keterangan]"></td>' +
                '<td><button type="button" class="merc-repeater-remove button-link"><span class="dashicons dashicons-trash"></span></button></td>';
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function(e){
            if (e.target.closest('.merc-repeater-remove')) {
                e.target.closest('tr').remove();
            }
        });
    })();
    </script>
    <?php
}