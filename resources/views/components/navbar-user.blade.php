<header class="sticky top-0 z-40 h-16 border-b border-border bg-white">
    <div x-data="{ menu: false, mobile: false }" class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-navy-500">🌐 Mercatoria</a>

        {{-- Menu tengah (desktop) --}}
        <nav class="hidden items-center gap-2 md:flex">
            <x-nav-links />
        </nav>

        {{-- Aksi kanan (desktop) --}}
        <div class="hidden items-center gap-1 md:flex">
            <a href="{{ route('dashboard') }}"
               class="px-3 py-2 text-sm {{ request()->routeIs('dashboard') ? 'font-semibold text-navy-500' : 'text-text-muted hover:text-text' }}">
                Dashboard
            </a>

            <div class="relative ml-2" @click.outside="menu = false">
                <button type="button" @click="menu = !menu" :aria-expanded="menu"
                        class="flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm hover:bg-surface-2">
                    <span class="max-w-[10rem] truncate">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-text-light">▾</span>
                </button>

                <div x-show="menu" x-cloak x-transition
                     class="absolute right-0 mt-2 w-48 rounded-lg border border-border bg-white py-1 shadow-lg">
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-surface-2">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-danger-500 hover:bg-danger-50">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tombol menu mobile --}}
        <button type="button" class="p-2 text-text-muted md:hidden" @click="mobile = !mobile"
                :aria-expanded="mobile" aria-label="Menu">
            <span x-show="!mobile">☰</span>
            <span x-show="mobile" x-cloak>✕</span>
        </button>

        {{-- Menu mobile --}}
        <div x-show="mobile" x-cloak @click.outside="mobile = false"
             class="absolute left-0 right-0 top-16 border-b border-border bg-white p-4 shadow md:hidden">
            <p class="mb-2 truncate px-3 text-sm font-semibold">{{ auth()->user()->name }}</p>
            <div class="flex flex-col gap-1">
                <x-nav-links mobile />
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-sm text-text-muted">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 text-left text-sm text-danger-500">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>