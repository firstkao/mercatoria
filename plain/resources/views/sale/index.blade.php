@extends('layouts.app', ['title' => 'Sedang Diskon'])

@section('content')
    <div class="catalog">
        <div class="catalog__head">
            <div>
                <h1 style="margin:0;">🔥 Sedang Diskon</h1>
                <p class="muted" style="margin:4px 0 0;">Harga coret terbatas, buruan sebelum masa promo habis.</p>
            </div>

            <form method="GET" action="{{ route('sale.index') }}" class="filters">
                <select name="game" aria-label="Game" onchange="this.form.submit()">
                    <option value="">Semua game</option>
                    @foreach ($games as $game)
                        <option value="{{ $game->slug }}" @selected($selectedGame?->is($game))>{{ $game->name }}</option>
                    @endforeach
                </select>
                <select name="developer" aria-label="Developer" onchange="this.form.submit()">
                    <option value="">Semua developer</option>
                    @foreach ($developers as $developer)
                        <option value="{{ $developer->slug }}" @selected($selectedDeveloper?->is($developer))>{{ $developer->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="button button--small">Terapkan</button></noscript>
            </form>
        </div>

        @if ($user?->isSpammer())
            @include('partials.quota-banner')
        @endif

        @if ($products->isEmpty())
            <div class="empty" style="margin-top:24px;">
                <p>Belum ada produk yang sedang diskon{{ ($selectedGame || $selectedDeveloper) ? ' untuk filter ini' : '' }}.</p>
                <p class="muted">Cek lagi nanti atau <a href="{{ route('catalog.index') }}" class="link">jelajahi katalog</a>.</p>
            </div>
        @else
            <ul class="product-grid">
                @foreach ($products as $product)
                    @php($discountPct = $product->maxDiscountPercent())
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

                                @if ($discountPct > 0)
                                    <span class="tag tag--discount">-{{ $discountPct }}%</span>
                                @endif

                                @if ($product->isOutOfStock())
                                    <span class="tag tag--sold">Out of stock</span>
                                @endif
                            </div>
                            <h2 class="product-card__name">{{ $product->name }}</h2>

                            @unless ($user?->isSpammer())
                                @php
                                    $prices = $product->variants->map(fn ($v) => $v->sellingPrice($calculator))->filter();
                                    $comparePrices = $product->variants->map(fn ($v) => $v->comparePrice($calculator))->filter();
                                @endphp
                                @if ($prices->isNotEmpty())
                                    <p class="product-card__price">
                                        @if ($comparePrices->isNotEmpty())
                                            <del class="product-card__compare">{{ \App\Support\PriceCalculator::formatRupiah($comparePrices->min()) }}</del>
                                        @endif
                                        {{ $prices->min() === $prices->max() ? '' : 'Mulai ' }}{{ \App\Support\PriceCalculator::formatRupiah($prices->min()) }}
                                    </p>
                                @endif
                            @endunless
                        </a>
                    </li>
                @endforeach
            </ul>

            {{ $products->links('partials.pagination') }}
        @endif
    </div>
@endsection