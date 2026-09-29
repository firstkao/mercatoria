<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Kata Sandi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align:center;margin-bottom:24px;">
        <h1 style="color: #0299e7; letter-spacing: 0.05em; margin: 0; font-size: 22px;">MERCATORIA</h1>
    </div>

    <h2 style="color: #312e39;">Halo{{ $user->full_name ? ', ' . $user->full_name : '' }}!</h2>

    <p>Kami menerima permintaan untuk mereset kata sandi akun MERCATORIA kamu.</p>

    <p>Klik tombol di bawah ini untuk membuat kata sandi baru:</p>

    <p style="text-align: center; margin: 32px 0;">
        <a href="{{ $resetUrl }}"
           style="display: inline-block; padding: 14px 28px; background-color: #0299e7; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Reset Kata Sandi
        </a>
    </p>

    <p style="font-size: 13px; color: #666;">
        Atau copy-paste link ini ke browser:<br>
        <a href="{{ $resetUrl }}" style="color: #0299e7; word-break: break-all;">{{ $resetUrl }}</a>
    </p>

    <div style="background: #fff4e0; color: #8a5200; padding: 12px 16px; border-left: 4px solid #ffd699; border-radius: 4px; margin: 24px 0; font-size: 13px;">
        <strong>Penting:</strong> Link ini hanya berlaku selama <strong>{{ $expireMinutes }} menit</strong>. Kalau kamu tidak meminta reset password, abaikan email ini — akunmu tetap aman.
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 32px 0;">

    <p style="font-size: 12px; color: #999; text-align: center;">
        Email otomatis dari MERCATORIA. Jangan balas email ini.
    </p>
</body>
</html>