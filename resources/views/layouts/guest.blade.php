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
<body id="top" class="flex min-h-screen flex-col font-sans text-ink antialiased">
{{-- Top Promo Bar (DS 4.4.1 / PRD 40.1) --}}
       @if (setting('promo_bar_active', '1') !== '0')
        <div id="promo-bar" class="relative bg-[#F2EFE8] px-10 py-2 text-center border-b border-[#D8D2C4]">
            <p class="text-[13px] text-ink">
                Gunakan kode promo <strong class="font-semibold text-danger">NEWWEB</strong> dan nikmati potongan harga sebesar <strong class="font-semibold italic">Rp 25.000</strong> dengan minimal pembelanjaan <strong class="font-semibold italic">Rp 150.000</strong>.
            </p>
        </div>
    @endif

    {{-- Main Navbar guest (Design Spec 4.4.1 + 4.6.2) --}}
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto max-w-[1200px] px-4">
            {{-- Desktop 3-kolom: Menu Kiri &middot; Logo Tengah &middot; Utility Kanan --}}
            <div class="hidden min-h-[72px] lg:grid lg:grid-cols-3 lg:items-center">
                <nav class="flex items-center gap-5" aria-label="Menu utama">
                    <a href="{{ route('home') }}" class="text-[14px] font-medium text-[#4FC3F7]">Beranda</a>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Game
                            <span class="text-[12px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @isset($footerGames)
                                @forelse ($footerGames as $game)
                                    <a href="{{ data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#') }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">
                                        {{ data_get($game, 'name') }}
                                    </a>
                                @empty
                                    <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                                @endforelse
                            @else
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                            @endisset
                        </div>
                    </details>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Developer
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @isset($footerDevelopers)
                                @forelse ($navDevelopers as $navDeveloper)
    <a href="{{ route('catalog.developer', $navDeveloper->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $navDeveloper->name }}</a>
@empty
    <a href="{{ route('coming-soon') }}" class="block px-3.5 py-1.5 text-[12px] text-[#999]">Menyusul ({{ $navDevelopers->count() }} items debug)</a>
@endforelse
                            @else
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                            @endisset
                        </div>
                    </details>

                    <a href="#" class="text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
                </nav>

                <a href="{{ route('home') }}" class="flex flex-col items-center justify-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" style="height: 50px; width: auto;">
                </a>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('search') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Cari">
                        <x-icons.magnifying-glass width="18" height="18" />
                    </a>

                    <div class="flex items-center gap-4 text-[#333]">
                        <a href="{{ setting('url_instagram', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Instagram">
                            <x-icons.instagram width="16" height="16" />
                        </a>
                        <a href="{{ setting('url_facebook', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Facebook">
                            <x-icons.facebook width="16" height="16" />
                        </a>
                        <a href="{{ setting('url_x', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="X">
                            <x-icons.twitter-x width="16" height="16" />
                        </a>
                        <a href="{{ setting('url_threads', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Threads">
                            <x-icons.threads width="16" height="16" />
                        </a>
                        <a href="{{ setting('url_whatsapp', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp">
                            <x-icons.whatsapp width="16" height="16" />
                        </a>
                    </div>

                    <a href="{{ route('login') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Masuk">
                        <x-icons.user width="18" height="18" />
                    </a>
                </div>
            </div>

            {{-- Mobile: Hamburger &middot; Logo &middot; User --}}
            <div class="flex min-h-[64px] items-center justify-between gap-3 lg:hidden">
                <details class="relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-[#333]" aria-label="Menu">
                        <x-icons.list width="22" height="22" />
                    </summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-64 rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                        <a href="{{ route('home') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
                        <a href="{{ route('search') }}" class="block px-4 py-2.5 text-[13px] text-[#333]">Cari</a>
                        <a href="#" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                            @isset($footerGames)
                                @foreach ($footerGames as $game)
                                    <a href="{{ data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#') }}" class="block px-4 py-2 text-[12px] text-[#333]">
                                        {{ data_get($game, 'name') }}
                                    </a>
                                @endforeach
                            @endisset
                        </div>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                            @isset($footerDevelopers)
                                @foreach ($footerDevelopers as $developer)
                                    <a href="{{ data_get($developer, 'url') ?? (data_get($developer, 'slug') ? route('catalog.developer', data_get($developer, 'slug')) : '#') }}" class="block px-4 py-2 text-[12px] text-[#333]">
                                        {{ data_get($developer, 'name') }}
                                    </a>
                                @endforeach
                            @endisset
                        </div>
                    </div>
                </details>

                <a href="{{ route('home') }}" class="flex flex-col items-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-9 w-auto" width="1608" height="494">
                </a>

                <a href="{{ route('login') }}" class="flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Masuk">
                    <x-icons.user width="20" height="20" />
                </a>
            </div>
        </div>
    </header>

    <main class="flex flex-1 items-start justify-center px-4 py-8 sm:py-10">
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

    {{-- Footer (Design Spec 4.4.5) --}}
    <footer class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                {{-- Kolom 1: Brand & Marketplace --}}
                <div class="flex flex-col items-center">
                    <a href="{{ route('home') }}" class="inline-flex flex-col items-center no-underline">
                        <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-10 w-auto" width="1608" height="494">
                    </a>

                    {{-- Sosmed — urutan IG, FB, X, Threads, WA (spec 4.6) --}}
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <a href="{{ setting('url_instagram', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Instagram">
                            <x-icons.instagram width="18" height="18" />
                        </a>
                        <a href="{{ setting('url_facebook', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Facebook">
                            <x-icons.facebook width="18" height="18" />
                        </a>
                        <a href="{{ setting('url_x', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="X">
                            <x-icons.twitter-x width="18" height="18" />
                        </a>
                        <a href="{{ setting('url_threads', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Threads">
                            <x-icons.threads width="18" height="18" />
                        </a>
                        <a href="{{ setting('url_whatsapp', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp">
                            <x-icons.whatsapp width="18" height="18" />
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ setting('url_toco', '#') }}" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            TOCO
                            <x-icons.arrow-right width="12" height="12" />
                        </a>
                        <a href="{{ setting('url_shopee', '#') }}" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            SHOPEE
                            <x-icons.arrow-right width="12" height="12" />
                        </a>
                    </div>
                </div>

                {{-- Kolom 2: Game (dinamis dari $footerGames) --}}
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Game
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                            <x-icons.chevron-up width="14" height="14" />
                        </span>
                    </summary>
                    <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                        @isset($footerGames)
                            @foreach ($footerGames as $game)
                                <li>
                                    <a href="{{ data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                        {{ data_get($game, 'name') }}
                                    </a>
                                </li>
                            @endforeach
                        @endisset
                    </ul>
                </details>

                {{-- Kolom 3: Developer & Jelajahi --}}
                <div class="space-y-8">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Developer
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <x-icons.chevron-up width="14" height="14" />
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            @isset($footerDevelopers)
                                @foreach ($footerDevelopers as $developer)
                                    <li>
                                        <a href="{{ data_get($developer, 'url') ?? (data_get($developer, 'slug') ? route('catalog.developer', data_get($developer, 'slug')) : '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            {{ data_get($developer, 'name') }}
                                        </a>
                                    </li>
                                @endforeach
                            @endisset
                        </ul>
                    </details>

                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Jelajahi
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <x-icons.chevron-up width="14" height="14" />
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            @isset($footerPages)
                                @foreach ($footerPages as $page)
                                    <li>
                                        <a href="{{ data_get($page, 'url') ?? (data_get($page, 'slug') ? url(data_get($page, 'slug')) : '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            {{ data_get($page, 'name') }}
                                        </a>
                                    </li>
                                @endforeach
                            @endisset
                        </ul>
                    </details>
                </div>

                {{-- Kolom 4: Produk Terpopuler (DS 4.4.5) --}}
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Produk Terpopuler
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                            <x-icons.chevron-up width="14" height="14" />
                        </span>
                    </summary>
                    <ul class="mt-3.5 space-y-2 text-[12px]">
                        @isset($footerTopProducts)
                            @foreach ($footerTopProducts as $product)
                                <li class="flex items-center gap-2.5 border-b border-white/10 pb-2">
                                    @if (data_get($product, 'image'))
                                        <img src="{{ data_get($product, 'image') }}" alt="" class="h-12 w-12 shrink-0 rounded object-cover">
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ data_get($product, 'url') ?? '#' }}" class="block truncate text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            {{ data_get($product, 'name') }}
                                        </a>
                                        @if (data_get($product, 'price') !== null)
                                            <span class="text-white">{{ format_rp(data_get($product, 'price')) }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        @endisset
                    </ul>
                </details>
            </div>
        </div>

        {{-- Bottom Copyright Bar (4.4.5) --}}
        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; {{ now()->year }} MERCATORIA</p>
                <div class="flex items-center gap-4">
                    <p>Powered by MERCATORIA</p>
                    <a href="#top" class="inline-flex h-7 w-7 items-center justify-center rounded border border-white/15 text-[#B0B3C5] transition-colors hover:border-white/35 hover:text-white" aria-label="Kembali ke atas">
                        <x-icons.chevron-up width="12" height="12" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
    </body>
</html>
