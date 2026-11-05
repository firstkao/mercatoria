@extends('layouts.app')

@section('title', 'Pesanan '.$order->no_order.' — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Detail pesanan — CSS biasa (tidak bergantung build Tailwind), selaras dengan beranda & halaman produk. */
        .od, .od *, .od *::before, .od *::after { box-sizing: border-box; }
        .od { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .od a { text-decoration: none; }

        .od-card { background: #fff; border: 1px solid #e1e1e1; border-radius: 0; padding: 24px; }
        .od-card + .od-card { margin-top: 20px; }
        .od-h2 { margin: 0 0 12px; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }

        .od-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .od-no-label { margin: 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: #888; }
        .od-no { margin: 6px 0 0; font-size: 18px; line-height: 24px; font-weight: 700; letter-spacing: .04em; color: #000; }
        .od-inv { margin: 4px 0 0; font-size: 12px; line-height: 18px; color: #555; }
        .od-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }

        .od-table { width: 100%; margin-top: 16px; border-collapse: collapse; }
        .od-table th { padding: 10px 0; border-bottom: 1px solid #e1e1e1; text-align: left; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .od-table td { padding: 12px 0; border-bottom: 1px solid #e1e1e1; font-size: 14px; line-height: 21px; color: #333; }
        .od-table tbody tr:last-child td { border-bottom: 0; }
        .od-item-name { margin: 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #000; }
        .od-item-var { margin: 2px 0 0; font-size: 12px; line-height: 18px; color: #888; }
        .od-item-store { margin: 2px 0 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .12em; text-transform: uppercase; color: #888; }

        .od-sum { margin: 16px 0 0; }
        .od-sum-row { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: 14px; line-height: 21px; color: #555; }
        .od-sum-row dt { margin: 0; }
        .od-sum-row dd { margin: 0; color: #333; }
        .od-sum-row.is-danger, .od-sum-row.is-danger dd { color: #e53935; }
        .od-sum-row.is-muted, .od-sum-row.is-muted dd { color: #555; }
        .od-sum-row.is-main, .od-sum-row.is-main dt, .od-sum-row.is-main dd { font-weight: 500; color: #000; }
        .od-sum-row.is-total { padding-top: 10px; border-top: 1px solid #e1e1e1; font-size: 16px; line-height: 24px; font-weight: 700; }
        .od-sum-row.is-total, .od-sum-row.is-total dt, .od-sum-row.is-total dd { color: #000; }

        .od-strong { font-weight: 700; color: #000; }

        .od-file { display: block; width: 100%; height: 42px; padding: 8px 12px; border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
                   font: 400 13px/24px 'Roboto', system-ui, sans-serif; color: #333; outline: 0; }
        .od-file:focus { border-color: #000; }

        .od-tl { margin: 14px 0 0; padding: 0 0 0 16px; border-left: 1px solid #e1e1e1; list-style: none; }
        .od-tl li { position: relative; padding-bottom: 14px; }
        .od-tl li:last-child { padding-bottom: 0; }
        .od-dot { position: absolute; left: -21px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: #e1e1e1; }
        .od-dot.is-on { background: #000; }
        .od-tl-status { margin: 0; font-size: 14px; line-height: 21px; color: #555; }
        .od-tl-status.is-strong { font-weight: 700; color: #000; }
        .od-tl-meta { margin: 4px 0 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .12em; text-transform: uppercase; color: #888; }

        .od-refund-form { margin-top: 20px; }
    </style>

    <div class="au od">
        <p class="au-eyebrow">Pesanan</p>
        <h1 class="au-title">Detail Pesanan</h1>

        <div class="od-card" style="margin-top:24px">
            <div class="od-head">
                <div>
                    <p class="od-no-label">Nomor Pesanan</p>
                    <p class="od-no">{{ $order->no_order }}</p>
                    @if ($order->invoice_number)
                        <p class="od-inv">{{ $order->invoice_number }}</p>
                    @endif
                </div>
                <div class="od-actions">
                    <x-order-status-badge :status="$order->status" size="lg" />
                    <a href="{{ route('orders.invoice', $order->no_order) }}" class="au-btn is-ghost is-sm">
                        Download Invoice PDF
                    </a>
                </div>
            </div>

            <table class="od-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        @php $snapshot = $item->snapshot; @endphp
                        <tr>
                            <td>
                                <p class="od-item-name">{{ $snapshot->product_name }}</p>
                                @if ($snapshot->variant_name)
                                    <p class="od-item-var">Varian: {{ $snapshot->variant_name }}</p>
                                @endif
                                @if ($snapshot->store_name)
                                    <p class="od-item-store">Store: {{ $snapshot->store_name }}</p>
                                @endif
                            </td>
                            <td>{{ format_rp($snapshot->final_price) }}</td>
                            <td>{{ $snapshot->qty }}</td>
                            <td>{{ format_rp($snapshot->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <dl class="od-sum">
                <div class="od-sum-row"><dt>Subtotal</dt><dd>{{ format_rp($order->subtotal) }}</dd></div>
                @if ((float) $order->discount_amount > 0)
                    <div class="od-sum-row is-danger">
                        <dt>Diskon {{ $order->discount_type === 'coin' ? 'Koin' : 'Voucher' }}</dt>
                        <dd>- {{ format_rp($order->discount_amount) }}</dd>
                    </div>
                @endif
                <div class="od-sum-row is-main"><dt>Total Harga Barang</dt><dd>{{ format_rp($order->total) }}</dd></div>
                <div class="od-sum-row is-muted"><dt>Fee QRIS</dt><dd>{{ format_rp($order->fee_qris) }}</dd></div>
                <div class="od-sum-row is-total"><dt>Bayar di Web</dt><dd>{{ format_rp($order->tagihan_web) }}</dd></div>
                @if ($order->estimasi_co_marketplace)
                    <div class="od-sum-row is-muted"><dt>Estimasi CO Marketplace</dt><dd>{{ format_rp($order->estimasi_co_marketplace) }}</dd></div>
                @endif
            </dl>

            @if ($order->customer_note)
                <div class="au-note">
                    <span>Catatan: {{ $order->customer_note }}</span>
                </div>
            @endif

            @if ($order->note_to_customer)
                <div class="au-note">
                    <span>
                        <b>Pesan dari Admin</b><br>
                        {{ $order->note_to_customer }}
                        @if ($order->note_to_customer_updated_at)
                            <br><span class="od-no-label">Diperbarui {{ $order->note_to_customer_updated_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </span>
                </div>
            @endif

            @if ((float) $order->discount_amount > 0 && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
                <div class="au-note">
                    <i class="bi bi-exclamation-triangle au-ico"></i>
                    <span>Koin atau voucher yang dipakai pada pesanan ini hangus permanen bila pembayaran gagal (PRD 9.3).</span>
                </div>
            @endif
        </div>

        @if (in_array($order->status, ['menunggu_pembayaran', 'menunggu_denda'], true))
            @php $isDenda = $order->status === 'menunggu_denda'; @endphp
            <div class="od-card">
                <h2 class="od-h2">{{ $isDenda ? 'Bayar & Unggah Bukti Denda' : 'Bayar & Unggah Bukti' }}</h2>
                <p class="au-lead" style="margin-top:0">
                    {{ $isDenda ? 'Denda' : 'Tagihan' }}: <span class="od-strong">{{ format_rp($isDenda ? $order->penalty_amount : $order->tagihan_web) }}</span>
                    @if ($isDenda && $order->penalty_deadline_at)
                        &middot; Batas waktu: {{ $order->penalty_deadline_at->format('d/m/Y H:i') }}
                    @elseif ($order->payment_deadline_at)
                        &middot; Batas waktu: {{ $order->payment_deadline_at->format('d/m/Y H:i') }}
                    @endif
                </p>

                <form method="POST" action="{{ route('orders.payments.store', $order->no_order) }}" enctype="multipart/form-data"
                      class="au-form" id="upload-proof-form">
                    @csrf
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png" required class="od-file">
                    <p class="od-no-label" style="margin:0">Format JPG/PNG, maksimal 1 MB. Hanya bisa diunggah 1 kali.</p>
                    @error('proof')<p class="au-err">{{ $message }}</p>@enderror
                    <button type="submit" class="au-btn">UNGGAH BUKTI</button>
                </form>
                <script>
                    (function () {
                        var form = document.getElementById('upload-proof-form');
                        if (!form) return;
                        form.addEventListener('submit', function () {
                            var btn = form.querySelector('button[type="submit"]');
                            if (!btn || btn.disabled) return;
                            btn.disabled = true;
                            btn.textContent = 'Memproses...';
                        });
                    })();
                </script>
            </div>
        @elseif ($order->status === 'ditahan')
            <div class="od-card">
                <h2 class="od-h2">Bukti Bayar Terkirim</h2>
                <p class="au-lead" style="margin-top:0">
                    Menunggu verifikasi admin. Form upload terkunci sampai admin menyetujui atau menolak bukti kamu.
                </p>
            </div>
        @endif

        @if ($refundPending)
            <div class="od-card">
                Permintaan refund sedang diproses admin.
            </div>
        @elseif ($canRequestRefund)
            <form method="POST" action="{{ route('orders.refund-request', $order->no_order) }}"
                  class="od-card od-refund-form"
                  data-confirm="Ajukan refund untuk pesanan ini?" data-confirm-title="Ajukan Refund" data-confirm-text="Ajukan">
                @csrf
                <label for="refund-reason" class="au-label">Ajukan Refund</label>
                <textarea id="refund-reason" name="reason" rows="2" maxlength="1000" required placeholder="Alasan refund"
                          class="au-input">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="au-err">{{ $message }}</p>
                @enderror
                <button type="submit" class="au-btn is-ghost is-sm">Kirim Permintaan Refund</button>
            </form>
        @endif

        <div class="od-card">
            <h2 class="od-h2">Riwayat Status</h2>
            <ol class="od-tl">
                @foreach ($order->statusLogs as $log)
                    <li>
                        <span class="od-dot {{ $loop->last ? 'is-on' : '' }}"></span>
                        <p class="od-tl-status {{ $loop->last ? 'is-strong' : '' }}">
                            <x-order-status-badge :status="$log->status" />
                        </p>
                        <p class="od-tl-meta">
                            {{ $log->created_at->format('d/m/Y H:i') }} &middot; oleh {{ $log->changed_by }}
                            @if ($log->note) — {{ $log->note }} @endif
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
