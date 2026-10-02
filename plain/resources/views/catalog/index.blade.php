@extends('layouts.app')
@section('main_class', 'main--reading')
@section('content')
<div class="catalog-new">
    {{-- Breadcrumb --}}
    <nav class="catalog-new__breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span>»</span>
        <a href="{{ route('catalog.index') }}">Produk</a>
        @if ($selectedGame)
            <span>»</span>
            <span>{{ $selectedGame->name }}</span>
        @elseif ($selectedDeveloper)
            <span>»</span>
            <span>{{ $selectedDeveloper->name }}</span>
        @endif
    </nav>

    <div class="catalog-new__layout">
        {{-- Sidebar Kiri --}}
        <aside class="catalog-new__sidebar">
            {{-- Kategori Game --}}
            <div class="sidebar-section">
                <h3 class="sidebar-section__title">Kategori Game</h3>
                <ul class="sidebar-section__list">
                    <li>
                        <a href="{{ route('catalog.index') }}" class="{{ ! $selectedGame && ! $selectedDeveloper ? 'is-active' : '' }}">
                            <span>Semua Game</span>
                        </a>
                    </li>
                    @foreach ($games as $game)
                        <li>
                            <a href="{{ route('catalog.index', ['game' => $game->slug]) }}"
                               class="{{ $selectedGame?->is($game) ? 'is-active' : '' }}">
                                <span>{{ $game->name }}</span>
                                <span class="count">({{ $game->products_count ?? 0 }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Kategori Developer --}}
            <div class="sidebar-section">
                <h3 class="sidebar-section__title">Kategori Developer</h3>
                <ul class="sidebar-section__list">
                    @foreach ($developers as $developer)
                        <li>
                            <a href="{{ route('catalog.index', ['developer' => $developer->slug]) }}"
                               class="{{ $selectedDeveloper?->is($developer) ? 'is-active' : '' }}">
                                <span>{{ $developer->name }}</span>
                                <span class="count">({{ $developer->products_count ?? 0 }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Filter Harga (dual range slider) --}}
            <div class="sidebar-section">
                <h3 class="sidebar-section__title">Filter Harga</h3>
                <form method="GET" action="{{ route('catalog.index') }}" class="price-filter" id="price-filter-form">
                    @if ($selectedGame)
                        <input type="hidden" name="game" value="{{ $selectedGame->slug }}">
                    @endif
                    @if ($selectedDeveloper)
                        <input type="hidden" name="developer" value="{{ $selectedDeveloper->slug }}">
                    @endif
                    @if ($selectedTag)
                        <input type="hidden" name="tag" value="{{ $selectedTag }}">
                    @endif

                    <div class="price-filter__display">
                        <span id="price-min-label">Rp{{ number_format($minPrice ?? 0, 0, ',', '.') }}</span>
                        <span class="muted">–</span>
                        <span id="price-max-label">Rp{{ number_format($maxPrice ?? $sliderMax, 0, ',', '.') }}</span>
                    </div>

                    <div class="price-filter__slider">
                        <input type="range" name="min_price" id="price-min" min="0" max="{{ $sliderMax }}" step="10000"
                               value="{{ $minPrice ?? 0 }}" data-role="min">
                        <input type="range" name="max_price" id="price-max" min="0" max="{{ $sliderMax }}" step="10000"
                               value="{{ $maxPrice ?? $sliderMax }}" data-role="max">
                        <div class="price-filter__track"><div class="price-filter__track-fill" id="price-track-fill"></div></div>
                    </div>

                    <div class="price-filter__actions">
                        <button type="submit" class="price-filter__btn">TERAPKAN</button>
                        <a href="{{ route('catalog.index', array_filter(['game' => $selectedGame?->slug, 'developer' => $selectedDeveloper?->slug, 'tag' => $selectedTag])) }}"
                           class="price-filter__reset">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Penawaran Terbaik --}}
            <div class="sidebar-section">
                <h3 class="sidebar-section__title">Penawaran Terbaik</h3>
                <p class="muted small">Segera hadir</p>
            </div>

            {{-- Terakhir Dilihat --}}
            <div class="sidebar-section">
                <h3 class="sidebar-section__title">Terakhir Dilihat</h3>
                <p class="muted small">Belum ada riwayat</p>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="catalog-new__main">
            {{-- Header --}}
            <div class="catalog-new__header">
                <h1 class="catalog-new__title">{{ $selectedGame?->name ?? $selectedDeveloper?->name ?? 'Katalog' }}</h1>

                <div class="catalog-new__meta">
                    <span class="catalog-new__count">
                        Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} hasil
                    </span>

                    <form method="GET" action="{{ route('catalog.index') }}" class="catalog-new__sort">
                        @if ($selectedGame)
                            <input type="hidden" name="game" value="{{ $selectedGame->slug }}">
                        @endif
                        @if ($selectedDeveloper)
                            <input type="hidden" name="developer" value="{{ $selectedDeveloper->slug }}">
                        @endif
                        <select name="sort" onchange="this.form.submit()">
                            <option value="" @selected(request('sort') === '' || request('sort') === null)>Urutkan berdasarkan Nama</option>
                            <option value="newest" @selected(request('sort') === 'newest')>Urutkan berdasarkan Terbaru</option>
                            <option value="popular" @selected(request('sort') === 'popular')>Urutkan berdasarkan Terpopuler</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Urutkan berdasarkan Termurah</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Urutkan berdasarkan Termahal</option>
                        </select>
                        <noscript><button type="submit">Urutkan</button></noscript>
                    </form>
                </div>
            </div>

            @auth
                @if (auth()->check() && auth()->user()?->isSpammer())
                    @include('partials.quota-banner')
                @endif
            @endauth

            {{-- Permintaan user #3: spammer tetap boleh jelajah katalog; kalau
                 kuota habis, banner ini menjelaskan produk mana yang terkunci. --}}
            @if ($quotaExhausted ?? false)
                <div class="quota quota--warning" role="status">
                    <strong>Kuota lihat produk kamu sudah habis ({{ $viewQuota }}/{{ $viewQuota }}).</strong>
                    <p>Katalog ini tetap bisa kamu jelajahi, tapi halaman detail produk akan terkunci sampai admin mereset kuota / akunmu dipulihkan.</p>
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="empty">Belum ada produk.</div>
            @else
                <ul class="product-grid-new">
                    @foreach ($products as $product)
                        <li class="product-card-new">
                            <a href="{{ route('slug.show', $product->slug) }}">
                                <div class="product-card-new__image">
                                    @if ($product->images->isNotEmpty())
                                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" loading="lazy">
                                    @else
                                        <span class="image-placeholder">MERCATORIA</span>
                                    @endif

                                    @if ($product->tagLabel())
                                        <span class="badge-new badge-new--{{ $product->tag }}">{{ $product->tagLabel() }}</span>
                                    @endif

                                    @if ($product->isOutOfStock())
                                        <span class="badge-new badge-new--sold">Out of stock</span>
                                    @endif
                                </div>
                                <h3 class="product-card-new__name">{{ $product->name }}</h3>
                                @unless (auth()->check() && auth()->user()?->isSpammer())
                                    @php($prices = $product->variants->map(fn ($variant) => $variant->sellingPrice($calculator))->filter())
                                    @if ($prices->isNotEmpty())
                                        <p class="product-card-new__price">
                                            @if ($prices->min() !== $prices->max())
                                                <span class="price-range">{{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }} – {{ \App\Support\PriceCalculator::formatRupiah($prices->max()) }}</span>
                                            @else
                                                {{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }}
                                            @endif
                                        </p>
                                    @endif
                                @endunless
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination --}}
                <div class="pagination-new">
                    {{ $products->links('partials.pagination') }}
                </div>
            @endif
        </main>
    </div>
</div>

<script>
(function () {
    var min = document.getElementById('price-min');
    var max = document.getElementById('price-max');
    if (!min || !max) return;
    var fill = document.getElementById('price-track-fill');
    var labelMin = document.getElementById('price-min-label');
    var labelMax = document.getElementById('price-max-label');
    var top = parseInt(max.max, 10);

    function fmt(n) { return 'Rp' + n.toLocaleString('id-ID'); }

    function sync(source) {
        var lo = parseInt(min.value, 10), hi = parseInt(max.value, 10);
        // Cegah handle saling menyilang.
        if (lo > hi) {
            if (source === min) { min.value = hi; lo = hi; }
            else { max.value = lo; hi = lo; }
        }
        labelMin.textContent = fmt(lo);
        labelMax.textContent = fmt(hi);
        if (fill) {
            fill.style.left = (lo / top * 100) + '%';
            fill.style.right = (100 - hi / top * 100) + '%';
        }
    }

    min.addEventListener('input', function () { sync(min); });
    max.addEventListener('input', function () { sync(max); });
    sync(null);
})();
</script>
@endsection