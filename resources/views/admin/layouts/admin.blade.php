<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Office') — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8F9FA] font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        <aside class="w-60 shrink-0 bg-[#1E1E2D] text-white">
            <div class="border-b border-white/10 px-4 py-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-white/45">Back-Office</p>
                <p class="mt-1 font-display text-lg font-semibold tracking-wide text-white">MERCATORIA</p>
            </div>
            <nav class="space-y-0.5 px-3 py-3 text-[13px]">
                <a href="{{ route('office.dashboard') }}" class="block rounded px-3 py-2 text-white transition-colors hover:bg-white/10">Dashboard</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Log</p>
                <a href="{{ route('office.activity-logs') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Activity Log</a>
                <a href="{{ route('office.auth-logs') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Auth Log</a>
                <a href="{{ route('office.admin-logs') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Admin Log</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Produk</p>
                <a href="{{ route('office.products.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Semua Produk</a>
                <a href="{{ route('office.merch-categories.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Kategori Merch</a>
                <a href="{{ route('office.tags.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Tag</a>
                <a href="{{ route('office.stores.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Stores</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Katalog</p>
                <a href="{{ route('office.games.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Game</a>
                <a href="{{ route('office.developers.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Developer</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Konten</p>
                <a href="{{ route('office.hero-slides.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Hero Slides</a>
                <a href="{{ route('office.pages.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Halaman CMS</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Transaksi</p>
                <a href="{{ route('office.orders.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Orders</a>
                <a href="{{ route('office.payments.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Verifikasi Pembayaran</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Pengguna</p>
                <a href="{{ route('office.users.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Users</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Lainnya</p>
                <a href="{{ route('office.reports.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Laporan</a>
                <a href="{{ route('office.settings.index') }}" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Settings</a>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-14 items-center justify-between border-b border-[#EAEAEA] bg-white px-6">
                <span class="font-mono text-[11px] uppercase tracking-[0.16em] text-ink/55">@yield('title', 'Office')</span>
                <div class="flex items-center gap-3 text-sm">
                    <a href="/" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat Frontend</a>
                    <span class="text-ink/75">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-rule px-3 py-1 text-xs font-semibold text-ink transition-colors hover:border-ink">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
