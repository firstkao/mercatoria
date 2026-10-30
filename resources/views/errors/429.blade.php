<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto flex min-h-[72px] max-w-[1200px] items-center justify-center px-4">
            <a href="/" class="flex items-center no-underline">
                <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-16" width="1608" height="494">
            </a>
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center px-4 py-16">
        <div class="w-full max-w-md rounded-xl border border-rule bg-white px-8 py-12 text-center">
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Error 429</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Terlalu banyak percobaan</h1>
            <p class="mt-3 text-sm text-ink/65">
                Kamu mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar, lalu coba lagi.
            </p>
            <a href="/" class="mt-6 inline-block rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                Kembali ke Beranda
            </a>
            <div class="mt-6 h-px bg-rule"></div>
            <nav class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm">
                <a href="/" class="font-medium text-link underline decoration-link/35 underline-offset-4">Beranda</a>
                <a href="/login" class="font-medium text-link underline decoration-link/35 underline-offset-4">Masuk</a>
                <a href="/register" class="font-medium text-link underline decoration-link/35 underline-offset-4">Daftar</a>
            </nav>
        </div>
    </main>
</body>
</html>
