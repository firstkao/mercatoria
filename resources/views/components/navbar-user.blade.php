@php
    $navUser = auth()->user();
    $cartCount = $navUser->carts()->count();
@endphp
<header class="sticky top-0 z-40 border-b border-border bg-white">
    <div x-data="{ menu: false, mobile: false }" class="mx-auto grid h-16 max-w-7xl grid-cols-[auto_1fr_auto] items-center gap-2 px-4 lg:grid-cols-[1fr_auto_1fr]">
        {{-- Kiri: menu (desktop) / hamburger (mobile) --}}
        <div class="flex items-center">
            <nav class="hidden items-center gap-1 md:flex">
                <x-nav-links />
            </nav>
            <button type="button" class="p-2 text-text-muted md:hidden" @click="mobile = !mobile"
                    :aria-expanded="mobile" aria-label="Menu">
                <span x-show="!mobile">☰</span>
                <span x-show="mobile" x-cloak>✕</span>
            </button>
        </div>

        {{-- Tengah: logo --}}
        <a href="{{ route('home') }}" class="justify-self-center text-xl font-bold text-navy-500">[M] MERCATORIA</a>

        {{-- Kanan --}}
        <div class="flex items-center justify-end gap-0.5">
            <div class="hidden items-center gap-0.5 md:flex"><x-nav-icons /></div>
            <a href="{{ route('cart.index') }}" aria-label="Keranjang" title="Keranjang" class="relative rounded-lg p-2 text-base hover:bg-surface-2">
                🛒
                @if ($cartCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 rounded-full bg-[#E53935] px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>
            <x-notification-bell />

            <div class="relative ml-1" @click.outside="menu = false">
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu akun"
                        class="flex items-center gap-1 rounded-lg p-2 text-sm hover:bg-surface-2">
                    <span aria-hidden="true">👤</span>
                    <span class="hidden items-center gap-1 lg:flex"><x-badge-state :user="$navUser" short /></span>
                    <span class="text-xs text-text-light">▾</span>
                </button>

                <div x-show="menu" x-cloak x-transition
                     class="absolute right-0 mt-2 w-60 rounded-lg border border-border bg-white py-1 shadow-lg">
                    <div class="border-b border-border px-4 py-3">
                        <p class="truncate text-sm font-semibold">{{ $navUser->name }}</p>
                        <div class="mt-2 flex flex-wrap gap-1"><x-badge-state :user="$navUser" /></div>
                    </div>
                    @if ($navUser->isAdmin())
                        <a href="{{ route('office.games.index') }}" class="block px-4 py-2 text-sm hover:bg-surface-2">Office Admin</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-surface-2">Dashboard</a>
                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm hover:bg-surface-2">Pesanan Saya</a>
                    <a href="{{ route('wallet.index') }}" class="block px-4 py-2 text-sm hover:bg-surface-2">Dompet</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-danger-500 hover:bg-danger-50">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Menu mobile --}}
        <div x-show="mobile" x-cloak @click.outside="mobile = false"
             class="absolute left-0 right-0 top-16 border-b border-border bg-white p-4 shadow md:hidden">
            <div class="mb-2 flex flex-wrap items-center gap-1 px-3">
                <p class="mr-1 truncate text-sm font-semibold">{{ $navUser->name }}</p>
                <x-badge-state :user="$navUser" short />
            </div>
            <div class="flex flex-col gap-1">
                <x-nav-links mobile />
                <div class="mt-1 flex items-center gap-1 border-t border-border pt-2"><x-nav-icons /></div>
                @if ($navUser->isAdmin())
                    <a href="{{ route('office.games.index') }}" class="block px-3 py-2 text-sm text-navy-500">Office Admin</a>
                @endif
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-sm text-text-muted">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 text-left text-sm text-danger-500">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>
