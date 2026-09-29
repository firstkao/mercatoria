@extends('layouts.app', ['title' => $q !== '' ? "Cari: {$q}" : 'Cari Produk'])

@section('content')
    <div class="catalog">
        <div class="catalog__head">
            <h1>{{ $q !== '' ? 'Hasil Pencarian' : 'Cari Produk' }}</h1>
        </div>

        <form method="GET" action="{{ route('search.index') }}" class="search-page-form">
            <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, SKU, game, atau developer…" autofocus required>
            <button type="submit" class="button">Cari</button>
        </form>

        @if ($q === '')
            <p class="card muted center">Masukkan kata kunci untuk mencari produk.</p>
        @else
            @php($total = $products->count() + $pages->count() + $games->count() + $developers->count())

            @if ($total === 0)
                <div class="empty">
                    <p>Tidak ada hasil untuk <strong>"{{ $q }}"</strong>.</p>
                    <p class="muted">Coba kata kunci lain, atau <a href="{{ route('catalog.index') }}" class="link">jelajahi katalog</a>.</p>
                </div>
            @else
                <p class="muted" style="margin-bottom:24px;">{{ $total }} hasil untuk <strong>"{{ $q }}"</strong>.</p>

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
                                    <a href="{{ route('products.show', $product) }}">
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