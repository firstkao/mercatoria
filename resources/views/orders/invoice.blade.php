<!doctype html>
<html lang='id'>
<head>
    <meta charset='utf-8'><title>Invoice {{ $order->order_no }}</title>
    <style>
        @page { size: A4; margin: 28px 32px; } * { box-sizing: border-box; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
        .header, .meta, .totals { width: 100%; border-collapse: collapse; } .header td { vertical-align: top; }
        .brand { color: #0b1b3a; font-size: 19px; font-weight: bold; } .muted { color: #64748b; }
        .invoice-title { color: #0b1b3a; font-size: 20px; font-weight: bold; text-align: right; }
        .meta { margin: 22px 0 14px; } .meta td { padding: 4px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; } .items th { background: #f1f5f9; text-align: left; }
        .items th, .items td { border: 1px solid #cbd5e1; padding: 8px; } .right { text-align: right; white-space: nowrap; }
        .totals { margin-top: 12px; } .totals td { padding: 4px 8px; text-align: right; }
        .grand { border-top: 2px solid #0b1b3a; color: #0b1b3a; font-size: 14px; font-weight: bold; }
        .note { background: #f8fafc; margin-top: 16px; padding: 10px; } .footer { border-top: 1px solid #cbd5e1; color: #64748b; margin-top: 24px; padding-top: 10px; text-align: center; }
        .watermark { color: rgba(30, 64, 175, .06); font-size: 42px; font-weight: bold; left: 10%; position: fixed; top: 45%; transform: rotate(-28deg); }
    </style>
</head>
<body>
<div class='watermark'>MERCATORIA</div>
<table class='header'><tr><td><div class='brand'>[M] MERCATORIA</div><div>Email CS: cs@mercatoria.id · WA Admin: {{ setting('wa_admin_phone', '—') }}</div><div class='muted'>https://mercatoria.id</div></td><td><div class='invoice-title'>INVOICE RESMI</div><div class='right'>#INV-{{ str_replace('MER-', '', $order->order_no) }}</div><div class='right'>{{ $order->created_at->format('d F Y') }}</div></td></tr></table>
<table class='meta'>
<tr><td><strong>No. Pesanan:</strong> {{ $order->order_no }}</td><td><strong>Pembeli:</strong> {{ $order->customer_name }}<br><span class="muted">{{ $order->user?->currentState()->label() ?? "Customer" }} · {{ $order->user?->currentTier() ?? "Tier belum ditentukan" }}</span></td></tr>
<tr><td><strong>Metode Bayar:</strong> {{ strtoupper($order->payment_method) }}</td><td><strong>Skema:</strong> {{ $order->payment_scheme === 'fp' ? 'Full Payment (100%)' : 'Down Payment (50%)' }}</td></tr>
<tr><td><strong>Platform:</strong> {{ ucfirst($order->platform) }}</td><td><strong>Status:</strong> {{ $order->status_enum->label() }}</td></tr>
<tr><td colspan='2' class='muted'>Invoice bukti pembayaran tanpa alamat pengiriman.</td></tr></table>
<table class='items'><thead><tr><th>No</th><th>Produk &amp; Varian (Snapshot)</th><th class='right'>Qty</th><th class='right'>Harga</th><th class='right'>Subtotal</th></tr></thead><tbody>
@foreach ($order->items->sortBy('id') as $item)
    @if ($item->snapshot)<tr><td>{{ $loop->iteration }}</td><td><strong>{{ $item->snapshot->product_name }}</strong>@if ($item->snapshot->variant_name)<br><span class='muted'>Varian: {{ $item->snapshot->variant_name }}</span>@endif@if ($item->snapshot->product_sku)<br><span class='muted'>SKU: {{ $item->snapshot->product_sku }}</span>@endif</td><td class='right'>{{ $item->snapshot->qty }}</td><td class='right'>{{ format_rupiah($item->snapshot->base_price) }}</td><td class='right'>{{ format_rupiah($item->snapshot->base_price * $item->snapshot->qty) }}</td></tr>@endif
@endforeach
</tbody></table>
@php($snapshotSubtotal = $order->items->sum(fn ($item) => $item->snapshot ? $item->snapshot->base_price * $item->snapshot->qty : 0))
<table class='totals'><tr><td>Subtotal Item</td><td>{{ format_rupiah($snapshotSubtotal) }}</td></tr>
@if ($order->coin_discount_amount > 0)<tr><td>Diskon Koin</td><td>-{{ format_rupiah($order->coin_discount_amount) }}</td></tr>@endif
@if ($order->voucher_discount_amount > 0)<tr><td>Diskon Voucher</td><td>-{{ format_rupiah($order->voucher_discount_amount) }}</td></tr>@endif
@php($productDiscount = max(0, $order->discount_amount - $order->coin_discount_amount - $order->voucher_discount_amount))
@if ($productDiscount > 0)<tr><td>Diskon Produk</td><td>-{{ format_rupiah($productDiscount) }}</td></tr>@endif
@if ($order->fee_marketplace > 0)<tr><td>Fee Marketplace</td><td>+{{ format_rupiah($order->fee_marketplace) }}</td></tr>@endif
@if ($order->fee_qris > 0)<tr><td>Fee QRIS</td><td>+{{ format_rupiah($order->fee_qris) }}</td></tr>@endif
<tr class='grand'><td>Total Dibayar (Pembulatan)</td><td>{{ format_rupiah($order->total) }}</td></tr></table>
@if ($order->customer_note)<div class='note'><strong>Catatan Pembelian:</strong><br>{{ $order->customer_note }}</div>@endif
<div class='footer'>Terima kasih telah berbelanja di Mercatoria! Simpan invoice ini sebagai bukti pembayaran. Sesuai Syarat &amp; Ketentuan mercatoria.id/tnc.</div>
</body></html>
