@extends('layouts.app')

@section('title', 'Pesanan '.$order->no_order.' — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        .od, .od *, .od *::before, .od *::after { box-sizing: border-box; }
        .od {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .od a { text-decoration: none; }

        .od-head-wrap { padding: 36px 40px 28px; background: #fff; }
        .od-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }

        .od-card { background: #fff; border: 1px solid #eee; padding: 24px; margin-top: 20px; }
        .od-h2 { margin: 0 0 12px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #26222f; }

        .od-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .od-no-label { margin: 0; font-size: 12px; font-weight: 500; letter-spacing: .1em; text-transform: uppercase; color: #888; }
        .od-no { margin: 6px 0 0; font-size: 20px; font-weight: 700; color: #26222f; }
        .od-inv { margin: 4px 0 0; font-size: 13px; color: #6f6a72; }
        .od-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }

        .od-table { width: 100%; margin-top: 16px; border-collapse: collapse; }
        .od-table th { padding: 10px 0; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #888; }
        .od-table td { padding: 12px 0; border-bottom: 1px solid #f5f5f5; font-size: 14px; color: #333; }
        .od-table tbody tr:last-child td { border-bottom: 0; }
        .od-item-name { margin: 0; font-weight: 600; color: #26222f; }
        .od-item-var, .od-item-store { margin: 2px 0 0; font-size: 12px; color: #888; }

        .od-sum { margin: 16px 0 0; }
        .od-sum-row { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: 14px; color: #555; }
        .od-sum-row dt, .od-sum-row dd { margin: 0; }
        .od-sum-row.is-danger, .od-sum-row.is-danger dd { color: #e53935; }
        .od-sum-row.is-main, .od-sum-row.is-main dt, .od-sum-row.is-main dd { font-weight: 600; color: #26222f; }
        .od-sum-row.is-total { padding-top: 10px; border-top: 1px solid #eee; font-size: 16px; font-weight: 700; color: #26222f; }

        .od-file { display: block; width: 100%; height: 42px; padding: 8px 12px; border: 1px solid #ddd; background: #fff; font: 400 14px/24px 'Roboto', system-ui, sans-serif; }
        .od-file:focus { border-color: #26222f; outline: 0; }

        .od-tl { margin: 14px 0 0; padding: 0 0 0 16px; border-left: 1px solid #eee; list-style: none; }
        .od-tl li { position: relative; padding-bottom: 14px; }
        .od-tl li:last-child { padding-bottom: 0; }
        .od-dot { position: absolute; left: -21px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: #ddd; }
        .od-dot.is-on { background: #26222f; }
        .od-tl-status { margin: 0; font-size: 14px; }
        .od-tl-meta { margin: 4px 0 0; font-size: 12px; color: #888; }

        @media (max-width: 767px) {
            .od-head-wrap { padding: 24px 16px; }
            .od-title { font-size: 26px; }
            .od-card { padding: 16px; }
        }
    </style>

    <div class="od">
        <div class="od-head-wrap">
            <h1 class="od-title">Detail Pesanan</h1>
        </div>

        <div class="od-card">
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
                <div class="od-sum-row"><dt>Fee QRIS</dt><dd>{{ format_rp($order->fee_qris) }}</dd></div>
                <div class="od-sum-row is-total"><dt>Bayar di Web</dt><dd>{{ format_rp($order->tagihan_web) }}</dd></div>
                @if ($order->estimasi_co_marketplace)
                    <div class="od-sum-row"><dt>Estimasi CO Marketplace</dt><dd>{{ format_rp($order->estimasi_co_marketplace) }}</dd></div>
                @endif
            </dl>

            @if ($order->customer_note)
                <div class="au-note" style="margin-top:16px">
                    <span>Catatan: {{ $order->customer_note }}</span>
                </div>
            @endif

            @if ($order->note_to_customer)
                <div class="au-note" style="margin-top:12px">
                    <span>
                        <b>Pesan dari Admin</b><br>
                        {{ $order->note_to_customer }}
                    </span>
                </div>
            @endif
        </div>

        @if (in_array($order->status, ['menunggu_pembayaran', 'menunggu_denda'], true))
            @php $isDenda = $order->status === 'menunggu_denda'; @endphp
            <div class="od-card">
                <h2 class="od-h2">{{ $isDenda ? 'Bayar & Unggah Bukti Denda' : 'Bayar & Unggah Bukti' }}</h2>
                <p style="margin:0 0 16px;font-size:14px;color:#555">
                    {{ $isDenda ? 'Denda' : 'Tagihan' }}: <strong>{{ format_rp($isDenda ? $order->penalty_amount : $order->tagihan_web) }}</strong>
                    @if ($isDenda && $order->penalty_deadline_at)
                        · Batas waktu: {{ $order->penalty_deadline_at->format('d/m/Y H:i') }}
                    @elseif ($order->payment_deadline_at)
                        · Batas waktu: {{ $order->payment_deadline_at->format('d/m/Y H:i') }}
                    @endif
                </p>

                <form method="POST" action="{{ route('orders.payments.store', $order->no_order) }}" enctype="multipart/form-data" id="upload-proof-form">
                    @csrf
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png" required class="od-file">
                    <p style="margin:6px 0 12px;font-size:12px;color:#888">Format JPG/PNG, maksimal 1 MB.</p>
                    @error('proof')<p class="au-err">{{ $message }}</p>@enderror
                    <button type="submit" class="au-btn">UNGGAH BUKTI</button>
                </form>
            </div>
        @elseif ($order->status === 'ditahan')
            <div class="od-card">
                <h2 class="od-h2">Bukti Bayar Terkirim</h2>
                <p style="margin:0;font-size:14px;color:#555">Menunggu verifikasi admin.</p>
            </div>
        @endif

        @if ($refundPending)
            <div class="od-card">Permintaan refund sedang diproses admin.</div>
        @elseif ($canRequestRefund)
            <form method="POST" action="{{ route('orders.refund-request', $order->no_order) }}" class="od-card">
                @csrf
                <label class="au-label">Ajukan Refund</label>
                <textarea name="reason" rows="2" maxlength="1000" required placeholder="Alasan refund" class="au-input">{{ old('reason') }}</textarea>
                @error('reason')<p class="au-err">{{ $message }}</p>@enderror
                <button type="submit" class="au-btn is-ghost is-sm" style="margin-top:12px">Kirim Permintaan Refund</button>
            </form>
        @endif

        <div class="od-card">
            <h2 class="od-h2">Riwayat Status</h2>
            <ol class="od-tl">
                @foreach ($order->statusLogs as $log)
                    <li>
                        <span class="od-dot {{ $loop->last ? 'is-on' : '' }}"></span>
                        <p class="od-tl-status">
                            <x-order-status-badge :status="$log->status" />
                        </p>
                        <p class="od-tl-meta">
                            {{ $log->created_at->format('d/m/Y H:i') }} · oleh {{ $log->changed_by }}
                            @if ($log->note) — {{ $log->note }} @endif
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
