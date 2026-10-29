<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Mercatoria' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F2EFE8;font-family:Georgia,'Times New Roman',serif;color:#141B22;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F2EFE8;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:100%;max-width:560px;background-color:#FFFFFF;border:1px solid #D8D2C4;">
                    <tr>
                        <td align="center" style="padding:24px 28px 16px;border-bottom:1px solid #D8D2C4;">
                            <img src="{{ asset('logo.png') }}" alt="Mercatoria" width="180" style="display:block;height:auto;max-width:180px;">
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#141B22;opacity:0.55;">
                                Merch &amp; Collectibles Store
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            @if (! empty($greeting))
                                <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:18px;font-weight:600;color:#141B22;">
                                    {{ $greeting }}
                                </p>
                            @endif

                            @foreach ($introLines as $line)
                                <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                    {{ $line }}
                                </p>
                            @endforeach

                            @isset($actionText)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:20px 0;">
                                    <tr>
                                        <td style="background-color:#000000;border-radius:6px;">
                                            <a href="{{ $actionUrl }}"
                                               style="display:inline-block;padding:12px 28px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;color:#FFFFFF;text-decoration:none;">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            @foreach ($outroLines as $line)
                                <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                    {{ $line }}
                                </p>
                            @endforeach

                            @isset($actionText)
                                <p style="margin:16px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.5;color:#141B22;opacity:0.6;">
                                    Jika tombol di atas tidak bisa diklik, salin dan tempel tautan berikut ke browser kamu:<br>
                                    <span style="word-break:break-all;">{{ $actionUrl }}</span>
                                </p>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #D8D2C4;">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.6;color:#141B22;opacity:0.55;">
                                @if (! empty($salutation))
                                    {{ $salutation }}
                                @else
                                    Salam,<br>Mercatoria
                                @endif
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
