<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Packing Slip {{ $order->order_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}?v=1">
</head>
<body class="print-page">
    <div class="print-toolbar">
        <div>
            <strong>Packing Slip {{ $order->order_number }}</strong>
            <span style="color:#64748b;margin-left:8px;font-size:13px;">Tanpa harga — untuk packing barang</span>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.print()" class="print-btn print-btn--primary">🖨️ Print / Save PDF</button>
            <a href="{{ route('admin.orders.show', $order) }}" class="print-btn">← Kembali</a>
        </div>
    </div>

    <div class="paper">
        <header class="paper__header">
            <div class="paper__brand">
                @if ($branding['has_logo'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['store_name'] }}" class="paper__logo">
                @else
                    <div class="paper__brand-name">{{ $branding['store_name'] }}</div>
                @endif
            </div>
            <div class="paper__doc">
                <h1 class="paper__doc-title">PACKING SLIP</h1>
                <table class="paper__doc-meta">
                    <tr><td>No. Order</td><td><strong>#{{ $order->order_number }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y') }}</td></tr>
                    <tr><td>Status</td><td>{{ $order->statusLabel() }}</td></tr>
                </table>
            </div>
        </header>

        <section class="paper__parties">
            <div class="paper__party" style="grid-column: 1 / -1;">
                <div class="paper__party-label">KIRIM KE</div>
                <div class="paper__party-name">{{ $order->user?->full_name ?? '—' }}</div>
                <div class="paper__party-info">
                    @if ($order->user?->whatsapp)
                        <div>WhatsApp: +{{ $order->user->whatsapp }}</div>
                    @endif
                    @if ($order->user?->street_address)
                        <div>{{ $order->user->street_address }}</div>
                        <div>
                            Kec. {{ $order->user->district }}, {{ $order->user->city }}<br>
                            {{ $order->user->province }} {{ $order->user->postal_code }}
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <table class="paper-table">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center;">#</th>
                    <th>Produk</th>
                    <th>Varian</th>
                    <th style="width:60px;text-align:center;">Qty</th>
                    <th style="width:70px;text-align:center;">Cek ✓</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $i => $item)
                    <tr>
                        <td style="text-align:center;">{{ $i + 1 }}</td>
                        <td><strong>{{ $item->product_name_snapshot }}</strong></td>
                        <td>{{ $item->variant_name_snapshot ?: '—' }}</td>
                        <td style="text-align:center;font-size:16px;">{{ $item->quantity }}</td>
                        <td style="text-align:center;">
                            <span class="checkbox-box"></span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="paper__note">
            <strong>Catatan Packing:</strong>
            <div style="margin-top:8px;min-height:80px;border-bottom:1px dashed #cbd5e1;"></div>
        </section>

        <footer class="paper__footer">
            <div>{{ $branding['store_name'] }} · {{ $branding['contact_email'] ?? '' }} {{ $branding['contact_whatsapp'] ? '· ' . $branding['contact_whatsapp'] : '' }}</div>
            <div class="paper__footer-meta">Dicetak: {{ $generatedAt->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</div>
        </footer>
    </div>
</body>
</html>