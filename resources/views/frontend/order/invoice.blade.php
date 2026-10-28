<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->no_order }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; position: relative; }
        .watermark {
            position: fixed; top: 45%; left: 15%; transform: rotate(-30deg);
            font-size: 64px; color: rgba(0, 0, 0, 0.06); font-weight: 700; letter-spacing: 8px;
        }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: 700; letter-spacing: 2px; }
        .meta { margin-top: 4px; color: #444; line-height: 1.5; }
        h1 { font-size: 14px; margin: 0 0 2px; }
        .no { font-size: 13px; font-weight: 700; letter-spacing: 1px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th { background: #f2f2f2; text-align: left; padding: 6px 8px; border: 1px solid #ddd; }
        table.items td { padding: 6px 8px; border: 1px solid #ddd; }
        .totals { width: 45%; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 3px 6px; }
        .totals td.r { text-align: right; }
        .grand td { font-weight: 700; border-top: 1px solid #000; }
        .note { margin-top: 12px; padding: 8px; background: #f7f7f7; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <div class="watermark">MERCATORIA</div>

    <div class="header">
        <div class="brand">MERCATORIA</div>
        <div class="meta">
            mercatoria.id · CS: info@mercatoria.id<br>
            Invoice = Bukti Pembayaran
        </div>
    </div>

    <h1>Invoice</h1>
    <div class="no">{{ $order->no_order }}</div>
    <div class="meta">
        Tanggal: {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Pembeli: {{ $order->user->name }}<br>
        Skema: {{ strtoupper($order->scheme) }} · Metode: {{ strtoupper($order->payment_method) }} · Platform: {{ ucfirst($order->platform) }}
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php $snapshot = $item->snapshot; @endphp
                <tr>
                    <td>
                        {{ $snapshot->product_name }}
                        @if ($snapshot->variant_name)
                            <br><span style="color:#666">Varian: {{ $snapshot->variant_name }}</span>
                        @endif
                    </td>
                    <td>{{ $snapshot->qty }}</td>
                    <td>{{ format_rp($snapshot->final_price) }}</td>
                    <td>{{ format_rp($snapshot->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="r">{{ format_rp($order->subtotal) }}</td></tr>
        @if ((float) $order->discount_amount > 0)
            <tr>
                <td>Diskon {{ $order->discount_type === 'coin' ? 'Koin' : 'Voucher' }}</td>
                <td class="r">- {{ format_rp($order->discount_amount) }}</td>
            </tr>
        @endif
        <tr><td>Fee QRIS</td><td class="r">{{ format_rp($order->fee_qris) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="r">{{ format_rp($order->total) }}</td></tr>
        <tr class="grand"><td>Tagihan Web</td><td class="r">{{ format_rp($order->tagihan_web) }}</td></tr>
    </table>

    @if ($order->customer_note)
        <div class="note"><strong>Catatan Pembelian:</strong> {{ $order->customer_note }}</div>
    @endif

    <div class="footer">
        Terima kasih telah berbelanja di Mercatoria. Dengan melanjutkan transaksi Anda menyetujui Syarat &amp; Ketentuan yang berlaku di mercatoria.id.
    </div>
</body>
</html>
