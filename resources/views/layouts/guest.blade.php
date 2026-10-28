<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mercatoria')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-ink antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="px-4 pt-10 pb-2 sm:pt-12">
            <a href="/" class="mx-auto flex w-fit items-center gap-2.5 no-underline">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-black font-display text-lg font-semibold text-white">M</span>
                <span class="font-mono text-sm font-medium uppercase tracking-[0.28em] text-ink">Mercatoria</span>
            </a>
        </header>

        <main class="flex flex-1 items-start justify-center px-4 py-6 sm:py-8">
            <div class="@yield('width', 'w-full max-w-md')">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-danger/35 bg-white px-4 py-3 text-sm text-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="px-4 pb-8 pt-2">
            <p class="mx-auto max-w-md text-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/45">
                Mercatoria — Merchandise IP Cina
            </p>
        </footer>
    </div>
</body>
</html>
