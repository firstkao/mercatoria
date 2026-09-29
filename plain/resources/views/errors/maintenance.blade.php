<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sedang Pemeliharaan - MERCATORIA</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Montserrat', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0f9ff, #dbeafe);
            color: #312e39;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }
        .box {
            background: #fff;
            padding: 48px 32px;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 12px 40px rgba(2, 153, 231, 0.08);
            border: 1px solid #e4e3e8;
        }
        .logo {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .08em;
            color: #312e39;
            margin: 0 0 24px;
        }
        .icon {
            font-size: 56px;
            margin-bottom: 20px;
            display: block;
            line-height: 1;
        }
        h1 {
            font-size: 1.5rem;
            margin: 0 0 12px;
            color: #312e39;
        }
        p {
            margin: 0;
            color: #6b6775;
        }
        .divider {
            width: 48px;
            height: 4px;
            background: #0299e7;
            border-radius: 99px;
            margin: 24px auto;
        }
        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 32px;
        }
        a { color: #0299e7; }
    </style>
</head>
<body>
    <div class="box">
        <p class="logo">MERCATORIA</p>
        <span class="icon">🔧</span>
        <h1>Sedang Pemeliharaan</h1>
        <div class="divider"></div>
        <p>{{ $message ?? 'Kami sedang melakukan pemeliharaan. Coba lagi sebentar lagi.' }}</p>
        <p class="footer">
            Terima kasih atas kesabarannya.<br>
            Info: <a href="mailto:cs@mercatoria.id">cs@mercatoria.id</a>
        </p>
    </div>
</body>
</html>