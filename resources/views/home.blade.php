@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@if (session('blocked_spammer_ip'))
    <div role="alert" class="mx-auto mt-4 max-w-7xl rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700">
        Akses dari alamat jaringan ini dibatasi. Halaman beranda tetap dapat diakses.
    </div>
@endif

<div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 lg:px-8">
    @if ($heroSlides->isNotEmpty())
        <section id="hero-carousel" class="group relative mb-6 mt-6 overflow-hidden bg-surface-2" aria-label="Promo pilihan Mercatoria">
            <div class="relative aspect-video md:aspect-[2/1]">
                @foreach ($heroSlides as $index => $slide)
                    <div data-hero-slide class="absolute inset-0 {{ $index === 0 ? '' : 'hidden' }}">
                        @php($slideImage = Storage::disk('public')->url($slide->image_path))
                        @if ($slide->link_url)
                            <a href="{{ $slide->link_url }}" class="block h-full">
                                <img src="{{ $slideImage }}" alt="{{ $slide->title ?: 'Promo Mercatoria' }}" class="h-full w-full object-cover" @if ($index > 0) loading="lazy" @endif>
                            </a>
                        @else
                            <img src="{{ $slideImage }}" alt="{{ $slide->title ?: 'Promo Mercatoria' }}" class="h-full w-full object-cover" @if ($index > 0) loading="lazy" @endif>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($heroSlides->count() > 1)
                <button type="button" data-hero-prev aria-label="Slide sebelumnya"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/50 px-3 py-1 text-2xl text-black opacity-70 transition hover:bg-white/80 group-hover:opacity-100">‹</button>
                <button type="button" data-hero-next aria-label="Slide berikutnya"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/50 px-3 py-1 text-2xl text-black opacity-70 transition hover:bg-white/80 group-hover:opacity-100">›</button>
                <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
                    @foreach ($heroSlides as $index => $slide)
                        <button type="button" data-hero-dot="{{ $index }}" aria-label="Ke slide {{ $index + 1 }}"
                            @class(['h-2.5 w-2.5 rounded-full border border-white', 'bg-white' => $index === 0, 'bg-white/40' => $index !== 0])></button>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <div class="h-6"></div>
    @endif
    <hr class="border-t border-[#E0E0E0]">

    <section id="game-favorit" class="py-8" aria-labelledby="game-favorit-title">
        <h2 id="game-favorit-title" class="text-center text-[26px] font-bold text-[#111]">Jelajahi Game Favoritmu</h2>
        <p class="mb-6 mt-2 text-center text-[13px] text-text-muted">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori</p>
        @if ($games->isEmpty())
            <p class="text-center text-sm text-text-muted">Belum ada game aktif.</p>
        @else
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($games as $game)
                    <a href="{{ route('games.show', $game->slug) }}" data-game-card class="group relative block aspect-square overflow-hidden bg-surface-2">
                        @if ($game->logo_path)
                            <img src="{{ Storage::disk('public')->url($game->logo_path) }}" alt="{{ $game->name }}" loading="lazy"
                                class="h-full w-full object-cover transition duration-200 group-hover:scale-[1.03]">
                        @endif
                        <span class="absolute bottom-3 left-1/2 w-[82%] -translate-x-1/2 bg-white/90 px-2 py-1.5 text-center text-[10.5px] font-bold uppercase leading-tight text-[#111]">{{ $game->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($bestOffers->isNotEmpty())
        <section id="penawaran-terbaik" class="pb-8" aria-labelledby="penawaran-title">
            <h2 id="penawaran-title" class="mb-5 text-center text-lg font-bold uppercase text-[#212121]">Penawaran Terbaik</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                @foreach ($bestOffers as $offer)
                    <x-product-card :product="$offer" :discount="true" />
                @endforeach
            </div>
        </section>
    @endif

    <section id="merch-baru" class="pb-12 pt-2" aria-labelledby="merch-baru-title">
        <h2 id="merch-baru-title" class="mb-5 mt-10 text-center text-lg font-bold uppercase text-[#212121]">Merch Baru</h2>
        @if ($newProducts->isEmpty())
            <p class="text-center text-sm text-text-muted">Belum ada produk.</p>
        @else
            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-5 lg:grid-cols-7">
                @foreach ($newProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const carousel = document.getElementById('hero-carousel');
            if (!carousel) return;
            const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
            const dots = [...carousel.querySelectorAll('[data-hero-dot]')];
            let index = 0;
            let interval;
            const show = next => {
                index = (next + slides.length) % slides.length;
                slides.forEach((slide, position) => slide.classList.toggle('hidden', position !== index));
                dots.forEach((dot, position) => {
                    dot.classList.toggle('bg-white', position === index);
                    dot.classList.toggle('bg-white/40', position !== index);
                });
            };
            const start = () => {
                window.clearInterval(interval);
                interval = window.setInterval(() => show(index + 1), 5000);
            };
            carousel.querySelector('[data-hero-prev]')?.addEventListener('click', () => { show(index - 1); start(); });
            carousel.querySelector('[data-hero-next]')?.addEventListener('click', () => { show(index + 1); start(); });
            dots.forEach(dot => dot.addEventListener('click', () => { show(Number(dot.dataset.heroDot)); start(); }));
            carousel.addEventListener('mouseenter', () => window.clearInterval(interval));
            carousel.addEventListener('mouseleave', start);
            let touchStart = 0;
            carousel.addEventListener('touchstart', event => { touchStart = event.changedTouches[0].clientX; }, { passive: true });
            carousel.addEventListener('touchend', event => {
                const distance = event.changedTouches[0].clientX - touchStart;
                if (Math.abs(distance) > 40) { show(index + (distance < 0 ? 1 : -1)); start(); }
            }, { passive: true });
            if (slides.length > 1) start();
        })();
        if (typeof window.mercatoriaTrack === 'function') {
            window.mercatoriaTrack('view_home');
        }
    </script>
@endpush

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Mercatoria',
            'url' => route('home'),
            'logo' => setting('seo_default_og_image')
                ? url(setting('seo_default_og_image'))
                : null,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush
