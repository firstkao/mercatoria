@extends('layouts.app', ['title' => 'Beranda'])

@push('head')
{{-- HERO SLIDER V2 STYLE --}}
<style>
    .home-hero {
        position: relative;
        width: 100%;
        background: #1a1a25;
        margin: 0 0 64px;
        overflow: hidden;
    }
    .home-hero__track {
        display: grid;
        grid-template-areas: "stack";
        width: 100%;
    }
    .home-hero__item {
        grid-area: stack;
        opacity: 0;
        transition: opacity .7s ease-in-out;
        pointer-events: none;
        z-index: 1;
    }
    .home-hero__item.is-active {
        opacity: 1;
        pointer-events: auto;
        z-index: 2;
    }
    .home-hero__item a {
        display: block;
        line-height: 0;
    }
    .home-hero__item img {
        width: 100%;
        height: auto;      /* ← aspect ratio asli, nggak dipotong */
        display: block;
    }
    .home-hero__dots {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 5;
    }
    .home-hero__dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 0;
        background: rgba(255,255,255,.5);
        cursor: pointer;
        padding: 0;
        transition: all .25s;
    }
    .home-hero__dot.is-active {
        background: #fff;
        width: 30px;
        border-radius: 999px;
    }
    .home-hero__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 0;
        background: rgba(255,255,255,.85);
        color: #312e39;
        font-size: 28px;
        line-height: 1;
        padding-bottom: 4px;
        font-family: Georgia, serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all .25s;
        z-index: 5;
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
    }
    .home-hero:hover .home-hero__arrow { opacity: 1; }
    .home-hero__arrow:hover { background: #0299e7; color: #fff; }
    .home-hero__arrow--prev { left: 24px; }
    .home-hero__arrow--next { right: 24px; }
</style>
@endpush

@section('content')
<div class="home">

    {{-- ===================== HERO SLIDER ===================== --}}
    @if ($slides->isNotEmpty())
        @php($isSingle = $slides->count() === 1)

        <section class="home-hero {{ $isSingle ? 'home-hero--single' : '' }}" data-hero-slider data-count="{{ $slides->count() }}">

            <div class="home-hero__track" data-hero-track>
                @foreach ($slides as $index => $slide)
                    <div class="home-hero__item {{ $index === 0 ? 'is-active' : '' }}" data-hero-item="{{ $index }}">
                        <a href="{{ $slide->link_url ?: '#' }}" @if ($slide->link_url) target="_blank" rel="noopener" @endif>
                            @if ($slide->image_path)
                                <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->alt_text ?? $slide->title ?? '' }}">
                            @else
                                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;">
                                    <strong>{{ $slide->title }}</strong>
                                </div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>

            @if (! $isSingle)
                <div class="home-hero__dots" data-hero-dots>
                    @foreach ($slides as $index => $slide)
                        <button type="button"
                                class="home-hero__dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-hero-go="{{ $index }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                <button type="button" class="home-hero__arrow home-hero__arrow--prev" data-hero-prev aria-label="Sebelumnya">‹</button>
                <button type="button" class="home-hero__arrow home-hero__arrow--next" data-hero-next aria-label="Berikutnya">›</button>
            @endif
        </section>
    @endif

    <div class="home__inner">

        {{-- GAME --}}
        @if ($games->isNotEmpty())
            <section class="home-section">
                <h2 class="home-section__title">Jelajahi Game Favoritmu</h2>
                <p class="home-section__sub">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori!</p>
                <div class="game-grid">
                    @foreach ($games as $game)
                        <a href="{{ route('catalog.index', ['game' => $game->slug]) }}" class="game-card">
                            <div class="game-card__image">
                                @if ($game->image_path)
                                    <img src="{{ $game->imageUrl() }}" alt="{{ $game->name }}" loading="lazy">
                                @else
                                    <span class="game-card__placeholder">{{ strtoupper(substr($game->name, 0, 1)) }}</span>
                                @endif
                                <span class="game-card__label">{{ $game->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MERCH BARU --}}
        @if ($latestProducts->isNotEmpty())
            <section class="home-section">
                <h2 class="home-section__title home-section__title--caps">MERCH BARU</h2>
                <div class="product-grid-v2">
                    @foreach ($latestProducts as $product)
                        <a href="{{ route('slug.show', $product->slug) }}" class="product-card-v2">
                            <div class="product-card-v2__image">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <span class="image-placeholder">MERCATORIA</span>
                                @endif
                                @if ($product->tagLabel())
                                    <span class="badge-v2 badge-v2--{{ $product->tag }}">{{ strtoupper($product->tagLabel()) }}</span>
                                @endif
                            </div>
                            <div class="product-card-v2__body">
                                <span class="product-card-v2__name">{{ $product->name }}</span>
                                @php($prices = $product->variants->map(fn ($v) => $v->sellingPrice($calculator))->filter())
                                @if ($prices->isNotEmpty())
                                    <span class="product-card-v2__price">
                                        {{ $prices->min() === $prices->max() ? '' : 'Mulai ' }}{{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- PRODUK TERLARIS --}}
        @if (isset($bestSellerProducts) && $bestSellerProducts->isNotEmpty())
            <section class="home-section">
                <h2 class="home-section__title home-section__title--caps">PRODUK TERLARIS</h2>
                <div class="product-grid-v2">
                    @foreach ($bestSellerProducts as $product)
                        <a href="{{ route('slug.show', $product->slug) }}" class="product-card-v2">
                            <div class="product-card-v2__image">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <span class="image-placeholder">MERCATORIA</span>
                                @endif
                                @if ($product->isBestSeller())
                                    <span class="badge-v2 badge-v2--best">BEST SELLER</span>
                                @endif
                            </div>
                            <div class="product-card-v2__body">
                                <span class="product-card-v2__name">{{ $product->name }}</span>
                                @php($prices = $product->variants->map(fn ($v) => $v->sellingPrice($calculator))->filter())
                                @if ($prices->isNotEmpty())
                                    <span class="product-card-v2__price">
                                        {{ $prices->min() === $prices->max() ? '' : 'Mulai ' }}{{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>

{{-- DEBUG MARKER (lihat di View Source, kalau nggak ada = file lama belum ke-load) --}}
<!-- HERO_SLIDER_V2_LOADED -->
@endsection

@push('scripts')
<script>
(function () {
    var slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;

    var count = parseInt(slider.dataset.count || '0', 10);
    if (count < 2) return;

    var items = slider.querySelectorAll('[data-hero-item]');
    var dots = slider.querySelectorAll('[data-hero-go]');
    var prevBtn = slider.querySelector('[data-hero-prev]');
    var nextBtn = slider.querySelector('[data-hero-next]');
    var total = items.length;
    var current = 0;
    var interval = null;

    // ============================================
    // ⏱️ UBAH DI SINI
    // 60000  = 1 menit
    // 90000  = 1,5 menit
    // 120000 = 2 menit
    // ============================================
    var AUTOPLAY_MS = 5000;

    function goTo(index) {
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        current = index;
        items.forEach(function (item, i) {
            item.classList.toggle('is-active', i === current);
        });
        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === current);
        });
    }
    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }
    function startAuto() {
        stopAuto();
        interval = setInterval(next, AUTOPLAY_MS);
    }
    function stopAuto() {
        if (interval) { clearInterval(interval); interval = null; }
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.heroGo, 10));
            startAuto();
        });
    });
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); startAuto(); });

    // Pause saat mouse di atas slider (opsional)
    // Kalau mau tetap auto walau di-hover, hapus 2 baris ini
    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    if (total > 1) startAuto();
})();
</script>
@endpush