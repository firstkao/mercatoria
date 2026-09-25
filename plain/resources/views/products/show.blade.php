@extends('layouts.app', ['title' => $product->name])

@section('content')
    <article class="product" data-product>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            @if ($product->game)
                / <a href="{{ route('catalog.index', ['game' => $product->game->slug]) }}">{{ $product->game->name }}</a>
            @endif
        </nav>

        @if ($user->isSpammer())
            @include('partials.quota-banner')
        @endif

        <div class="product__layout">
            <div class="product__gallery">
                <div class="product__main-image">
                    @if ($product->images->isNotEmpty())
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" data-main-image>
                    @else
                        <span class="image-placeholder">MERCATORIA</span>
                    @endif
                </div>
                @if ($product->images->count() > 1)
                    <div class="product__thumbs">
                        @foreach ($product->images as $image)
                            <button type="button" data-thumb="{{ $image->url() }}"><img src="{{ $image->url() }}" alt="" loading="lazy"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product__info">
                @if ($product->tagLabel())
                    <span class="tag tag--{{ $product->tag }} tag--inline">{{ $product->tagLabel() }}</span>
                @endif
                <h1>{{ $product->name }}</h1>

                <p class="product__price">
                    <del data-compare-price hidden></del>
                    <strong data-price>Pilih varian</strong>
                </p>

                @if ($variants->isNotEmpty())
                    <fieldset class="variants">
                        <legend>Varian</legend>
                        @foreach ($variants as $variant)
                            <label class="variant {{ $variant['available'] ? '' : 'variant--sold' }}">
                                <input type="radio" name="variant" value="{{ $variant['id'] }}"
                                       data-price="{{ $variant['price'] !== null ? \App\Support\PriceCalculator::formatRupiah($variant['price']) : 'Harga belum tersedia' }}"
                                       data-compare-price="{{ $variant['comparePrice'] !== null ? \App\Support\PriceCalculator::formatRupiah($variant['comparePrice']) : '' }}"
                                       data-image="{{ $variant['imageUrl'] }}"
                                       data-available="{{ $variant['available'] && $variant['price'] !== null ? '1' : '0' }}"
                                       @checked($loop->first)>
                                <span>
                                    {{ $variant['name'] }}
                                    @unless ($variant['available'])
                                        <small>Out of stock</small>
                                    @endunless
                                </span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif

                <p class="sold-out-note" data-sold-out hidden>Varian ini sedang out of stock.</p>
                <button type="button" class="button button--block" data-add-to-cart disabled>Tambah ke keranjang</button>
                <p class="hint">Keranjang aktif di tahap berikutnya.</p>

                <dl class="product__meta">
                    @if ($product->game)
                        <div><dt>Game</dt><dd>{{ $product->game->name }}</dd></div>
                    @endif
                    @if ($product->developer)
                        <div><dt>Developer</dt><dd>{{ $product->developer->name }}</dd></div>
                    @endif
                </dl>

                @if ($product->description)
                    <div class="prose product__description">{!! nl2br(e($product->description)) !!}</div>
                @endif
            </div>
        </div>
    </article>
@endsection

@push('scripts')
    <script>
        (function () {
            var root = document.querySelector('[data-product]');
            var price = root.querySelector('[data-price]');
            var comparePrice = root.querySelector('[data-compare-price]');
            var mainImage = root.querySelector('[data-main-image]');
            var soldOut = root.querySelector('[data-sold-out]');

            function select(input) {
                price.textContent = input.dataset.price;
                comparePrice.textContent = input.dataset.comparePrice;
                comparePrice.hidden = !input.dataset.comparePrice;
                soldOut.hidden = input.dataset.available === '1';
                if (mainImage && input.dataset.image) {
                    mainImage.src = input.dataset.image;
                }
            }

            root.querySelectorAll('input[name=variant]').forEach(function (input) {
                input.addEventListener('change', function () { select(input); });
                if (input.checked) {
                    select(input);
                }
            });

            root.querySelectorAll('[data-thumb]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (mainImage) {
                        mainImage.src = button.dataset.thumb;
                    }
                });
            });
        })();
    </script>
@endpush