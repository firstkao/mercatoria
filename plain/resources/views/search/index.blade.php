@extends('layouts.app', ['title' => $q !== '' ? "Cari: {$q}" : 'Cari Produk'])

@section('content')
    <div class="catalog search-page">
        <div class="catalog__head">
            <h1>{{ $q !== '' ? 'Hasil Pencarian' : 'Cari Produk' }}</h1>
            @if ($q !== '')
                @php($total = $products->count() + $pages->count() + $games->count() + $developers->count())
                <p class="muted" style="margin:4px 0 0;">
                    Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk untuk "<strong>{{ $q }}</strong>"
                    &middot; <a href="{{ route('search.index') }}" class="link">cari ulang</a>
                </p>
            @endif
        </div>

        {{-- Form pencarian dipercantik: input besar + ikon, tombol cari menyatu --}}
        <form method="GET" action="{{ route('search.index') }}" class="search-page-form">
            <span class="search-page-form__field">
                <span class="search-page-form__icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, SKU, game, atau developer…" autofocus>
            </span>
            {{-- Safe sorting v16.9: urut nama (standar) / terbaru / terpopuler / termurah / termahal --}}
            <select name="sort" class="search-page-form__sort" onchange="this.form.submit()" aria-label="Urutkan hasil">
                <option value="name" @selected(($sort ?? '') === 'name' || ($sort ?? '') === '')>Urut: Nama (A-Z)</option>
                <option value="newest" @selected(($sort ?? '') === 'newest')>Urut: Terbaru</option>
                <option value="popular" @selected(($sort ?? '') === 'popular')>Urut: Terpopuler</option>
                <option value="price" @selected(($sort ?? '') === 'price')>Urut: Termurah</option>
                <option value="price-desc" @selected(($sort ?? '') === 'price-desc')>Urut: Termahal</option>
            </select>
            <button type="submit" class="button button--primary">Cari</button>
        </form>

        @if ($q === '')
            <div class="card muted center" style="padding:40px 20px;">
                <p style="margin:0 0 8px;font-size:15px;">Masukkan kata kunci untuk mencari produk.</p>
                <p style="margin:0;font-size:13px;">Contoh: <em>figure</em>, <em>acrylic stand</em>, <em>Genshin</em>, atau kode SKU.</p>
            </div>
        @else
            @if ($total === 0)
                <div class="empty">
                    <p>Tidak ada hasil untuk <strong>"{{ $q }}"</strong>.</p>
                    <p class="muted">Coba kata kunci lain, atau <a href="{{ route('catalog.index') }}" class="link">jelajahi katalog</a>.</p>
                </div>
            @else
                {{-- Games --}}
                @if ($games->isNotEmpty())
                    <section style="margin-bottom:32px;">
                        <h2>Game</h2>
                        <div class="chip-list">
                            @foreach ($games as $game)
                                <a href="{{ route('catalog.index', ['game' => $game->slug]) }}" class="chip">{{ $game->name }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Developers --}}
                @if ($developers->isNotEmpty())
                    <section style="margin-bottom:32px;">
                        <h2>Developer</h2>
                        <div class="chip-list">
                            @foreach ($developers as $developer)
                                <a href="{{ route('catalog.index', ['developer' => $developer->slug]) }}" class="chip">{{ $developer->name }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Products --}}
                @if ($products->isNotEmpty())
                    <section style="margin-bottom:32px;">
                        <h2>Produk</h2>
                        <ul class="product-grid">
                            @foreach ($products as $product)
                                <li class="product-card">
                                    <a href="{{ route('slug.show', $product->slug) }}">
                                        <div class="product-card__image">
                                            @if ($product->images->isNotEmpty())
                                                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" loading="lazy">
                                            @else
                                                <span class="image-placeholder">MERCATORIA</span>
                                            @endif
                                            @if ($product->tagLabel())
                                                <span class="tag tag--{{ $product->tag }}">{{ $product->tagLabel() }}</span>
                                            @endif
                                            @if ($product->isOutOfStock())
                                                <span class="tag tag--sold">Out of stock</span>
                                            @endif
                                        </div>
                                        <h3 class="product-card__name">{{ $product->name }}</h3>
                                        @unless (optional($user)->isSpammer())
                                            @php($prices = $product->variants->map(fn ($v) => $v->sellingPrice($calculator))->filter())
                                            @if ($prices->isNotEmpty())
                                                <p class="product-card__price">
                                                    {{ $prices->min() === $prices->max() ? '' : 'Mulai ' }}{{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }}
                                                </p>
                                            @endif
                                        @endunless
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Pagination v16.9: 30 produk/halaman, sort & q ikut terbawa --}}
                        @if ($products->hasPages())
                            <div class="pagination-new" style="margin-top:24px;">
                                {{ $products->links('partials.pagination') }}
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Pages --}}
                @if ($pages->isNotEmpty())
                    <section>
                        <h2>Halaman</h2>
                        <ul class="chip-list">
                            @foreach ($pages as $page)
                                <li>
                                    <a href="{{ route('slug.show', $page->slug) }}" class="chip">{{ $page->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endif
        @endif
    </div>
@endsection