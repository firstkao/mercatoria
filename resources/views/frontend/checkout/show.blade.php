@extends('layouts.app')

@section('title', 'Checkout — MERCATORIA')

@section('content')
    {{-- Font Roboto + background #f7f7f7 (sama dengan beranda/produk/keranjang) --}}
    @include('frontend.partials._auth-style')

    @php
        $initialSubtotal = collect($lines)->sum(fn ($l) => $l['final_price_bersih'] * $l['qty']);
        $needsVerify = auth()->user() && ! auth()->user()->hasVerifiedEmail();
        $needsDob = auth()->user() && ! auth()->user()->tgl_lahir;
        $hasBundle = $items->contains(fn ($i) => $i->bundle_group_id);
        $warnIcon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="#26222f" aria-hidden="true"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>';
    @endphp

    <style>
        /* Checkout — CSS biasa (tidak bergantung build Tailwind). Turunan dari halaman keranjang. */
        .ck-box, .ck-box *, .ck-box *::before, .ck-box *::after { box-sizing: border-box; }
        .ck-box {
            position: relative; left: 50%; transform: translateX(-50%);
            width: min(1200px, calc(100vw - 48px)); margin: 28px 0 28px;
            padding: 47px 58px 80px; background: #fff;
            font-family: 'Roboto', system-ui, sans-serif; font-size: 16px; line-height: 24px; color: #26222f; -webkit-font-smoothing: antialiased;
        }
        .ck-box a { text-decoration: none; color: inherit; }
        .ck-title { margin: 0 0 0 -8px; font-size: 28px; line-height: 34px; font-weight: 500; color: #26222f; }

        /* Notice (gaya sama dengan keranjang) */
        .ck-notice { display: flex; align-items: flex-start; gap: 17px; margin-top: 24px; padding: 16px 23px; border-top: 3px solid #26222f; background: #f7f6f7; font-size: 15px; line-height: 22px; color: #26222f; }
        .ck-notice svg { flex: none; margin-top: 3px; }
        .ck-notice a { color: #02a1ea; }
        .ck-notice a:hover { text-decoration: underline; }
        .ck-notice ul { margin: 0; padding: 0; list-style: none; }
        .ck-notice.is-error { border-top-color: #e53935; background: #fff5f5; color: #c62828; }

        /* Dua kolom: isi (kiri) + ringkasan (kanan) */
        .ck-cols { display: grid; grid-template-columns: minmax(0, 1fr) 430px; gap: 32px; margin-top: 32px; align-items: start; }
        .ck-side { position: sticky; top: 20px; }

        /* Kartu section */
        .ck-sec { margin-bottom: 24px; border: 1px solid #ddd; }
        .ck-sec:last-child { margin-bottom: 0; }
        .ck-sec h2 { margin: 0; height: 53px; padding: 0 20px; border-bottom: 1px solid #ddd; background: #fbfbfb; font-size: 19px; line-height: 52px; font-weight: 400; color: #26222f; }
        .ck-body { padding: 20px; }

        /* Alamat */
        .ck-addr-name { margin: 0; font-weight: 500; color: #26222f; }
        .ck-addr-line { margin: 2px 0 0; font-size: 15px; line-height: 22px; color: #7a7a7a; }
        .ck-addr-link { display: inline-block; margin-top: 12px; font-size: 14px; line-height: 20px; color: #26222f; border-bottom: 1px solid #26222f; }
        .ck-addr-link:hover { color: #02a1ea; border-color: #02a1ea; }

        /* Tabel produk */
        .ck-table-wrap { overflow-x: auto; }
        .ck-table { width: 100%; min-width: 520px; border-collapse: collapse; }
        .ck-table th { padding: 12px 20px 12px 0; border-bottom: 1px solid #ddd; text-align: left; font-size: 14px; line-height: 20px; font-weight: 500; color: #26222f; }
        .ck-table th:first-child, .ck-table td:first-child { padding-left: 20px; }
        .ck-table td { padding: 14px 20px 14px 0; border-bottom: 1px solid #ddd; vertical-align: middle; font-size: 15px; line-height: 22px; color: #7a7a7a; }
        .ck-table tbody tr:last-child td { border-bottom: 0; }
        .ck-thumb { display: block; width: 56px; height: 56px; background: #f1f1f1; }
        .ck-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .ck-item-name { margin: 0; font-size: 15px; line-height: 22px; font-weight: 500; color: #26222f; }
        .ck-item-var { margin: 2px 0 0; font-size: 14px; line-height: 20px; color: #7a7a7a; }

        /* Form */
        .ck-field + .ck-field { margin-top: 20px; }
        .ck-label { display: block; margin: 0 0 8px; font-size: 14px; line-height: 20px; font-weight: 500; color: #26222f; }
        .ck-input { display: block; width: 100%; height: 42px; padding: 0 14px; border: 1px solid #ddd; border-radius: 0; background: #fff;
                    font: 400 15px/20px 'Roboto', system-ui, sans-serif; color: #26222f; outline: 0; transition: border-color .15s; }
        textarea.ck-input { height: auto; padding: 10px 14px; resize: vertical; }
        .ck-input::placeholder { color: #999; }
        .ck-input:focus { border-color: #26222f; }
        .ck-hint { margin: 6px 0 0; font-size: 13px; line-height: 18px; color: #888; }

        /* Pilihan kartu (radio) */
        .ck-opts { display: grid; grid-template-columns: repeat(var(--cols, 2), minmax(0, 1fr)); gap: 12px; }
        .ck-opt { position: relative; display: block; cursor: pointer; }
        .ck-opt input { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
        .ck-opt span { display: block; padding: 12px 14px; border: 1px solid #ddd; background: #fff; font-size: 15px; line-height: 22px; font-weight: 500; color: #26222f; transition: border-color .15s; }
        .ck-opt span small { display: block; margin-top: 1px; font-size: 13px; line-height: 18px; font-weight: 400; color: #888; }
        .ck-opt:hover span { border-color: #999; }
        .ck-opt input:checked + span { border-color: #26222f; box-shadow: inset 0 0 0 1px #26222f; }
        .ck-opt input:focus-visible + span { outline: 2px solid #02a1ea; outline-offset: 2px; }

        /* ═══ Diskon — checkbox Koin ═══ */
        .ck-coin-toggle {
            display: flex; align-items: center; gap: 10px; cursor: pointer;
            padding: 14px 16px;
            border: 1px solid #ddd; background: #fff;
            transition: border-color .15s;
            user-select: none;
        }
        .ck-coin-toggle:hover { border-color: #999; }
        .ck-coin-toggle input { width: 18px; height: 18px; accent-color: #26222f; flex-shrink: 0; cursor: pointer; }
        .ck-coin-toggle .ck-coin-label { font-size: 15px; font-weight: 500; color: #26222f; }
        .ck-coin-toggle .ck-coin-balance { font-size: 13px; color: #888; margin-left: auto; }

        /* Ringkasan */
        .ck-sum { margin: 0; padding: 8px 20px 20px; }
        .ck-sum-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; font-size: 15px; line-height: 22px; color: #26222f; }
        .ck-sum-row dt, .ck-sum-row dd { margin: 0; }
        .ck-sum-row dd { color: #7a7a7a; text-align: right; }
        .ck-sum-row.is-minus dd { color: #e53935; }
        .ck-sum-row.is-main { border-top: 1px solid #ddd; margin-top: 6px; padding-top: 15px; font-weight: 500; }
        .ck-sum-row.is-main dd { color: #26222f; }
        .ck-sum-row.is-muted dt, .ck-sum-row.is-muted dd { font-size: 14px; color: #7a7a7a; }
        .ck-sum-row.is-total { margin-top: 6px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 18px; line-height: 26px; font-weight: 700; }
        .ck-sum-row.is-total dd { color: #26222f; }
        .ck-go { display: block; width: calc(100% - 40px); height: 60px; margin: 0 20px 20px; border: 0; border-radius: 2px; background: #26222f; color: #fff; cursor: pointer;
                 font: 500 17px/60px 'Roboto', system-ui, sans-serif; letter-spacing: .02em; text-transform: uppercase; text-align: center; }
        .ck-go:hover { background: #3a3547; }
        .ck-go:disabled { background: #918f96; cursor: default; }

        @media (max-width: 960px) {
            .ck-box { padding: 28px 18px 48px; }
            .ck-cols { grid-template-columns: 1fr; }
            .ck-side { position: static; }
        }
        @media (max-width: 520px) {
            .ck-opts { grid-template-columns: 1fr; }
        }
    </style>

    <div class="ck-box">
        <h1 class="ck-title">Checkout</h1>

        @if ($errors->any())
            <div class="ck-notice is-error">
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($needsVerify || $needsDob)
            <div class="ck-notice">
                {!! $warnIcon !!}
                <span>
                    @if ($needsVerify)
                        Email kamu belum diverifikasi. Verifikasi dulu agar pesananmu bisa diproses.
                        <a href="{{ route('verification.notice') }}">Kirim ulang link verifikasi</a>
                    @endif
                    @if ($needsVerify && $needsDob)
                        <br>
                    @endif
                    @if ($needsDob)
                        Tanggal lahir belum diisi.
                        <a href="{{ route('profile.complete') }}">Lengkapi profil</a>
                    @endif
                </span>
            </div>
        @endif

        @if ($hasBundle)
            <div class="ck-notice">
                {!! $warnIcon !!}
                <span>Slot Split Box baru terkunci setelah bukti bayar diupload; slot bisa diambil orang lain sebelum itu.</span>
            </div>
        @endif

        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf

            @foreach ($items as $item)
                <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
            @endforeach

            {{-- Hidden field — di-set otomatis oleh JS --}}
            <input type="hidden" name="discount_type" id="discount_type_hidden" value="none">

            <div class="ck-cols">

                {{-- ═════ KIRI ═════ --}}
                <div>
                    {{-- Alamat --}}
                    <section class="ck-sec">
                        <h2>Alamat Pengiriman</h2>
                        <div class="ck-body">
                            <p class="ck-addr-name">{{ auth()->user()->name }}</p>
                            <p class="ck-addr-line">{{ auth()->user()->alamat ?? '—' }}</p>
                            <p class="ck-addr-line">{{ auth()->user()->no_hp ?? '—' }}</p>
                            <a href="{{ route('profile.edit') }}" class="ck-addr-link">Ubah alamat</a>
                        </div>
                    </section>

                    {{-- Produk --}}
                    <section class="ck-sec">
                        <h2>Produk Dipesan</h2>
                        <div class="ck-table-wrap">
                            <table class="ck-table">
                                <thead>
                                    <tr>
                                        <th style="width:76px"></th>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th style="text-align:right;padding-right:20px">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $index => $item)
                                        @php $img = $item->variant?->image_path ?? $item->product->image_path; @endphp
                                        <tr>
                                            <td>
                                                <span class="ck-thumb">
                                                    @if ($img)
                                                        <img src="{{ \App\Services\Image\ImageOptimizerService::url($img, 'thumb') }}" alt="{{ $item->product->name }}">
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <p class="ck-item-name">{{ $item->product->name }}</p>
                                                @if ($item->variant)
                                                    <p class="ck-item-var">Varian: {{ $item->variant->name }}</p>
                                                @endif
                                            </td>
                                            <td>{{ format_rp($lines[$index]['final_price_bersih']) }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td style="text-align:right;padding-right:20px">{{ format_rp($lines[$index]['final_price_bersih'] * $item->qty) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- Pembayaran --}}
                    <section class="ck-sec">
                        <h2>Pembayaran</h2>
                        <div class="ck-body">
                            <div class="ck-field">
                                <span class="ck-label">Platform Marketplace</span>
                                <div class="ck-opts">
                                    <label class="ck-opt"><input type="radio" name="platform" value="toco" class="summary-trigger" checked><span>Toco</span></label>
                                    <label class="ck-opt"><input type="radio" name="platform" value="shopee" class="summary-trigger"><span>Shopee</span></label>
                                </div>
                            </div>

                            <div class="ck-field">
                                <span class="ck-label">Skema Pembayaran</span>
                                <div class="ck-opts">
                                    <label class="ck-opt"><input type="radio" name="scheme" value="fp" class="summary-trigger" checked><span>Full Payment<small>Bayar 100%</small></span></label>
                                    <label class="ck-opt"><input type="radio" name="scheme" value="dp" class="summary-trigger"><span>Down Payment<small>Bayar 50% dulu</small></span></label>
                                </div>
                            </div>

                            <div class="ck-field">
                                <label for="payment_method" class="ck-label">Metode Bayar</label>
                                <select name="payment_method" id="payment_method" class="ck-input summary-trigger">
                                    @foreach ($methods as $method)
                                        <option value="{{ $method->code }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    {{-- ═══ Diskon — checkbox Koin + dropdown Voucher ═══ --}}
                    <section class="ck-sec">
                        <h2>Diskon</h2>
                        <div class="ck-body">
                            {{-- Koin — checkbox toggle --}}
                            <div class="ck-field">
                                <label class="ck-coin-toggle" for="use_coin">
                                    <input type="checkbox" name="use_coin" id="use_coin" value="1" class="summary-trigger">
                                    <span class="ck-coin-label">Gunakan Koin</span>
                                    <span class="ck-coin-balance">Saldo: {{ number_format($coinBalance, 0, ',', '.') }} koin</span>
                                </label>

                                <div id="coin-box" hidden style="margin-top:12px;">
                                    <label for="coin_amount" class="ck-label">Jumlah Koin</label>
                                    <input type="number" name="coin_amount" id="coin_amount" min="0" max="{{ min($coinBalance, $maxCoin) }}"
                                           placeholder="0" class="ck-input summary-trigger">
                                    <p class="ck-hint">Maksimal dipakai {{ number_format($maxCoin, 0, ',', '.') }} koin</p>
                                </div>
                            </div>

                            {{-- Voucher — dropdown langsung --}}
                            <div class="ck-field">
                                <label for="user_voucher_id" class="ck-label">Voucher <span style="font-weight:400;color:#888;">(opsional)</span></label>
                                <select name="user_voucher_id" id="user_voucher_id" class="ck-input summary-trigger">
                                    <option value="">— Tanpa voucher —</option>
                                    @foreach ($userVouchers as $userVoucher)
                                        <option value="{{ $userVoucher->id }}">
                                            {{ $userVoucher->voucher->code }} — potong {{ format_rp($userVoucher->voucher->nominal) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    {{-- Catatan --}}
                    <section class="ck-sec">
                        <h2>Catatan untuk Mercatoria</h2>
                        <div class="ck-body">
                            <textarea name="customer_note" id="customer_note" rows="3" maxlength="500" class="ck-input"
                                      placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B" aria-label="Catatan untuk Mercatoria"></textarea>
                        </div>
                    </section>
                </div>

                {{-- ═════ KANAN: ringkasan — dinamis real-time ═════ --}}
                <aside class="ck-side">
                    <section class="ck-sec">
                        <h2>Ringkasan Pembayaran</h2>
                        <dl class="ck-sum">
                            <div class="ck-sum-row"><dt>Subtotal</dt><dd id="sum-subtotal">{{ format_rp($initialSubtotal) }}</dd></div>
                            <div class="ck-sum-row is-minus"><dt>Diskon</dt><dd id="sum-discount">- Rp0</dd></div>
                            <div class="ck-sum-row is-main"><dt>Total Harga Barang</dt><dd id="sum-total">{{ format_rp($initialSubtotal) }}</dd></div>

                            {{-- Fee QRIS: hidden by default, muncul hanya jika QRIS + ≥ threshold --}}
                            <div class="ck-sum-row is-muted" id="row-fee-qris" style="display:none;"><dt>Fee QRIS</dt><dd id="sum-fee-qris">Rp0</dd></div>

                            <div class="ck-sum-row is-total"><dt>Bayar di Web</dt><dd id="sum-tagihan">{{ format_rp($initialSubtotal) }}</dd></div>

                            {{-- Estimasi CO: muncul hanya saat DP --}}
                            <div class="ck-sum-row is-muted" id="row-estimasi" style="display:none;"><dt>Estimasi CO Marketplace</dt><dd id="sum-estimasi">Rp0</dd></div>

                            {{-- Biaya CO: muncul hanya saat FP --}}
                            <div class="ck-sum-row is-muted" id="row-fee-co"><dt>Biaya CO Marketplace</dt><dd id="sum-fee-co">Rp0</dd></div>
                        </dl>
                        <button type="submit" class="ck-go">Buat Pesanan</button>
                    </section>
                </aside>

            </div>
        </form>

        <script>
            (function () {
                const form = document.getElementById('checkout-form');
                const token = form.querySelector('input[name=_token]').value;
                const cartItemIds = @json($items->pluck('id'));
                const rp = (value) => 'Rp' + Number(value || 0).toLocaleString('id-ID');

                // ═══ Data dari server → JS (kalkulasi instant) ═══
                const CONFIG = {
                    subtotal: @json($initialSubtotal),
                    maxCoin: @json((float) $maxCoin),
                    qrisThreshold: @json((float) setting('qris_threshold', 500000)),
                    qrisRate: @json((float) setting('fee_qris_percent', 0.03)) / 100,
                    dpPercent: @json((float) setting('dp_minimum_percent', 50)) / 100,
                    feeShopeeFp: @json((float) setting('fee_shopee_fp', 109)),
                    feeTocoFp: @json((float) setting('fee_toco_fp', 1)),
                    feeShopeeDp: @json((float) setting('fee_shopee_dp', 25)) / 100,
                    feeTocoDp: @json((float) setting('fee_toco_dp', 0)),
                };

                // Voucher nominal map
                const voucherNominals = {};
                @foreach ($userVouchers as $uv)
                    voucherNominals[{{ $uv->id }}] = {{ (float) $uv->voucher->nominal }};
                @endforeach

                // Element refs
                const useCoin = document.getElementById('use_coin');
                const coinBox = document.getElementById('coin-box');
                const coinAmount = document.getElementById('coin_amount');
                const voucherSelect = document.getElementById('user_voucher_id');
                const discountTypeHidden = document.getElementById('discount_type_hidden');
                const rowFeeQris = document.getElementById('row-fee-qris');
                const rowEstimasi = document.getElementById('row-estimasi');
                const rowFeeCo = document.getElementById('row-fee-co');

                // ═══ Sync toggle: coin box + exclusive koin/voucher ═══
                function syncDiscount() {
                    // Tampilkan input koin hanya jika checkbox dicentang
                    coinBox.hidden = !useCoin.checked;
                    if (!useCoin.checked) coinAmount.value = '';

                    // Exclusive: koin & voucher gak bisa barengan
                    if (useCoin.checked && voucherSelect.value) {
                        voucherSelect.value = '';
                    }
                    if (!useCoin.checked && voucherSelect.value) {
                        // Voucher dipilih tanpa koin — OK
                    }

                    // Update hidden discount_type untuk backend
                    if (useCoin.checked && parseInt(coinAmount.value) > 0) {
                        discountTypeHidden.value = 'coin';
                    } else if (voucherSelect.value) {
                        discountTypeHidden.value = 'voucher';
                    } else {
                        discountTypeHidden.value = 'none';
                    }
                }

                // ═══ Hitung diskon ═══
                function getDiscount() {
                    // Koin: hanya jika checkbox dicentang
                    if (useCoin.checked) {
                        const coins = parseInt(coinAmount.value) || 0;
                        return Math.min(coins, CONFIG.maxCoin);
                    }

                    // Voucher: hanya jika dipilih
                    const vid = parseInt(voucherSelect.value) || 0;
                    if (vid > 0) {
                        return voucherNominals[vid] || 0;
                    }

                    return 0;
                }

                // ═══ Kalkulasi INSTANT (0ms) ═══
                function calculateInstant() {
                    const subtotal = CONFIG.subtotal;
                    const discount = getDiscount();
                    const totalBarang = Math.max(0, subtotal - discount);
                    const scheme = form.scheme.value;
                    const platform = form.platform.value;
                    const method = form.payment_method.value;

                    // QRIS fee
                    const nominalTagihan = scheme === 'dp'
                        ? totalBarang * CONFIG.dpPercent
                        : totalBarang;
                    const feeQris = (method === 'qris' && nominalTagihan >= CONFIG.qrisThreshold)
                        ? Math.round(nominalTagihan * CONFIG.qrisRate)
                        : 0;

                    let tagihanWeb, estimasi, feeCo;

                    if (scheme === 'dp') {
                        const dp = totalBarang * CONFIG.dpPercent;
                        const sisa = totalBarang - dp;
                        tagihanWeb = Math.round(dp + feeQris);
                        estimasi = platform === 'shopee'
                            ? Math.round(sisa * (1 + CONFIG.feeShopeeDp))
                            : Math.round(sisa + CONFIG.feeTocoDp);
                        feeCo = 0;
                    } else {
                        tagihanWeb = Math.round(totalBarang + feeQris);
                        estimasi = 0;
                        feeCo = platform === 'shopee' ? CONFIG.feeShopeeFp : CONFIG.feeTocoFp;
                    }

                    // Update semua angka
                    document.getElementById('sum-subtotal').textContent = rp(subtotal);
                    document.getElementById('sum-discount').textContent = '- ' + rp(discount);
                    document.getElementById('sum-total').textContent = rp(totalBarang);
                    document.getElementById('sum-fee-qris').textContent = rp(feeQris);
                    document.getElementById('sum-tagihan').textContent = rp(tagihanWeb);
                    document.getElementById('sum-estimasi').textContent = rp(estimasi);
                    document.getElementById('sum-fee-co').textContent = rp(feeCo);

                    // Show/hide rows
                    rowFeeQris.style.display = feeQris > 0 ? '' : 'none';
                    rowEstimasi.style.display = scheme === 'dp' ? '' : 'none';
                    rowFeeCo.style.display = scheme === 'dp' ? 'none' : '';
                }

                // ═══ AJAX → presisi dari server ═══
                async function refreshFromServer() {
                    const payload = {
                        cart_item_ids: cartItemIds,
                        platform: form.platform.value,
                        scheme: form.scheme.value,
                        payment_method: form.payment_method.value,
                        discount_type: discountTypeHidden.value,
                        coin_amount: useCoin.checked && coinAmount.value ? Number(coinAmount.value) : null,
                        user_voucher_id: voucherSelect.value ? Number(voucherSelect.value) : null,
                    };

                    try {
                        const response = await fetch('{{ route('checkout.summary') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (!response.ok) return;

                        const s = await response.json();
                        document.getElementById('sum-subtotal').textContent = rp(s.subtotal);
                        document.getElementById('sum-discount').textContent = '- ' + rp(s.discount);
                        document.getElementById('sum-total').textContent = rp(s.total_barang);
                        document.getElementById('sum-fee-qris').textContent = rp(s.fee_qris);
                        document.getElementById('sum-tagihan').textContent = rp(s.tagihan_web);
                        document.getElementById('sum-estimasi').textContent = rp(s.estimasi_co_marketplace ?? 0);
                        document.getElementById('sum-fee-co').textContent = rp(s.fee_co_marketplace ?? 0);

                        rowFeeQris.style.display = s.fee_qris > 0 ? '' : 'none';
                        rowEstimasi.style.display = form.scheme.value === 'dp' ? '' : 'none';
                        rowFeeCo.style.display = form.scheme.value === 'dp' ? 'none' : '';
                    } catch (e) {
                        // Biarkan nilai instant
                    }
                }

                // ═══ Handler ═══
                function onChange() {
                    syncDiscount();
                    calculateInstant();
                    refreshFromServer();
                }

                // Event delegation
                form.addEventListener('change', onChange);
                form.addEventListener('input', function (e) {
                    if (e.target.matches('input[type="number"]')) onChange();
                });

                // Init
                syncDiscount();
                calculateInstant();
                refreshFromServer();

                // Submit
                form.addEventListener('submit', function () {
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn || btn.disabled) return;
                    btn.disabled = true;
                    btn.textContent = 'Memproses...';
                });
            })();
        </script>
    </div>
@endsection
