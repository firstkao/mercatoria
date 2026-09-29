<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align:center;margin-bottom:24px;">
        <h1 style="color: #0299e7; letter-spacing: 0.05em; margin: 0; font-size: 22px;">MERCATORIA</h1>
    </div>

    <h2 style="color: #312e39;">Halo{{ $user->full_name ? ', ' . $user->full_name : '' }}!</h2>

    <p>Terima kasih sudah mendaftar di MERCATORIA. Untuk mengaktifkan akun, masukkan kode verifikasi di bawah ini:</p>

    <div style="background: #eef9ff; border: 1px solid #c7ecff; border-radius: 12px; padding: 32px; text-align: center; margin: 28px 0;">
        <p style="margin: 0 0 8px; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.1em;">Kode Verifikasi</p>
        <div style="font-family: ui-monospace, monospace; font-size: 42px; font-weight: 700; letter-spacing: 0.25em; color: #0299e7; line-height: 1;">
            {{ $otp }}
        </div>
    </div>

    <p style="text-align: center;">
        <a href="{{ route('verification.notice') }}"
           style="display: inline-block; padding: 12px 28px; background-color: #0299e7; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Buka Halaman Verifikasi
        </a>
    </p>

    <div style="background: #fff4e0; color: #8a5200; padding: 12px 16px; border-left: 4px solid #ffd699; border-radius: 4px; margin: 24px 0; font-size: 13px;">
        <strong>Catatan:</strong> Kode ini hanya berlaku <strong>{{ $expireMinutes }} menit</strong>. Kalau kamu tidak mendaftar di MERCATORIA, abaikan email ini.
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 32px 0;">

    <p style="font-size: 12px; color: #999; text-align: center;">
        Email otomatis dari MERCATORIA. Jangan balas email ini.
    </p>
</body>
</html>