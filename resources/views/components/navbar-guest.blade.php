<header class="sticky top-0 z-40 border-b border-border bg-white">
    <div x-data="{ open: false }" class="mx-auto grid h-16 max-w-7xl grid-cols-[auto_1fr_auto] items-center gap-2 px-4 lg:grid-cols-[1fr_auto_1fr]">
        {{-- Kiri: menu (desktop) / hamburger (mobile) --}}
        <div class="flex items-center">
            <nav class="hidden items-center gap-1 md:flex">
                <x-nav-links />
            </nav>
            <button type="button" class="p-2 text-text-muted md:hidden" @click="open = !open"
                    :aria-expanded="open" aria-label="Menu">
                <span x-show="!open">☰</span>
                <span x-show="open" x-cloak>✕</span>
            </button>
        </div>

        {{-- Tengah: logo --}}
        <a href="{{ route('home') }}" class="justify-self-center text-xl font-bold text-navy-500">[M] MERCATORIA</a>

        {{-- Kanan --}}
        <div class="flex items-center justify-end gap-0.5">
            <div class="hidden items-center gap-0.5 md:flex"><x-nav-icons /></div>
            <a href="{{ route('login') }}" aria-label="Masuk" title="Masuk" class="rounded-lg p-2 text-base hover:bg-surface-2">👤</a>
        </div>

        {{-- Menu mobile --}}
        <div x-show="open" x-cloak @click.outside="open = false"
             class="absolute left-0 right-0 top-16 border-b border-border bg-white p-4 shadow md:hidden">
            <div class="flex flex-col gap-1">
                <x-nav-links mobile />
                <div class="mt-2 flex items-center gap-1 border-t border-border pt-2"><x-nav-icons /></div>
                <div class="mt-2 flex flex-col gap-2">
                    <x-button variant="secondary" href="{{ route('login') }}">Masuk</x-button>
                    <x-button variant="auth" href="{{ route('register') }}">Daftar</x-button>
                </div>
            </div>
        </div>
    </div>
</header>
