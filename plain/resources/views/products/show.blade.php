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
        {{-- Breadcrumb DI LUAR card boxed (sesuai permintaan) --}}
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            @if ($product->game)
                / <a href="{{ route('catalog.index', ['game' => $product->game->slug]) }}">{{ $product->game->name }}</a>
            @endif
        </nav>

        @if (($user ?? null)?->isSpammer())
            @include('partials.quota-banner')
        @endif

        {{-- ===== BOXED CARD: putih, max 1200px, radius, shadow ===== --}}
        <div class="product-boxed-card">
            <div class="product-boxed-layout">
                {{-- Kolom kiri: gambar (50%) --}}
                <div class="product-boxed-gallery">
                    <div class="product__main-image product-boxed-main-image">
                        {{-- BUG 5: badge LIMITED/PRESALE di pojok KIRI ATAS gambar (absolute) --}}
                        @if ($product->tagLabel())
                            <span class="tag tag--{{ $product->tag }} product-boxed-badge">{{ $product->tagLabel() }}</span>
                        @endif
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

                {{-- Kolom kanan: info (50%), urutan sesuai spesifikasi --}}
                <div class="product-boxed-info">
                    {{-- 1. Judul --}}
                    <h1 class="product-boxed-title">{{ $product->name }}</h1>

                    {{-- 2. Harga --}}
                    <p class="product__price product-boxed-price">
                        <del data-compare-price hidden></del>
                        <strong data-price>Pilih varian</strong>
                    </p>

                    <p class="sold-out-note" data-sold-out hidden>Varian ini sedang habis.</p>

                    @if ($variants->isNotEmpty())
                        {{-- Dinamis: label fieldset mengikuti nama atribut varian. Sumber prioritas:
                             key 'attribute' pada baris varian (bila controller menyediakannya),
                             lalu parsing "Atribut: Nilai" dari nama varian (mis. "Karakter: Raiden"),
                             terakhir fallback statis "Pilih Varian".
                             CATATAN: semua radio TETAP dalam SATU fieldset & satu group name="variant"
                             karena JS update harga/gambar/tombol memetakan 1 produk = 1 varian terpilih.
                             Struktur class/data-attr label TEPERSIS sama, logic JS tidak disentuh. --}}
                        @php
                            $firstVariant = $variants->first();
                            $firstAttribute = trim((string) ($firstVariant['attribute'] ?? ''));
                            if ($firstAttribute === '' && preg_match('/^(.{2,24}?):/u', trim((string) $firstVariant['name']), $m)) {
                                $firstAttribute = trim($m[1]);
                            }
                        @endphp
                        <fieldset class="variants">
                            <legend>Pilih {{ $firstAttribute !== '' ? \Illuminate\Support\Str::ucfirst($firstAttribute) : 'Varian' }}</legend>
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

                    {{-- 3. Quantity + TAMBAH KE KERANJANG dalam satu baris --}}
                    @auth
                        <form method="POST" action="{{ route('cart.store') }}" class="product-boxed-buyrow product__add-to-cart">
                            @csrf
                            <input type="hidden" name="variant" value="" data-product-variant-id>
                            <div class="product-boxed-qty" data-qty-control>
                                <button type="button" data-qty-minus aria-label="Kurangi">−</button>
                                <input type="number" name="quantity" value="1" min="1" max="99" data-qty-input inputmode="numeric">
                                <button type="button" data-qty-plus aria-label="Tambah">+</button>
                            </div>
                            <button type="submit" class="button" data-add-to-cart disabled>TAMBAH KE KERANJANG</button>
                        </form>
                    @else
                        {{-- Halaman produk global (guest boleh lihat), tapi
                             keranjang butuh login: arahkan ke halaman masuk dulu. --}}
                        <div class="product-boxed-buyrow product__add-to-cart">
                            <a href="{{ route('login') }}" class="button button--block">Masuk untuk belanja</a>
                        </div>
                    @endauth

                    {{-- 4. Info meta: SKU, Kategori(Game), Tag, Brand(Developer) --}}
                    <dl class="product__meta product-boxed-meta">
                        @if ($product->sku)
                            <div><dt>SKU</dt><dd>{{ $product->sku }}</dd></div>
                        @endif
                        <div><dt>Kategori</dt><dd>{{ $product->game->name ?? 'General' }}</dd></div>
                        <div><dt>Tag</dt><dd>{{ $product->tagLabel() ?: '-' }}</dd></div>
                        <div><dt>Brand</dt><dd>{{ $product->developer->name ?? 'Unknown' }}</dd></div>
                    </dl>

                    {{-- 5. Jaminan Pembayaran Aman --}}
                    <div class="product-boxed-payment">
                        <p class="product-boxed-payment__title">🔒 Jaminan Pembayaran Aman</p>
                        <div class="product-boxed-payment__logos">
                            <span class="product-boxed-pay-logo">BCA</span>
                            <span class="product-boxed-pay-logo">QRIS</span>
                            <span class="product-boxed-pay-logo">PayPal</span>
                            <span class="product-boxed-pay-logo">Alipay</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TAB di bawah card: Deskripsi / Ulasan ===== --}}
        <div class="product-boxed-tabs" data-product-tabs>
            <div class="product-boxed-tabs__nav" role="tablist">
                <button type="button" class="product-boxed-tab is-active" data-tab-btn="deskripsi" role="tab">Deskripsi</button>
                <button type="button" class="product-boxed-tab" data-tab-btn="ulasan" role="tab">Ulasan (0)</button>
            </div>
            <div class="product-boxed-tabs__panel is-active" data-tab-panel="deskripsi">
                @if ($product->description)
                    <div class="prose product__description">{!! nl2br(e($product->description)) !!}</div>
                @else
                    <p class="muted">Belum ada deskripsi untuk produk ini.</p>
                @endif
            </div>
            <div class="product-boxed-tabs__panel" data-tab-panel="ulasan">
                <p class="muted">Belum ada ulasan.</p>
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

            // Quantity stepper (+/-) di baris beli
            var qtyWrap = root.querySelector('[data-qty-control]');
            if (qtyWrap) {
                var qtyInput = qtyWrap.querySelector('[data-qty-input]');
                var clampQty = function (v) {
                    v = parseInt(v, 10);
                    if (isNaN(v) || v < 1) return 1;
                    if (v > 99) return 99;
                    return v;
                };
                qtyWrap.querySelector('[data-qty-minus]').addEventListener('click', function () {
                    qtyInput.value = clampQty(qtyInput.value - 1);
                });
                qtyWrap.querySelector('[data-qty-plus]').addEventListener('click', function () {
                    qtyInput.value = clampQty(qtyInput.value + 1);
                });
                qtyInput.addEventListener('change', function () {
                    qtyInput.value = clampQty(qtyInput.value);
                });
            }

            // Tab Deskripsi / Ulasan: toggle class is-active
            var tabsRoot = document.querySelector('[data-product-tabs]');
            if (tabsRoot) {
                tabsRoot.querySelectorAll('[data-tab-btn]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        tabsRoot.querySelectorAll('[data-tab-btn]').forEach(function (b) {
                            b.classList.toggle('is-active', b === btn);
                        });
                        tabsRoot.querySelectorAll('[data-tab-panel]').forEach(function (p) {
                            p.classList.toggle('is-active', p.dataset.tabPanel === btn.dataset.tabBtn);
                        });
                    });
                });
            }
        })();
    </script>
@endpush
