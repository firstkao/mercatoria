<div class="mx-auto flex min-h-[72px] w-full flex-wrap items-center justify-between gap-3 px-6 lg:grid lg:grid-cols-3 lg:px-12">
    <nav class="flex items-center gap-5" aria-label="Menu utama">
        <a href="{{ route('home') }}" class="text-[13px] font-medium text-[#4FC3F7]">Beranda</a>

        {{-- Dropdown Game --}}
        <details class="group relative">
            <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                Game
                <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
            </summary>
            <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                @forelse (($navGames ?? collect()) as $g)
                    <a href="{{ route('catalog.game', $g->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $g->name }}</a>
                @empty
                    <a href="{{ route('coming-soon') }}" class="block px-3.5 py-1.5 text-[12px] text-[#999]">Menyusul</a>
                @endforelse
            </div>
        </details>

        {{-- Dropdown Developer --}}
        <details class="group relative">
            <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                Developer
                <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
            </summary>
            <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                @forelse (($navDevelopers ?? collect()) as $dev)
                    <a href="{{ route('catalog.developer', $dev->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $dev->name }}</a>
                @empty
                    <a href="{{ route('coming-soon') }}" class="block px-3.5 py-1.5 text-[12px] text-[#999]">Menyusul</a>
                @endforelse
            </div>
        </details>

        <a href="#merch-baru" class="text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
    </nav>

    <a href="{{ route('home') }}" class="flex items-center justify-center no-underline">
        <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-16" width="1608" height="494">
    </a>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('search') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Cari">
            <x-icons.magnifying-glass width="18" height="18" />
        </a>
        <div class="flex items-center gap-2.5 text-[#333]">
            <a href="{{ setting('url_instagram', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Instagram"><x-icons.instagram width="16" height="16" /></a>
            <a href="{{ setting('url_facebook', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Facebook"><x-icons.facebook width="16" height="16" /></a>
            <a href="{{ setting('url_x', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="X"><x-icons.twitter-x width="16" height="16" /></a>
            <a href="{{ setting('url_threads', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Threads"><x-icons.threads width="16" height="16" /></a>
            <a href="{{ setting('url_whatsapp', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp"><x-icons.whatsapp width="16" height="16" /></a>
        </div>
        @auth
            <span data-cart-total class="text-[13px] font-bold text-ink" @if (empty($cartTotal)) style="display:none" @endif>{{ format_rp($cartTotal ?? 0) }}</span>
            <a href="{{ route('cart.index') }}" class="relative text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Keranjang">
                <x-icons.shopping-cart width="18" height="18" />
                <span data-cart-count class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#333] px-1 text-[9px] font-bold leading-none text-white" @if (($cartCount ?? 0) < 1) style="display:none" @endif>{{ $cartCount ?? 0 }}</span>
            </a>
            <x-notification-bell :notifications="$bellNotifications ?? []" :unread-count="$bellUnreadCount ?? 0" />
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Akun"><x-icons.user width="18" height="18" /></a>
        @else
            <a href="{{ route('login') }}" class="rounded-[20px] border border-[#333] px-4 py-1.5 text-[12px] font-semibold text-[#333] transition-colors hover:border-[#4FC3F7] hover:text-[#4FC3F7]">Login</a>
            <a href="{{ route('register') }}" class="rounded-[20px] bg-[#4FC3F7] px-4 py-1.5 text-[12px] font-semibold text-white transition-opacity hover:opacity-90">Daftar</a>
        @endauth
    </div>
</div>

{{-- Mobile --}}
<div class="mx-auto flex min-h-[64px] w-full items-center justify-between gap-3 px-6 lg:hidden">
    <details class="relative">
        <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-[#333]" aria-label="Menu">
            <x-icons.list width="22" height="22" />
        </summary>
        <div class="absolute left-0 top-full z-20 mt-2 w-64 rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
            <a href="{{ route('home') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
            <a href="#merch-baru" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>

            <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                @foreach (($navGames ?? collect()) as $g)
                    <a href="{{ route('catalog.game', $g->slug) }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ $g->name }}</a>
                @endforeach
            </div>

            <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                @foreach (($navDevelopers ?? collect()) as $dev)
                    <a href="{{ route('catalog.developer', $dev->slug) }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ $dev->name }}</a>
                @endforeach
            </div>

            <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#333]">Akun Saya</a>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#333]">Login</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Daftar</a>
                @endauth
            </div>
        </div>
    </details>

    <a href="{{ route('home') }}" class="flex items-center no-underline">
        <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto" width="1608" height="494">
    </a>

    <div class="flex items-center gap-2">
        @auth
            <a href="{{ route('cart.index') }}" class="relative flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Keranjang">
                <x-icons.shopping-cart width="20" height="20" />
                <span data-cart-count class="absolute right-0 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#333] px-1 text-[9px] font-bold leading-none text-white" @if (($cartCount ?? 0) < 1) style="display:none" @endif>{{ $cartCount ?? 0 }}</span>
            </a>
            <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Akun">
                <x-icons.user width="20" height="20" />
            </a>
        @else
            <a href="{{ route('login') }}" class="rounded-[20px] border border-[#333] px-3 py-1.5 text-[12px] font-semibold text-[#333]">Login</a>
            <a href="{{ route('register') }}" class="rounded-[20px] bg-[#4FC3F7] px-3 py-1.5 text-[12px] font-semibold text-white">Daftar</a>
        @endauth
    </div>
</div>
