@extends('layouts.app', ['title' => 'Beranda'])

@section('content')
    {{-- Hero Slider Section --}}
    @if($slides->isNotEmpty())
        <section class="hero-slider" style="margin-bottom: 40px; border-radius: 12px; overflow: hidden;">
            @foreach($slides as $slide)
                <a href="{{ $slide->link_url ?? '#' }}">
                    <img src="{{ asset('storage/' . $slide->image_desktop_path) }}" alt="{{ $slide->alt_text }}" style="width: 100%; height: auto; display: block;">
                </a>
            @endforeach
        </section>
    @endif

    {{-- Kategori Game Section --}}
    @if($games->isNotEmpty())
        <section class="game-categories" style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
                <h2 style="margin: 0;">Jelajahi Game Favoritmu</h2>
                <a href="{{ route('catalog.index') }}" class="link-button">Lihat Semua Game</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                @foreach($games as $game)
                    <a href="{{ route('catalog.index', ['game' => $game->slug]) }}" class="card" style="text-align: center; padding: 15px; text-decoration: none;">
                        @if($game->image_path)
                            <img src="{{ asset('storage/' . $game->image_path) }}" alt="{{ $game->name }}" style="width: 60px; height: 60px; border-radius: 8px; margin-bottom: 10px;">
                        @else
                            <div style="width: 60px; height: 60px; background: var(--bg); border-radius: 8px; margin: 0 auto 10px; display: flex; align-items:center; justify-content:center; font-size: 24px; font-weight: bold; color: var(--muted);">{{ substr($game->name, 0, 1) }}</div>
                        @endif
                        <span style="display: block; font-weight: 600; color: var(--text); font-size: 14px;">{{ $game->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk Terbaru Section --}}
    @if($latestProducts->isNotEmpty())
        <section class="latest-products" style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
                <h2 style="margin: 0;">Merch Baru</h2>
                <a href="{{ route('catalog.index') }}" class="link-button">Lihat Semua Produk</a>
            </div>
            
            <ul class="product-grid">
                @foreach ($latestProducts as $product)
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
                            <h3 class="product-card__name" style="font-size: 14px; margin: 10px 12px 4px;">{{ $product->name }}</h3>
                            
                            @unless (optional($user)->isSpammer())
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
        </section>
    @endif
@endsection
