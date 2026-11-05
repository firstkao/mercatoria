<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-head :seo="$seo" :json-ld="$jsonLd" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-ga4 />
    <style>
        /* Homepage — CSS biasa (tidak bergantung build Tailwind). Ukuran dari screenshot 1920px. */
        .hp, .hp *, .hp *::before, .hp *::after { box-sizing: border-box; }
        .hp { width: 100%; max-width: 1172px; margin: 0 auto; padding: 0 16px; color: #000; font-family: 'Roboto', system-ui, sans-serif; }
        .hp a { color: inherit; text-decoration: none; }
        .hp img { display: block; max-width: 100%; }

        /* Hero 1140×650 */
        .hp-hero { padding-top: 10px; }
        .hp-hero-box { position: relative; overflow: hidden; }
        .hp-hero-track { position: relative; aspect-ratio: 1140 / 650; background: #1f2230; }
        .hp-slide { position: absolute; inset: 0; display: none; }
        .hp-slide.is-on { display: block; }
        .hp-slide img { width: 100%; height: 100%; object-fit: cover; }
        .hp-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: none; border: 0; padding: 4px; cursor: pointer; color: rgba(255,255,255,.6); line-height: 0; }
        .hp-arrow:hover { color: #fff; }
        .hp-arrow.prev { left: 14px; } .hp-arrow.next { right: 14px; }
        .hp-dots { position: absolute; left: 50%; bottom: 14px; transform: translateX(-50%); display: flex; gap: 6px; }
        .hp-dots button { width: 6px; height: 6px; padding: 0; border: 0; border-radius: 50%; background: rgba(255,255,255,.45); cursor: pointer; }
        .hp-dots button.is-on { background: #fff; }
        .hp-line { height: 1px; margin-top: 35px; background: #000; }

        /* Judul */
        .hp-h2 { margin: 52px 0 0; text-align: center; font-size: 50px; line-height: 60px; font-weight: 700; color: #000; }
        .hp-sub { margin: 23px 0 0; text-align: center; font-size: 15px; line-height: 22px; font-weight: 700; color: #597480; }

        /* Grid game: 6 kolom × 173px */
        .hp-games { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); column-gap: 20px; row-gap: 44.7px; margin-top: 27px; }
        .hp-game { display: block; background: #fff; padding-bottom: 15px; }
        .hp-game-img { position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: linear-gradient(160deg, #e4e8ef 0%, #cfd5df 100%); }
        .hp-game-img > img { width: 100%; height: 100%; object-fit: cover; }
        .hp-game-ph { display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; padding-bottom: 36px; font-size: 64px; line-height: 1; font-weight: 700; color: rgba(255,255,255,.7); user-select: none; }
        .hp-game-label { position: absolute; left: 50%; bottom: 10px; width: 84%; transform: translateX(-50%); padding: 7px 4px; text-align: center; background: rgba(255,255,255,.9); }
        .hp-game-label h3 { margin: 0; font-size: 13px; line-height: 22px; font-weight: 700; text-transform: uppercase; color: #222; }

        /* Merch baru: 7 kolom × 146px */
        .hp-merch-h { margin: 66px 0 0; text-align: center; font-size: 32px; line-height: 38px; font-weight: 500; text-transform: uppercase; color: #312e39; }
        .hp-merch { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); column-gap: 20px; row-gap: 50px; margin-top: 23px; padding-bottom: 58px; }
        .hp-card { display: block; }
        .hp-card-img { position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: #fff; }
        .hp-card-img > img { width: 100%; height: 100%; object-fit: cover; }
        .hp-name { margin: 10px 0 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #000; }
        .hp-price { margin: 8px 0 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #02a1ea; }
        .hp-price s { color: rgba(0,0,0,.4); }
        .hp-price i { font-style: normal; color: rgba(0,0,0,.4); }
        .hp-empty { grid-column: 1 / -1; text-align: center; font-size: 14px; color: rgba(0,0,0,.45); }

        /* Ribbon LIMITED / PRESALE */
        .hp-flag { position: absolute; top: 0; left: 0; z-index: 9; height: 21px; padding: 0 15px 0 9px; font-size: 15px; line-height: 21px; font-weight: 500; text-transform: uppercase; color: #fff;
                   clip-path: polygon(0 0, 100% 0, calc(100% - 9px) 100%, 0 100%); }
        .hp-flag.is-limited { background: #FF4949; }
        .hp-flag.is-presale { background: #3986C6; }

        @media (max-width: 1100px) { .hp-merch { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (max-width: 900px)  { .hp-games { grid-template-columns: repeat(3, minmax(0, 1fr)); } .hp-merch { grid-template-columns: repeat(3, minmax(0, 1fr)); } .hp-h2 { font-size: 36px; line-height: 44px; } }
        @media (max-width: 560px)  { .hp-games, .hp-merch { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hp-h2 { font-size: 28px; line-height: 36px; margin-top: 32px; } .hp-sub { font-size: 13px; } .hp-merch-h { font-size: 24px; margin-top: 40px; } }
    </style>
</head>
<body id="top" class="flex min-h-screen flex-col font-sans text-ink antialiased" style="background:#f7f7f7;font-family:'Roboto',system-ui,-apple-system,'Segoe UI',sans-serif">
    @include('frontend.partials._header')

    <main style="flex:1 1 auto">
        <div class="hp">

            {{-- ═══ HERO ═══ --}}
            <section id="hero" class="hp-hero">
                @if (count($heroSlides ?? []) > 0)
                    <div class="hp-hero-box" id="hero-carousel">
                        <div class="hp-hero-track" id="hero-track">
                            @foreach ($heroSlides as $i => $slide)
                                <a href="{{ data_get($slide, 'url') ?? '#' }}" class="hp-slide {{ $i === 0 ? 'is-on' : '' }}" data-hero-slide="{{ $i }}">
                                    @if (data_get($slide, 'image'))
                                        <img src="{{ data_get($slide, 'image') }}" alt="">
                                    @endif
                                </a>
                            @endforeach
                        </div>

                        <button type="button" id="hero-prev" class="hp-arrow prev" aria-label="Sebelumnya">
                            <svg width="22" height="22" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M10 3 5 8l5 5"/></svg>
                        </button>
                        <button type="button" id="hero-next" class="hp-arrow next" aria-label="Berikutnya">
                            <svg width="22" height="22" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="m6 3 5 5-5 5"/></svg>
                        </button>

                        <div class="hp-dots" id="hero-dots">
                            @foreach ($heroSlides as $i => $slide)
                                <button type="button" data-hero-dot="{{ $i }}" class="{{ $i === 0 ? 'is-on' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="hp-line"></div>
            </section>

            {{-- ═══ JELAJAHI GAME FAVORITMU ═══ --}}
            <section id="game-grid">
                <h2 class="hp-h2">Jelajahi Game Favoritmu</h2>
                <p class="hp-sub">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori!</p>

                <div class="hp-games">
                    @forelse (($games ?? collect()) as $game)
                        <a href="{{ route('catalog.game', $game->slug) }}" class="hp-game">
                            <div class="hp-game-img">
                                @if ($game->logo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}">
                                @else
                                    <div class="hp-game-ph" aria-hidden="true">{{ mb_strtoupper(mb_substr($game->name, 0, 1)) }}</div>
                                @endif
                                <div class="hp-game-label"><h3>{{ $game->name }}</h3></div>
                            </div>
                        </a>
                    @empty
                        <p class="hp-empty">Game akan segera hadir.</p>
                    @endforelse
                </div>
            </section>

            {{-- ═══ MERCH BARU ═══ --}}
            <section id="merch-baru">
                <h2 class="hp-merch-h">MERCH BARU</h2>

                <div class="hp-merch">
                    @forelse (($products ?? collect()) as $product)
                        <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="hp-card">
                            <div class="hp-card-img">
                                @if ($product->image_path)
                                    <img src="{{ \App\Services\Image\ImageOptimizerService::url($product->image_path, 'thumb') }}" alt="{{ $product->name }}">
                                @endif
                                @if ($product->tags->contains('slug', 'presale'))
                                    <span class="hp-flag is-presale">PRESALE</span>
                                @elseif ($product->tags->contains('slug', 'limited'))
                                    <span class="hp-flag is-limited">LIMITED</span>
                                @endif
                            </div>

                            <p class="hp-name">{{ $product->name }}</p>

                            @if (!empty($product->price_min) && !empty($product->price_max) && $product->price_min != $product->price_max)
                                <p class="hp-price"><s>{{ format_rp($product->price_min) }}</s> <i>–</i> {{ format_rp($product->price_max) }}</p>
                            @elseif (!empty($product->price_idr))
                                <p class="hp-price">{{ format_rp($product->price_idr) }}</p>
                            @elseif (!empty($product->price_max))
                                <p class="hp-price">{{ format_rp($product->price_max) }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="hp-empty">Belum ada merch.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>

    @include('frontend.partials._footer')

    <script>
        (function () {
            var carousel = document.getElementById('hero-carousel');
            if (!carousel) return;
            var slides = carousel.querySelectorAll('[data-hero-slide]');
            var dots = carousel.querySelectorAll('[data-hero-dot]');
            if (slides.length < 2) return;
            var index = 0, timer = null;

            function show(i) {
                index = (i + slides.length) % slides.length;
                slides.forEach(function (s, n) { s.classList.toggle('is-on', n === index); });
                dots.forEach(function (d, n) { d.classList.toggle('is-on', n === index); });
            }
            function next() { show(index + 1); }
            function prev() { show(index - 1); }
            function stop() { if (timer) clearInterval(timer); timer = null; }
            function start() { stop(); timer = setInterval(next, 5000); }

            var prevBtn = document.getElementById('hero-prev');
            var nextBtn = document.getElementById('hero-next');
            if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
            dots.forEach(function (d) {
                d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-hero-dot'), 10) || 0); start(); });
            });

            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);

            var touchX = null;
            carousel.addEventListener('touchstart', function (e) { touchX = e.changedTouches[0].clientX; stop(); }, { passive: true });
            carousel.addEventListener('touchend', function (e) {
                if (touchX === null) return;
                var dx = e.changedTouches[0].clientX - touchX;
                if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); }
                touchX = null; start();
            }, { passive: true });

            show(0); start();
        })();
    </script>
</body>
</html>