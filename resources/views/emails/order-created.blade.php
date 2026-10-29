<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pesanan {{ $order->no_order }} — Mercatoria</title>
</head>
<body style="font-family: sans-serif; color: #111;">
    <h2 style="letter-spacing: 2px;">MERCATORIA</h2>
    <p>Halo {{ $order->user->name }},</p>
    <p>Pesanan <strong>{{ $order->no_order }}</strong> berhasil dibuat.</p>
    <ul>
        <li>Total Harga Barang: {{ format_rp($order->total) }}</li>
        <li>Tagihan Web: {{ format_rp($order->tagihan_web) }}</li>
        <li>Skema: {{ strtoupper($order->scheme) }} · Metode: {{ strtoupper($order->payment_method) }}</li>
    </ul>
    <p>Silakan unggah bukti pembayaran dalam 1×24 jam.</p>
    <p style="color:#666;font-size:12px;">Email ini dikirim otomatis oleh sistem Mercatoria.</p>
</body>
</html>
