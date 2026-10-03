@extends('layouts.app', ['title' => $product->name])

@php
    // Null safety (permintaan user): semua variabel yang berpotensi tidak
    // didefinisikan diberi default di sini supaya view TIDAK PERNAH melempar
    // "Undefined variable" -> 500, walau controller versi lama ikut ter-deploy.
    $calculator = $calculator ?? \App\Support\PriceCalculator::fromSettings();
    $variants = collect($variants ?? []);
    $viewQuota = $viewQuota ?? 10;
@endphp

@push('head')
@php
    $firstVariant = $variants->firstWhere('available', true) ?? $variants->first();

    $ld = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->name,
        'description' => \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 200),
    ];

    if ($product->images->isNotEmpty()) {
        $ld['image'] = $product->images->first()->url();
    }
    if ($product->sku) {
        $ld['sku'] = $product->sku;
    }
    if ($product->game) {
        $ld['category'] = $product->game->name;
    }
    if ($firstVariant && $firstVariant['price'] !== null) {
        $ld['offers'] = [
            '@type'         => 'Offer',
            'priceCurrency' => 'IDR',
            'price'         => (string) $firstVariant['price'],
            'availability'  => $firstVariant['available']
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url'           => url()->current(),
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <article class="product" data-product>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            @if ($product->game)
                / <a href="{{ route('catalog.index', ['game' => $product->game->slug]) }}">{{ $product->game->name }}</a>
            @endif
        </nav>

        @if (($user ?? null)?->isSpammer())
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

                <p class="sold-out-note" data-sold-out hidden>Varian ini sedang habis.</p>

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

                @auth
                    <form method="POST" action="{{ route('cart.store') }}" class="product__add-to-cart">
                        @csrf
                        <input type="hidden" name="variant" value="" data-product-variant-id>
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="button button--block" data-add-to-cart disabled>Tambah ke keranjang</button>
                    </form>
                @else
                    {{-- Halaman produk kini global (guest boleh lihat), tapi
                         keranjang butuh login: arahkan ke halaman masuk dulu. --}}
                    <div class="product__add-to-cart">
                        <a href="{{ route('login') }}" class="button button--block">Masuk untuk belanja</a>
                    </div>
                @endauth

                <dl class="product__meta">
                    @if ($product->game)
                        <div><dt>Game</dt><dd>{{ $product->game->name ?? 'General' }}</dd></div>
                    @endif
                    @if ($product->developer)
                        <div><dt>Developer</dt><dd>{{ $product->developer->name ?? 'Unknown' }}</dd></div>
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
            if (!root) return;

            var price = root.querySelector('[data-price]');
            var comparePrice = root.querySelector('[data-compare-price]');
            var mainImage = root.querySelector('[data-main-image]');
            var soldOut = root.querySelector('[data-sold-out]');
            var addToCartButton = root.querySelector('[data-add-to-cart]');
            var hiddenVariantId = root.querySelector('[data-product-variant-id]');

            function select(input) {
                if (!input) return;

                hiddenVariantId.value = input.value;
                addToCartButton.disabled = !(input.dataset.available === '1');
                if (price) price.textContent = input.dataset.price;
                if (comparePrice) {
                    comparePrice.textContent = input.dataset.comparePrice || '';
                    comparePrice.hidden = !input.dataset.comparePrice;
                }
                if (soldOut) {
                    soldOut.hidden = input.dataset.available === '1';
                }
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
