@extends('layouts.app', ['title' => 'Katalog'])

@section('content')
    <div class="catalog">
        <div class="catalog__head">
            <h1>Katalog</h1>

            <form method="GET" action="{{ route('catalog.index') }}" class="filters">
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
                <select name="tag" aria-label="Tag" onchange="this.form.submit()">
                    <option value="">Semua tag</option>
                    @foreach (\App\Models\Product::TAGS as $value => $label)
                        <option value="{{ $value }}" @selected($selectedTag === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="button button--small">Terapkan</button></noscript>
            </form>
        </div>

        @if ($user->isSpammer())
            @include('partials.quota-banner')
        @endif

        @if ($products->isEmpty())
            <p class="card muted center">Belum ada produk.</p>
        @else
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
                            <h2 class="product-card__name">{{ $product->name }}</h2>
                            @unless ($user->isSpammer())
                                @php($prices = $product->variants->map(fn ($variant) => $variant->sellingPrice($calculator))->filter())
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

            {{ $products->links('partials.pagination') }}
        @endif
    </div>
@endsection