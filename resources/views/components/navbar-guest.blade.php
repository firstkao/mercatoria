<header class="sticky top-0 z-40 h-16 border-b border-border bg-white">
    <div x-data="{ open: false }" class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-navy-500">🌐 Mercatoria</a>

        {{-- Menu tengah (desktop) --}}
        <nav class="hidden items-center gap-2 md:flex">
            <x-nav-links />
        </nav>

        {{-- Aksi kanan (desktop) --}}
        <div class="hidden items-center gap-2 md:flex">
            <x-button variant="ghost" href="{{ route('login') }}">Masuk</x-button>
            <x-button href="{{ route('register') }}">Daftar</x-button>
        </div>

        {{-- Tombol menu mobile --}}
        <button type="button" class="p-2 text-text-muted md:hidden" @click="open = !open"
                :aria-expanded="open" aria-label="Menu">
            <span x-show="!open">☰</span>
            <span x-show="open" x-cloak>✕</span>
        </button>

        {{-- Menu mobile --}}
        <div x-show="open" x-cloak @click.outside="open = false"
             class="absolute left-0 right-0 top-16 border-b border-border bg-white p-4 shadow md:hidden">
            <div class="flex flex-col gap-1">
                <x-nav-links mobile />
                <div class="mt-2 flex flex-col gap-2">
                    <x-button variant="ghost" href="{{ route('login') }}">Masuk</x-button>
                    <x-button href="{{ route('register') }}">Daftar</x-button>
                </div>
            </div>
        </div>
    </div>
</header>