<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-head :seo="$seo" :json-ld="$jsonLd" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-ga4 />
</head>
<body id="top" class="flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    @include('frontend.partials._header')

    <main class="flex-1">
        {{-- Hero Carousel (PRD 40.2) — foto saja, tanpa teks overlay --}}
        <section id="hero" class="mx-auto max-w-[1200px] px-8 py-6">
            @if (count($heroSlides ?? []) > 0)
                <div class="relative overflow-hidden rounded-lg border border-rule" id="hero-carousel">
                    <div class="relative aspect-[13/8] bg-ink" id="hero-track">
                        @foreach ($heroSlides as $i => $slide)
                            <a href="{{ data_get($slide, 'url') ?? '#' }}" class="absolute inset-0 {{ $i === 0 ? '' : 'hidden' }}" data-hero-slide="{{ $i }}">
                                @if (data_get($slide, 'image'))
                                    <img src="{{ data_get($slide, 'image') }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <button type="button" id="hero-prev" class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Sebelumnya">
                        <x-icons.chevron-up class="rotate-90" width="16" height="16" />
                    </button>
                    <button type="button" id="hero-next" class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Berikutnya">
                        <x-icons.chevron-up class="-rotate-90" width="16" height="16" />
                    </button>

                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2" id="hero-dots">
                        @foreach ($heroSlides as $i => $slide)
                            <button type="button" data-hero-dot="{{ $i }}" class="h-2 w-2 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/45' }}" aria-label="Slide {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="mt-3 h-px bg-rule"></div>
        </section>

        {{-- Jelajahi Game Favoritmu (PRD 40.4 / Design Spec 4.4.3) --}}
        <section id="game-grid" class="mx-auto max-w-[1200px] px-8 pb-12">
            <h2 class="text-center font-display text-[50px] font-semibold text-ink">Jelajahi Game Favoritmu</h2>
            <p class="mt-2 text-center text-[16px] text-ink/60">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori!</p>

            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                @forelse (($games ?? collect()) as $game)
                    <a href="{{ route('catalog.game', $game->slug) }}" class="group relative block aspect-square overflow-hidden rounded-lg bg-paper">
                        @if ($game->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-[#F5F7FA]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">{{ $game->name }}</span>
                            </div>
                        @endif
                        <div class="absolute bottom-2 left-1/2 w-[82%] -translate-x-1/2 rounded bg-white/92 px-2 py-1.5 text-center">
                            <p class="text-[13px] font-bold uppercase leading-tight text-ink">{{ $game->name }}</p>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-center text-sm text-ink/45">Game akan segera hadir.</p>
                @endforelse
            </div>
        </section>

        {{-- MERCH BARU (PRD 40.6 / Design Spec 4.4.4) --}}
        <section id="merch-baru" class="mx-auto max-w-[1200px] px-8 pb-14">
            <h2 class="text-center text-[28px] font-bold uppercase tracking-wide text-ink">MERCH BARU</h2>

            <div class="mt-5 grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7">
                @forelse (($products ?? collect()) as $product)
                    <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="group block">
                        <div class="relative aspect-[5/4] overflow-hidden rounded bg-paper">
                            @if ($product->image_path)
                                <img src="{{ \App\Services\Image\ImageOptimizerService::url($product->image_path, 'thumb') }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                            @endif
                            @if ($product->tags->contains('slug', 'presale'))
                                <span class="absolute left-2 top-2 rounded bg-[#3986C6] px-2 py-[3px] text-[9px] font-bold uppercase text-white">PRESALE</span>
                            @elseif ($product->tags->contains('slug', 'limited'))
                                <span class="absolute left-2 top-2 rounded bg-[#E53935] px-2 py-[3px] text-[9px] font-bold uppercase text-white">LIMITED</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-[15px] font-bold text-ink">{{ $product->name }}</p>
                        @if (!empty($product->price_min) && !empty($product->price_max))
                            <p class="mt-0.5 text-[15px]">
                                <span class="text-[13px] text-ink/45 line-through">{{ format_rp($product->price_min) }}</span>
                                <span class="text-ink/45"> – </span>
                                <strong class="text-ink">{{ format_rp($product->price_max) }}</strong>
                            </p>
                        @elseif (!empty($product->price_idr))
                            <p class="mt-0.5 text-[15px] font-bold text-ink">{{ format_rp($product->price_idr) }}</p>
                        @endif
                    </a>
                @empty
                    <p class="col-span-full text-center text-sm text-ink/45">Belum ada merch.</p>
                @endforelse
            </div>
        </section>
    </main>

    @include('frontend.partials._footer')

    <script>
        (function () {
            var carousel = document.getElementById('hero-carousel');
            if (!carousel) return;
            var slides = carousel.querySelectorAll('[data-hero-slide]');
            var dots = carousel.querySelectorAll('[data-hero-dot]');
            if (slides.length < 2) return;
            var index = 0;
            var timer = null;

            function show(i) {
                index = (i + slides.length) % slides.length;
                slides.forEach(function (s, n) { s.classList.toggle('hidden', n !== index); });
                dots.forEach(function (d, n) {
                    d.classList.toggle('bg-white', n === index);
                    d.classList.toggle('bg-white/45', n !== index);
                });
            }
            function next() { show(index + 1); }
            function prev() { show(index - 1); }
            function start() { stop(); timer = setInterval(next, 5000); }
            function stop() { if (timer) clearInterval(timer); timer = null; }

            var prevBtn = document.getElementById('hero-prev');
            var nextBtn = document.getElementById('hero-next');
            if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
            dots.forEach(function (d) {
                d.addEventListener('click', function () {
                    show(parseInt(d.getAttribute('data-hero-dot'), 10) || 0);
                    start();
                });
            });

            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);

            var touchX = null;
            carousel.addEventListener('touchstart', function (e) {
                touchX = e.changedTouches[0].clientX;
                stop();
            }, { passive: true });
            carousel.addEventListener('touchend', function (e) {
                if (touchX === null) return;
                var dx = e.changedTouches[0].clientX - touchX;
                if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); }
                touchX = null;
                start();
            }, { passive: true });

            show(0);
            start();
        })();
    </script>
</body>
</html>
