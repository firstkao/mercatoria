<!DOCTYPE html>
<html>
<head>
    <title>Pesan Kontak Baru</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #0299e7;">Pesan Kontak Baru</h2>

    <p>Ada pesan baru dari form kontak MERCATORIA:</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding:4px 12px 4px 0;color:#666;">Nama</td><td><strong>{{ $message->name }}</strong></td></tr>
        <tr><td style="padding:4px 12px 4px 0;color:#666;">Email</td><td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td></tr>
        @if ($message->whatsapp)
            <tr><td style="padding:4px 12px 4px 0;color:#666;">WhatsApp</td><td>{{ $message->whatsapp }}</td></tr>
        @endif
        <tr><td style="padding:4px 12px 4px 0;color:#666;">Subjek</td><td>{{ $message->subject }}</td></tr>
        <tr><td style="padding:4px 12px 4px 0;color:#666;">IP</td><td class="muted">{{ $message->ip_address }}</td></tr>
    </table>

    <div style="background: #f7f7f7; padding: 16px; border-radius: 8px; border-left: 4px solid #0299e7;">
        {!! nl2br(e($message->message)) !!}
    </div>

    <p style="margin-top: 24px; font-size: 13px; color: #888;">
        Dikirim otomatis dari form kontak di {{ url('/') }}
    </p>
</body>
</html>