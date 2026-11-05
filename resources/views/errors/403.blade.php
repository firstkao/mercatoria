<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @include('frontend.partials._auth-style')
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;margin:0;background:#f7f7f7;font-family:'Roboto',system-ui,sans-serif">
    @include('frontend.partials._header')

    <main style="flex:1 1 auto;display:flex;align-items:center;justify-content:center;padding:64px 16px">
        <div class="au" style="width:100%;max-width:448px">
            <div class="au-card" style="padding:48px 32px 40px;text-align:center">
                <p class="au-eyebrow">Error 403</p>
                <h1 class="au-title">Akses ditolak</h1>
                <p class="au-lead">
                    Kamu tidak memiliki izin untuk membuka halaman ini.
                </p>
                <a href="{{ route('home') }}" class="au-btn" style="display:inline-block;width:auto;margin-top:24px;padding:0 28px">Kembali ke Beranda</a>
                <div class="au-hr" style="margin-top:28px"></div>
                <nav style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:8px 20px;margin-top:18px;font-size:14px">
                    <a href="{{ route('home') }}" class="au-link">Beranda</a>
                    <a href="{{ route('login') }}" class="au-link">Masuk</a>
                    <a href="{{ route('register') }}" class="au-link">Daftar</a>
                </nav>
            </div>
        </div>
    </main>

    @include('frontend.partials._footer')
</body>
</html>