<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Packing List {{ $order->no_order }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: 700; letter-spacing: 2px; }
        .meta { margin-top: 4px; color: #444; line-height: 1.5; }
        h1 { font-size: 14px; margin: 0 0 2px; }
        .no { font-size: 13px; font-weight: 700; letter-spacing: 1px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th { background: #f2f2f2; text-align: left; padding: 6px 8px; border: 1px solid #ddd; }
        table.items td { padding: 6px 8px; border: 1px solid #ddd; }
        .check { width: 40px; text-align: center; font-size: 14px; }
        .note { margin-top: 12px; padding: 8px; background: #fff3cd; border: 1px solid #ffe08a; }
        .sign { margin-top: 48px; width: 200px; border-top: 1px solid #000; padding-top: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">MERCATORIA</div>
        <div class="meta">
            {{ url('/') }} · CS: cs@mercatoria.id · WA Admin: {{ setting('url_whatsapp', '-') }}
        </div>
    </div>

    <h1>Packing List</h1>
    <div class="no">{{ $order->no_order }}</div>
    <div class="meta">
        Tanggal: {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Pembeli: {{ $order->user->name }}
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Varian</th>
                <th>Qty</th>
                <th class="check">&#10003;</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php $snapshot = $item->snapshot; @endphp
                <tr>
                    <td>{{ $snapshot->product_name }}</td>
                    <td>{{ $snapshot->variant_name ?: '-' }}</td>
                    <td>{{ $snapshot->qty }}</td>
                    <td class="check">&#9744;</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($order->customer_note)
        <div class="note"><strong>Catatan Pembelian:</strong> {{ $order->customer_note }}</div>
    @endif
    @if ($order->note_to_customer)
        <div class="note"><strong>Note to Customer (admin):</strong> {{ $order->note_to_customer }}</div>
    @endif

    <div class="sign">Tanda tangan packer</div>
</body>
</html>
