<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Packing List {{ $order->order_no }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    h1 { font-size: 20px; margin: 0 0 4px; color: #1a4d8f; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
    th { background: #f5f7fa; }
    .box { margin-top: 14px; padding: 8px 10px; border-radius: 4px; }
    .customer-note { background: #fff9c4; }
    .admin-note { background: #e3f2fd; }
    .sign { margin-top: 40px; width: 200px; border-top: 1px solid #1f2937; padding-top: 4px; text-align: center; }
</style>
</head>
<body>
    <h1>MERCATORIA — PACKING LIST (INTERNAL GUDANG)</h1>
    <p>Order: <strong>{{ $order->order_no }}</strong><br>
       Nama Pembeli: <strong>{{ $order->customer_name }}</strong> (TANPA ALAMAT — Privasi &amp; Keamanan Gudang)<br>
       Tanggal: {{ $order->created_at->format('d M Y') }}</p>

    <table>
        <thead><tr><th style="width:40px">Cek</th><th>Nama Produk</th><th style="width:40px">Qty</th><th style="width:110px">SKU</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            @php($s = $item->snapshot)
            @if ($s)
            <tr>
                <td>☐</td>
                <td>{{ $s->product_name }}@if ($s->variant_name) — {{ $s->variant_name }}@endif</td>
                <td>{{ $s->qty }}</td>
                <td>{{ $s->variant_sku ?: $s->product_sku }}</td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    @if ($order->customer_note)
        <div class="box customer-note"><strong>CATATAN PEMBELIAN DARI USER (customer_note):</strong><br>{!! nl2br(e($order->customer_note)) !!}</div>
    @endif
    @if ($order->note_to_customer)
        <div class="box admin-note"><strong>CATATAN ADMIN UNTUK PELANGGAN (note_to_customer):</strong><br>{!! nl2br(e($order->note_to_customer)) !!}</div>
    @endif

    <p style="margin-top:40px">Diperiksa &amp; Dikemas Oleh (Tanda Tangan Packer): ______________________</p>
</body>
</html>
