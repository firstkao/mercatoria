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
<body id="top" class="flex min-h-screen flex-col bg-[#F5F7FA] font-sans text-ink antialiased">
    {{-- Top Promo Bar (DS 4.4.1 / PRD 40.1) --}}
    @if (setting('promo_bar_active', '1') !== '0')
        <div id="promo-bar" class="relative bg-[#F2EFE8] px-10 py-2 text-center border-b border-[#D8D2C4]">
            <p class="text-[13px] text-ink">
                Gunakan kode promo <strong class="font-semibold text-danger">NEWWEB</strong> dan nikmati potongan harga sebesar <strong class="font-semibold italic">Rp 25.000</strong> dengan minimal pembelanjaan <strong class="font-semibold italic">Rp 150.000</strong>.
            </p>
        </div>
    @endif

    {{-- Navbar user login (Design Spec 4.6.2) --}}
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto max-w-[1200px] px-4">
            {{-- Desktop 3-kolom --}}
            <div class="hidden min-h-[72px] lg:grid lg:grid-cols-3 lg:items-center">
                <nav class="flex items-center gap-5" aria-label="Menu utama">
                    <a href="{{ route('home') }}" class="text-[13px] font-medium text-[#4FC3F7]">Beranda</a>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Game
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @forelse ($navGames as $navGame)
                                <a href="{{ route('catalog.game', $navGame->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $navGame->name }}@if (!empty($navGame->products_count)) ({{ $navGame->products_count }})@endif</a>
                            @empty
                                <a href="{{ route('coming-soon') }}" class="block px-3.5 py-1.5 text-[12px] text-[#999]">Menyusul</a>
                            @endforelse
                        </div>
                    </details>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Developer
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @forelse ($navDevelopers as $navDeveloper)
                                <a href="{{ route('catalog.developer', $navDeveloper->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $navDeveloper->name }}</a>
                            @empty
                                <a href="{{ route('coming-soon') }}" class="block px-3.5 py-1.5 text-[12px] text-[#999]">Menyusul</a>
                            @endforelse
                        </div>
                    </details>

                    <a href="{{ route('coming-soon') }}" class="text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
                </nav>

                <a href="{{ route('home') }}" class="flex items-center justify-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-16" width="1608" height="494">
                </a>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('search') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Cari">
                        <x-icons.magnifying-glass width="18" height="18" />
                    </a>

                    <div class="flex items-center gap-2.5 text-[#333]">
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

                    @if (!empty($cartTotal))
                        <span class="text-[13px] font-bold text-ink">{{ format_rp($cartTotal) }}</span>
                    @endif
                    <a href="{{ route('cart.index') }}" class="relative text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Keranjang">
                        <x-icons.shopping-cart width="18" height="18" />
                        @if (!empty($cartCount) && $cartCount > 0)
                            <span class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-black px-1 text-[9px] font-semibold text-white">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    <x-notification-bell :notifications="$bellNotifications" :unread-count="$bellUnreadCount ?? 0" />

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Akun">
                        <x-icons.user width="18" height="18" />
                        @if (!empty($stateBadge))
                            <x-user-badge :value="$stateBadge" type="state" />
                        @endif
                        @if (!empty($tierBadge))
                            <span class="inline-flex items-center gap-0.5">
                                <x-user-badge :value="$tierBadge" type="tier" />
                                <span class="text-ink/45 text-[8px]">&#9662;</span>
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Mobile --}}
            <div class="flex min-h-[64px] items-center justify-between gap-3 lg:hidden">
                <details class="relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-[#333]" aria-label="Menu">
                        <x-icons.list width="22" height="22" />
                    </summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-64 rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                        <a href="{{ route('home') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
                        <a href="{{ route('search') }}" class="block px-4 py-2.5 text-[13px] text-[#333]">Cari</a>
                        <a href="{{ route('cart.index') }}" class="block px-4 py-2.5 text-[13px] text-[#333]">Keranjang</a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 text-[13px] text-[#333]">Dashboard</a>
                        <a href="{{ route('coming-soon') }}" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                            @foreach ($navGames as $navGame)
                                <a href="{{ route('catalog.game', $navGame->slug) }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ $navGame->name }}@if (!empty($navGame->products_count)) ({{ $navGame->products_count }})@endif</a>
                            @endforeach
                        </div>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                            @foreach ($navDevelopers as $navDeveloper)
                                <a href="{{ route('catalog.developer', $navDeveloper->slug) }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ $navDeveloper->name }}</a>
                            @endforeach
                        </div>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2.5 text-left text-[13px] text-[#333]">Keluar</button>
                            </form>
                        </div>
                    </div>
                </details>

                <a href="{{ route('home') }}" class="flex items-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto" width="1608" height="494">
                </a>

                <a href="{{ route('cart.index') }}" class="relative flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Keranjang">
                    <x-icons.shopping-cart width="20" height="20" />
                    @if (!empty($cartCount) && $cartCount > 0)
                        <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-black px-1 text-[9px] font-semibold text-white">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="mx-auto w-full max-w-[1200px] px-4 pt-4">
            <div class="rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="mx-auto w-full max-w-[1200px] flex-1 px-4 py-8">
        @yield('content')
    </main>

    {{-- Footer (sama dengan guest — Design Spec 4.4.5) --}}
    <footer class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col items-center">
                    <a href="{{ route('home') }}" class="inline-flex flex-col items-center no-underline">
                        <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-14" width="1608" height="494">
                    </a>

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

                {{-- Kolom 4: Produk Terpopuler (data BE menyusul) --}}
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

        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; {{ now()->year }} MERCATORIA</p>
                <div class="flex items-center gap-4">
                    <p>Powered by MERCATORIA</p>
                </div>
            </div>
        </div>
    </footer>

    {{-- Back-to-top floating (muncul saat scroll > 400px) --}}
    <button id="back-to-top" type="button"
            class="fixed bottom-6 right-4 z-40 hidden h-11 w-11 items-center justify-center rounded-full border border-rule bg-white text-ink shadow-sm transition-colors hover:border-ink sm:right-6"
            aria-label="Kembali ke atas">
        <x-icons.chevron-up width="16" height="16" />
    </button>

    <script>
        (function () {
            var btn = document.getElementById('back-to-top');
            if (!btn) return;
            function onScroll() {
                if (window.scrollY > 400) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                } else {
                    btn.classList.add('hidden');
                    btn.classList.remove('flex');
                }
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            btn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            onScroll();
        })();

        @if (\Illuminate\Support\Facades\Route::has('profile.notifications.unread-count'))
        (function () {
            var badge = document.querySelector('[data-unread]');
            if (!badge) return;
            setInterval(function () {
                fetch('{{ route("profile.notifications.unread-count") }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        var n = d.count ?? d.unread ?? 0;
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.style.display = n > 0 ? '' : 'none';
                    })
                    .catch(function () {});
            }, 60000);
        })();
        @endif
    </script>
</body>
</html>
