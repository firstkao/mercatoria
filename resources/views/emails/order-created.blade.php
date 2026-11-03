<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan {{ $order->no_order }} — Mercatoria</title>
</head>
<body style="margin:0;padding:0;background-color:#F2EFE8;font-family:Georgia,'Times New Roman',serif;color:#141B22;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F2EFE8;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:100%;max-width:560px;background-color:#FFFFFF;border:1px solid #D8D2C4;">
                    <tr>
                        <td align="center" style="padding:24px 28px 16px;border-bottom:1px solid #D8D2C4;">
                            <img src="{{ asset('logo.png') }}" alt="Mercatoria" width="180" style="display:block;height:auto;max-width:180px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#141B22;opacity:0.5;">
                                Pesanan
                            </p>
                            <h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:600;color:#141B22;">
                                Pesanan Dibuat
                            </h1>
                            <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                Halo {{ $order->user->name }},
                            </p>
                            <p style="margin:0 0 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                Pesanan <strong>{{ $order->no_order }}</strong> berhasil dibuat.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #D8D2C4;">
                                <tr>
                                    <td style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#141B22;opacity:0.7;border-bottom:1px solid #D8D2C4;">Total Harga Barang</td>
                                    <td align="right" style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;color:#141B22;border-bottom:1px solid #D8D2C4;">{{ format_rp($order->total) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#141B22;opacity:0.7;border-bottom:1px solid #D8D2C4;">Tagihan Web</td>
                                    <td align="right" style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;color:#141B22;border-bottom:1px solid #D8D2C4;">{{ format_rp($order->tagihan_web) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#141B22;opacity:0.7;">Skema &amp; Metode</td>
                                    <td align="right" style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;color:#141B22;">{{ strtoupper($order->scheme) }} · {{ strtoupper($order->payment_method) }}</td>
                                </tr>
                            </table>

                            <p style="margin:18px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                Silakan unggah bukti pembayaran dalam 1×24 jam.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #D8D2C4;">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.6;color:#141B22;opacity:0.55;">
                                Salam,<br>Mercatoria
                            </p>
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#141B22;opacity:0.45;">
                                Email ini dikirim otomatis oleh sistem Mercatoria.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
