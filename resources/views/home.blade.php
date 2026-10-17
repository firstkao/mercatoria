@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@if (session('blocked_spammer_ip'))
    <div role="alert" class="mx-auto mt-4 max-w-7xl rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700">
        Akses dari alamat jaringan ini dibatasi. Halaman beranda tetap dapat diakses.
    </div>
@endif
@if ($heroSlides->isNotEmpty())
    <section id="hero-carousel" class="relative w-full overflow-hidden bg-surface-2" aria-label="Promo pilihan Mercatoria">
        <div class="relative aspect-[2/1] max-h-[560px] min-h-48 md:aspect-[3/1]">
            @foreach ($heroSlides as $index => $slide)
                <div data-hero-slide class="absolute inset-0 {{ $index === 0 ? '' : 'hidden' }}">
                    @if ($slide->link_url)
                        <a href="{{ $slide->link_url }}" class="block h-full">
                            <img src="{{ Storage::disk('public')->url($slide->image_path) }}" alt="{{ $slide->title ?: 'Promo Mercatoria' }}"
                                class="h-full w-full object-cover" @if ($index > 0) loading="lazy" @endif>
                        </a>
                    @else
                        <img src="{{ Storage::disk('public')->url($slide->image_path) }}" alt="{{ $slide->title ?: 'Promo Mercatoria' }}"
                            class="h-full w-full object-cover" @if ($index > 0) loading="lazy" @endif>
                    @endif
                </div>
            @endforeach
        </div>
        @if ($heroSlides->count() > 1)
            <button type="button" data-hero-prev aria-label="Slide sebelumnya"
                class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 px-3 py-2 text-white hover:bg-black/70">‹</button>
            <button type="button" data-hero-next aria-label="Slide berikutnya"
                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 px-3 py-2 text-white hover:bg-black/70">›</button>
            <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                @foreach ($heroSlides as $index => $slide)
                    <button type="button" data-hero-dot="{{ $index }}" aria-label="Ke slide {{ $index + 1 }}"
                        @class(['h-2.5 w-2.5 rounded-full border border-white', 'bg-white' => $index === 0, 'bg-white/40' => $index !== 0])></button>
                @endforeach
            </div>
        @endif
    </section>
@endif
<section class="bg-surface-2">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:grid-cols-2 md:items-center md:py-24">
        <div>
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-navy-500">
                Merchandise IP Cina, lebih mudah
            </p>
            <h1 class="max-w-2xl text-4xl font-bold leading-tight text-text md:text-5xl">
                Koleksi merch favoritmu. Urusan belinya, biar kami bantu.
            </h1>
            <p class="mt-5 max-w-xl text-lg text-text-muted">
                Mercatoria membantu penggemar di Indonesia menemukan dan membeli merchandise game, anime, donghua, dan novel dari Tiongkok.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button variant="ghost" href="{{ route('catalog.index') }}">Jelajahi katalog</x-button>
                <x-button href="{{ route('register') }}">Mulai belanja</x-button>
                <x-button variant="ghost" href="{{ route('login') }}">Masuk ke akun</x-button>
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-white p-6 shadow-sm md:p-8">
            <h2 class="text-xl font-semibold text-navy-500">Belanja lintas negara, tanpa ribet</h2>
            <ul class="mt-5 space-y-4 text-sm text-text-muted">
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Katalog terkurasi untuk menemukan merchandise berdasarkan IP favorit.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Pembayaran lokal dan informasi harga dalam Rupiah.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Proses pembelian dan pengiriman dibantu oleh Mercatoria.</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 text-center">
    <h2 class="text-2xl font-bold text-text">Mulai dari fandom favoritmu</h2>
    <p class="mx-auto mt-3 max-w-2xl text-text-muted">
        Buat akun untuk mengikuti perjalanan pesanan dan mendapat kabar seputar koleksi terbaru.
    </p>
    <x-button href="{{ route('register') }}" class="mt-6">Buat akun Mercatoria</x-button>
</section>
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
            start();
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
