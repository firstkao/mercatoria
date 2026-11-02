<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">
    @include('frontend.partials._header')

    <main class="flex flex-1 items-center justify-center px-4 py-16">
        <div class="w-full max-w-md rounded-xl border border-rule bg-white px-8 py-12 text-center">
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Error 500</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Terjadi kesalahan</h1>
            <p class="mt-3 text-sm text-ink/65">
                Server sedang bermasalah. Coba lagi beberapa saat lagi. Pesananmu tidak hilang.
            </p>
            <a href="{ route('home') }" class="mt-6 inline-block rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                Kembali ke Beranda
            </a>
            <div class="mt-6 h-px bg-rule"></div>
            <nav class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm">
                <a href="{ route('home') }" class="font-medium text-link underline decoration-link/35 underline-offset-4">Beranda</a>
                <a href="{ route('login') }" class="font-medium text-link underline decoration-link/35 underline-offset-4">Masuk</a>
                <a href="{ route('register') }" class="font-medium text-link underline decoration-link/35 underline-offset-4">Daftar</a>
            </nav>
        </div>
    </main>

    @include('frontend.partials._footer')
</body>
</html>
