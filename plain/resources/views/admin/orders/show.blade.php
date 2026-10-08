@extends('layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number])

@section('content')
@php
    $mpName  = $order->marketplace->name ?? '';
    $isToco  = strtolower(trim($mpName)) === 'toco';
    $qrisFee = (int) ($order->qris_fee_idr ?? 0);
@endphp

<div class="account-page">
    <div class="account-card">

        <header class="account-head">
            <p class="account-head__eyebrow">Pesanan</p>
            <h1 class="account-head__title mono">#{{ $order->order_number }}</h1>
            <div class="account-head__meta">
                <span class="account-head__role">{{ $order->statusLabel() }}</span>
                <span class="account-head__dot">·</span>
                <span>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="account-alert account-alert--danger" role="alert">
                <strong class="account-alert__title">Periksa kembali</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($order->status === 'ditahan')
            <div class="account-alert account-alert--info">
                <strong class="account-alert__title">Bukti Pembayaran Sedang Diperiksa</strong>
                <p>
                    Terima kasih, bukti transfer kamu sudah kami terima dan sedang diverifikasi admin.
                    Kamu tidak perlu mengunggah bukti lagi — jika ditolak, kami kirim notifikasi beserta alasannya
                    dan kamu punya 24 jam untuk unggah ulang.
                </p>
            </div>
        @elseif (in_array($order->status, ['menunggu_pembayaran', 'pembayaran_gagal']))
            @php
                $resubmit = $order->latestPaymentProof()?->status === 'rejected'
                    ? $order->latestPaymentProof()->resubmit_deadline_at
                    : null;
            @endphp
            <div class="account-alert account-alert--warning">
                <strong class="account-alert__title">
                    {{ $order->status === 'pembayaran_gagal' ? 'Bukti Pembayaran Ditolak — Unggah Ulang' : 'Menunggu Pembayaran' }}
                </strong>
                <p>
                    @if ($order->payment_deadline_at)
                        {{ $order->status === 'pembayaran_gagal' ? 'Bukti pembayaran kamu belum kami validasi. Perbaiki sesuai alasan penolakan dan unggah ulang pembayaran sebesar' : 'Segera lakukan pembayaran sebesar' }}
                        {{-- Banner reaktif: JS update nilai ini saat user pilih metode QRIS dengan nominal >Rp500rb --}}
                        <strong id="pay-banner-amount">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong>
                        sebelum
                        <strong>{{ $order->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</strong>.
                    @else
                        Segera lakukan pembayaran sebesar
                        <strong id="pay-banner-amount">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong>
                        dan unggah bukti transfer secepatnya.
                    @endif
                </p>
                @if ($order->status === 'pembayaran_gagal' && $resubmit)
                    <p class="account-alert__meta">
                        Batas unggah ulang bukti:
                        <strong>{{ $resubmit->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</strong>.
                    </p>
                @endif
            </div>

            <section class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Upload Bukti Transfer</h2>
                </div>

                <form id="upload-bukti"
                      action="{{ route('account.orders.upload', $order->order_number) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="account-form">
                    @csrf

                    <div class="account-form__grid">
                        <label class="field">
                            <span>Metode Pembayaran / Transfer Ke</span>
                            <select name="payment_method_id" id="pm-select" required>
                                <option value="">Pilih Rekening Tujuan</option>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">
                                        {{ $pm->label }}{{ $pm->account_number ? ' — '.$pm->account_number : '' }}{{ $pm->account_name ? ' ('.$pm->account_name.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field">
                            <span>File Bukti (JPG/PNG/WEBP maks 4 MB)</span>
                            <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
                        </label>

                        <div id="pm-detail" class="account-info-box field--full" style="display:none;">
                            <p id="pm-instructions" class="account-info-box__text"></p>
                            <a id="pm-image-link" href="#" target="_blank" style="display:none;">
                                <img id="pm-image" src="" alt="QR/Barcode" class="account-info-box__img">
                            </a>
                        </div>

                        <div id="qris-preview" class="account-info-box field--full" style="display:none;">
                            <p class="account-info-box__text">
                                Nominal tagihan kamu lebih dari Rp500.000 dan memakai QRIS,
                                jadi ada biaya tambahan <strong>0,3%</strong> dari nominal.
                            </p>
                            <dl class="order-summary" style="margin-top:10px;">
                                <div class="order-summary__row">
                                    <dt>Nominal Tagihan</dt>
                                    <dd id="qris-base">Rp0</dd>
                                </div>
                                <div class="order-summary__row">
                                    <dt>Biaya QRIS (0,3%)</dt>
                                    <dd id="qris-fee">+ Rp0</dd>
                                </div>
                                <div class="order-summary__row order-summary__row--total">
                                    <dt>Total Transfer</dt>
                                    <dd id="qris-total">Rp0</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="account-form__actions">
                        <button type="submit" class="account-btn">Upload &amp; Konfirmasi</button>
                    </div>
                </form>

                <script>
                (function () {
                    var methods   = @json($paymentMethodsJs);
                    var payNowIdr = {{ (int) $order->pay_now_idr }};
                    var sel       = document.getElementById('pm-select');
                    var box       = document.getElementById('pm-detail');
                    var qrisBox   = document.getElementById('qris-preview');
                    var bannerEl  = document.getElementById('pay-banner-amount');
                    if (! sel || ! box) return;

                    function formatRp(n) { return 'Rp' + n.toLocaleString('id-ID'); }

                    function sync() {
                        var m = methods.find(function (x) { return String(x.id) === sel.value; });

                        if (! m) {
                            box.style.display = 'none';
                            if (qrisBox) qrisBox.style.display = 'none';
                            if (bannerEl) bannerEl.textContent = formatRp(payNowIdr);
                            return;
                        }

                        // Detail metode
                        var lines = [];
                        if (m.account_number) lines.push('Transfer ke: ' + m.account_number + (m.account_name ? ' a.n ' + m.account_name : ''));
                        if (m.instructions)   lines.push(m.instructions);
                        document.getElementById('pm-instructions').textContent = lines.join('\n');

                        var imgLink = document.getElementById('pm-image-link');
                        if (m.qr_image) {
                            document.getElementById('pm-image').src = m.qr_image;
                            imgLink.href = m.qr_image;
                            imgLink.style.display = 'block';
                        } else {
                            imgLink.style.display = 'none';
                        }
                        box.style.display = 'block';

                        // Preview QRIS fee
                        var isQris   = m.type === 'qris';
                        var applyFee = isQris && payNowIdr > 500000;
                        var fee      = applyFee ? Math.floor(payNowIdr * 0.003) : 0;

                        if (qrisBox) {
                            if (applyFee) {
                                document.getElementById('qris-base').textContent  = formatRp(payNowIdr);
                                document.getElementById('qris-fee').textContent   = '+ ' + formatRp(fee);
                                document.getElementById('qris-total').textContent = formatRp(payNowIdr + fee);
                                qrisBox.style.display = 'block';
                            } else {
                                qrisBox.style.display = 'none';
                            }
                        }

                        // Sinkronkan banner "Segera lakukan pembayaran sebesar ..."
                        if (bannerEl) {
                            bannerEl.textContent = formatRp(payNowIdr + fee);
                        }
                    }

                    sel.addEventListener('change', sync);
                    sync();
                })();
                </script>
            </section>
        @endif

        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Daftar Barang</h2>
            </div>
            <ul class="order-items">
                @foreach($order->items as $item)
                    @php
                        $effStatus = $item->item_status ?? $order->status;
                        $effLabel  = \App\Enums\OrderStatus::tryFrom($effStatus)?->label() ?? $effStatus;
                        $isOverride = $item->item_status !== null && $item->item_status !== $order->status;
                    @endphp
                    <li class="order-items__row order-items__row--with-thumb">
                        <div class="order-items__thumb">
                            @if($item->variant?->image_path)
                                <img src="{{ $item->variant->imageUrl() }}" alt="">
                            @else
                                <div class="image-placeholder">IMG</div>
                            @endif
                        </div>

                        <div class="order-items__info">
                            <strong class="order-items__name">{{ $item->product_name_snapshot }}</strong>
                            <span class="order-items__meta">
                                Varian: {{ $item->variant_name_snapshot }} · Qty: {{ $item->quantity }}
                            </span>

                            <span class="order-items__status" data-inherit="{{ $isOverride ? '0' : '1' }}">
                                {{ $effLabel }}
                            </span>
                        </div>

                        <div class="order-items__price">
                            {{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Rincian Transaksi</h2>
            </div>
            <dl class="order-summary">
                <div class="order-summary__row">
                    <dt>Subtotal Produk</dt>
                    <dd>{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</dd>
                </div>

                @if($order->discount_idr > 0)
                    <div class="order-summary__row">
                        <dt>Potongan ({{ ucfirst($order->discount_type) }})</dt>
                        <dd class="is-discount">− {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</dd>
                    </div>
                @endif

                <div class="order-summary__row order-summary__row--total">
                    <dt>Total Pesanan</dt>
                    <dd><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->total_idr) }}</strong></dd>
                </div>

                @if($order->payment_scheme === 'DP')
                    <div class="order-summary__row order-summary__row--emphasis">
                        <dt>DP Dibayar Sekarang</dt>
                        <dd>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd>
                    </div>
                    <div class="order-summary__row">
                        <dt>
                            Sisa Pelunasan
                            @unless($isToco)
                                (termasuk biaya admin, di {{ $mpName }})
                            @endunless
                        </dt>
                        <dd>{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</dd>
                    </div>
                @else
                    <div class="order-summary__row order-summary__row--emphasis">
                        <dt>
                            Dibayar Sekarang
                            @unless($isToco)
                                (termasuk admin {{ $mpName }})
                            @endunless
                        </dt>
                        <dd>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd>
                    </div>
                @endif

                @if($qrisFee > 0)
                    <div class="order-summary__row">
                        <dt>Biaya QRIS (0,3%)</dt>
                        <dd>+ {{ \App\Support\PriceCalculator::formatRupiah($qrisFee) }}</dd>
                    </div>
                    <div class="order-summary__row order-summary__row--total">
                        <dt>Total Transfer</dt>
                        <dd><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr + $qrisFee) }}</strong></dd>
                    </div>
                @endif
            </dl>
        </section>

    </div>

    @include('orders.timeline', ['order' => $order])
</div>
@endsection
