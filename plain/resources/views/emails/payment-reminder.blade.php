<!DOCTYPE html>
<html>
<head>
    <title>Peringatan Pembayaran</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #d93b3b;">Halo, {{ $user->full_name }}!</h2>
    
    <p>Ini adalah pengingat otomatis dari <strong>MERCATORIA</strong>.</p>
    
    <p>Kami melihat Anda belum mengunggah bukti pembayaran untuk pesanan Anda. Sesuai dengan Syarat & Ketentuan kami, bukti transfer wajib diunggah <strong>dalam 24 jam</strong> setelah checkout.</p>
    
    <!-- <p><strong>Nomor Pesanan:</strong> #{{ $order->id }}</p> -->
    <!-- <p><strong>Total Tagihan:</strong> Rp{{ number_format($order->total_price, 0, ',', '.') }}</p> -->

    <p style="background: #fff4e0; color: #8a5200; padding: 10px; border-left: 4px solid #ffd699;">
        Jika bukti tidak diunggah dalam batas waktu tersebut, pesanan akan <strong>dibatalkan otomatis</strong>. Bagi pengguna yang belum berbelanja sebelumnya, akun Anda juga akan ikut terhapus.
    </p>

    <p>
        <a href="{{ url('/account/orders') }}" style="display: inline-block; padding: 10px 20px; background-color: #0299e7; color: #ffffff; text-decoration: none; border-radius: 5px;">
            Unggah Bukti Pembayaran Sekarang
        </a>
    </p>

    <p>Abaikan email ini jika Anda sudah melakukan pembayaran dan sedang menunggu verifikasi admin.</p>

    <br>
    <p>Terima kasih,<br><strong>MERCATORIA</strong></p>
</body>
</html>
