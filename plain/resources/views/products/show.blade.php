@extends('layouts.app', ['title' => $product->name])

@php
    // Null safety: semua variabel yang berpotensi tidak
    // didefinisikan diberi default di sini supaya view TIDAK PERNAH melempar
    // "Undefined variable" -> 500, walau controller versi lama ikut ter-deploy.
    $calculator = $calculator ?? \App\Support\PriceCalculator::fromSettings();
    $variants = collect($variants ?? []);
    $viewQuota = $viewQuota ?? 10;
@endphp

@push('head')
<style>
    /* Modal "masuk keranjang" - di-embed di sini supaya tidak tergantung cache app.css */
    .pb-cm { position: fixed; inset: 0; z-index: 99990; display: grid; place-items: center; padding: 16px; background: rgba(0, 0, 0, .55); animation: pb-cm-fade .15s ease; }
    .pb-cm__box {
        position: relative; width: 100%; max-width: 420px; padding: 28px 24px 22px;
        background: #fff; border-radius: 6px; box-shadow: 0 12px 40px rgba(0, 0, 0, .25);
        animation: pb-cm-pop .18s ease;
    }
    .pb-cm__head { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; }
    .pb-cm__icon { flex: 0 0 auto; width: 32px; height: 32px; display: grid; place-items: center; border-radius: 50%; background: #10b981; color: #fff; }
    .pb-cm--error .pb-cm__icon { background: #d93b3b; }
    .pb-cm__title { margin: 0; font-size: 17px; font-weight: 700; color: #1a1a25; }
    .pb-cm__msg { margin: 0 0 16px; font-size: 14px; color: #4a4655; }
    .pb-cm__item { display: flex; gap: 14px; align-items: center; margin: 0 0 20px; padding: 12px; border: 1px solid #ececf1; border-radius: 4px; }
    .pb-cm__item img { flex: 0 0 64px; width: 64px; height: 64px; object-fit: cover; background: #f8f9fb; }
    .pb-cm__name { margin: 0 0 2px; font-size: 14px; font-weight: 600; line-height: 1.35; color: #1a1a25; overflow-wrap: anywhere; }
    .pb-cm__meta { margin: 0; font-size: 12.5px; color: #6b6775; }
    .pb-cm__price { margin: 4px 0 0; font-size: 14px; font-weight: 700; color: #1a1a25; }
    .pb-cm__actions { display: flex; gap: 10px; }
    .pb-cm .pb-cm__btn {
        all: unset; box-sizing: border-box; cursor: pointer; flex: 1 1 0;
        display: inline-flex; align-items: center; justify-content: center; text-align: center;
        height: 38px; padding: 0 14px; border-radius: 3px;
        background: #1f1b2d; color: #fff; font: inherit; font-size: 13px; font-weight: 700;
        letter-spacing: .02em; border: 1px solid #1f1b2d;
    }
    .pb-cm .pb-cm__btn:hover { background: #3a3550; border-color: #3a3550; }
    .pb-cm .pb-cm__btn--ghost { background: #fff; color: #1f1b2d; border-color: #d9d9e0; }
    .pb-cm .pb-cm__btn--ghost:hover { background: #f4f4f7; border-color: #bdbdc8; }
    .pb-cm .pb-cm__btn:focus-visible,
    .pb-cm .pb-cm__x:focus-visible { outline: 2px solid var(--accent-strong, #0299e7); outline-offset: 2px; }
    .pb-cm .pb-cm__x {
        all: unset; box-sizing: border-box; cursor: pointer; position: absolute; top: 8px; right: 8px;
        width: 32px; height: 32px; display: grid; place-items: center; color: #6b6775;
    }
    .pb-cm .pb-cm__x:hover { color: #1a1a25; }
    @keyframes pb-cm-fade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes pb-cm-pop { from { transform: translateY(8px) scale(.98); opacity: 0; } to { transform: none; opacity: 1; } }
    @media (max-width: 420px) { .pb-cm__actions { flex-direction: column-reverse; } }
</style>
@endpush

@push('head')
<style>
    /* Lightbox zoom: sengaja di-embed di sini (bukan app.css) supaya tidak ketahan cache CSS lama */
    .pb-lb { position: fixed; inset: 0; z-index: 100000; display: block; margin: 0; padding: 0; background: rgba(0, 0, 0, .7); }
    .pb-lb__stage {
        position: absolute; inset: 0; padding: 44px; overflow: hidden;
        display: flex; align-items: center; justify-content: center; cursor: zoom-out;
    }
    .pb-lb .pb-lb__img {
        display: block; width: auto; height: auto; border: 0; border-radius: 0; background: none; box-shadow: none;
        max-width: calc(100vw - 88px); max-height: calc(100vh - 88px);
        object-fit: contain; cursor: zoom-in; user-select: none; -webkit-user-drag: none;
        transition: transform .25s ease;
    }
    .pb-lb.is-zoomed .pb-lb__img { transform: scale(2); cursor: zoom-out; }

    .pb-lb__bar { position: absolute; top: 0; right: 0; z-index: 2; display: flex; }
    .pb-lb button.pb-lb__btn,
    .pb-lb button.pb-lb__nav {
        all: unset; box-sizing: border-box; cursor: pointer;
        width: 44px; height: 44px; display: grid; place-items: center;
        color: #fff; opacity: .75;
    }
    .pb-lb button.pb-lb__btn:hover,
    .pb-lb button.pb-lb__nav:hover { opacity: 1; }
    .pb-lb button.pb-lb__btn:focus-visible,
    .pb-lb button.pb-lb__nav:focus-visible { outline: 2px solid #fff; outline-offset: -4px; }
    .pb-lb button.pb-lb__nav {
        position: absolute; top: 50%; margin-top: -22px; z-index: 2;
        background: rgba(0, 0, 0, .35); border-radius: 50%;
    }
    .pb-lb button.pb-lb__nav--prev { left: 12px; }
    .pb-lb button.pb-lb__nav--next { right: 12px; }
</style>
@endpush

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
    @php
        // Logo pembayaran: taruh file di public/images/payments/{bca,qris,shopeepay}.(svg|png|webp).
        // Kalau file belum ada, otomatis jatuh ke teks biasa (tidak ada gambar rusak).
        $payLogos = ['bca' => 'BCA', 'qris' => 'QRIS', 'shopeepay' => 'ShopeePay'];
        $payLogoUrl = function (string $key): ?string {
            foreach (['svg', 'png', 'webp'] as $ext) {
                if (file_exists(public_path("images/payments/{$key}.{$ext}"))) {
                    return asset("images/payments/{$key}.{$ext}");
                }
            }
            return null;
        };
    @endphp

    <article class="product" data-product>
        @if (($user ?? null)?->isSpammer())
            <div class="container">
                @include('partials.quota-banner')
            </div>
        @endif

        {{-- ===== SATU CARD PUTIH: gambar | info, lalu tab di bawahnya ===== --}}
        <div class="product-boxed-card">
            <div class="product-boxed-layout">

                {{-- ---------- KIRI: gambar ---------- --}}
                <div class="product-boxed-gallery">
                    <div class="product-boxed-main-image">
                        @if ($product->tagLabel())
                            <span class="product-boxed-badge tag tag--{{ $product->tag }}">{{ $product->tagLabel() }}</span>
                        @endif

                        @if ($product->images->isNotEmpty())
                            <button type="button" class="product-boxed-zoom" data-zoom aria-label="Perbesar gambar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                                    <circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/>
                                </svg>
                            </button>
                            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" data-main-image>
                        @else
                            <span class="image-placeholder">MERCATORIA</span>
                        @endif
                    </div>

                    @if ($product->images->count() > 1)
                        <div class="product-boxed-thumbs">
                            @foreach ($product->images as $image)
                                <button type="button" class="product-boxed-thumb {{ $loop->first ? 'is-active' : '' }}" data-thumb="{{ $image->url() }}">
                                    <img src="{{ $image->url() }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ---------- KANAN: info ---------- --}}
                <div class="product-boxed-info">
                    {{-- Breadcrumb ada DI DALAM kolom kanan, di atas judul --}}
                    <nav class="product-boxed-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}">Beranda</a>
                        @if ($product->game)
                            / <a href="{{ route('catalog.index', ['game' => $product->game->slug]) }}">{{ $product->game->name }}</a>
                        @endif
                        / <span>{{ $product->name }}</span>
                    </nav>

                    <h1 class="product-boxed-title">{{ $product->name }}</h1>

                    <div class="product-boxed-price-wrap">
                        <p class="product-boxed-price">
                            <del data-compare-price hidden></del>
                            <strong data-price>{{ $variants->isEmpty() ? 'Harga belum tersedia' : 'Pilih varian' }}</strong>
                        </p>
                        <p class="product-boxed-coin">
                            Beli produk ini sekarang dan dapatkan <strong>800 Koin</strong>!
                        </p>
                    </div>

                    <p class="sold-out-note" data-sold-out hidden>Varian ini sedang habis.</p>

                    {{-- Radio varian SELALU dirender (JS butuh), tapi chip-nya
                         disembunyikan kalau cuma ada 1 varian (sesuai desain). --}}
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
                        <fieldset class="product-boxed-variants" @if ($variants->count() === 1) hidden @endif>
                            <legend>Pilih Varian</legend>
                            <div class="variant-chips">
                                @foreach ($variants as $variant)
                                    @php $buyable = $variant['available'] && $variant['price'] !== null; @endphp
                                    <label class="variant-chip {{ $buyable ? '' : 'variant-chip--sold' }}">
                                        <input type="radio" name="variant" value="{{ $variant['id'] }}"
                                               data-price="{{ $variant['price'] !== null ? \App\Support\PriceCalculator::formatRupiah($variant['price']) : 'Harga belum tersedia' }}"
                                               data-compare-price="{{ $variant['comparePrice'] !== null ? \App\Support\PriceCalculator::formatRupiah($variant['comparePrice']) : '' }}"
                                               data-image="{{ $variant['imageUrl'] }}"
                                               data-available="{{ $buyable ? '1' : '0' }}"
                                               data-name="{{ $variant['name'] }}"
                                               @checked($loop->first)>
                                        <span>
                                            {{ $variant['name'] }}
                                            @unless ($variant['available'])
                                                <small>Out of stock</small>
                                            @endunless
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    {{-- Qty + tombol dalam satu baris --}}
                    @auth
                        <form method="POST" action="{{ route('cart.store') }}" class="product-boxed-buyrow">
                            @csrf
                            <input type="hidden" name="variant" value="" data-product-variant-id>
                            <div class="product-boxed-qty" data-qty-control>
                                <button type="button" class="product-boxed-qty-btn" data-qty-minus aria-label="Kurangi">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="99" class="product-boxed-qty-input" data-qty-input inputmode="numeric">
                                <button type="button" class="product-boxed-qty-btn" data-qty-plus aria-label="Tambah">+</button>
                            </div>
                            <button type="submit" class="product-boxed-add-to-cart button" data-add-to-cart disabled>Tambah ke keranjang</button>
                        </form>
                    @endauth

                    {{-- 4. Info meta: SKU, lalu Game -> Developer -> Tag (link katalog berfilter) --}}
                    <dl class="product__meta product-boxed-meta">
                        @if ($product->sku)
                            <div class="is-full"><dt>SKU:</dt><dd>{{ $product->sku }}</dd></div>
                        @endif

                        {{-- 1. Game (dulu Kategori) --}}
                        @if ($product->game)
                            <div>
                                <dt>Game</dt>
                                <dd><a href="{{ route('catalog.index', ['game' => $product->game->slug]) }}">{{ $product->game->name }}</a></dd>
                            </div>
                        @endif

                        {{-- 2. Developer (dulu Brand) --}}
                        @if ($product->developer)
                            <div>
                                <dt>Developer</dt>
                                <dd><a href="{{ route('catalog.index', ['developer' => $product->developer->slug]) }}">{{ $product->developer->name }}</a></dd>
                            </div>
                        @endif

                        {{-- 3. Tag --}}
                        @if ($product->tag)
                            <div>
                                <dt>Tag</dt>
                                <dd><a href="{{ route('catalog.index', ['tag' => $product->tag]) }}">{{ $product->tagLabel() }}</a></dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Jaminan Pembayaran Aman: fieldset, judul "menembus" garis atas --}}
                    <fieldset class="product-boxed-payment">
                        <legend class="product-boxed-payment__title">Jaminan Pembayaran Aman</legend>
                        <div class="product-boxed-payment__logos">
                            <img src="{{ asset('images/bca.svg') }}" alt="BCA" class="payment-logo">
                            <img src="{{ asset('images/qris.svg') }}" alt="QRIS" class="payment-logo">
                            <img src="{{ asset('images/shopeepay.svg') }}" alt="ShopeePay" class="payment-logo">
                        </div>
                    </fieldset>
                </div>
            </div>

            {{-- ===== TAB: Deskripsi (masih di dalam card putih yang sama) ===== --}}
            <div class="product-boxed-tabs" data-product-tabs>
                <div class="product-boxed-tabs__nav" role="tablist">
                    <button type="button" class="product-boxed-tab is-active" data-tab-btn="deskripsi" role="tab">Deskripsi</button>
                </div>
                <div class="product-boxed-tabs__panel is-active" data-tab-panel="deskripsi">
                    @if ($product->description)
                        <div class="prose product__description">{!! nl2br(e($product->description)) !!}</div>
                    @else
                        <p class="muted">Belum ada deskripsi untuk produk ini.</p>
                    @endif
                </div>
            </div>
        </div>
        @auth
            {{-- Modal konfirmasi masuk keranjang --}}
            <div class="pb-cm" data-cart-modal role="dialog" aria-modal="true" aria-labelledby="pb-cm-title" hidden>
                <div class="pb-cm__box">
                    <button type="button" class="pb-cm__x" data-cm-close aria-label="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
                    </button>
                    <div class="pb-cm__head">
                        <span class="pb-cm__icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" data-cm-icon-ok><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" data-cm-icon-err hidden><path d="M12 6v8M12 18v.5"/></svg>
                        </span>
                        <h2 class="pb-cm__title" id="pb-cm-title" data-cm-title>Berhasil masuk keranjang</h2>
                    </div>
                    <p class="pb-cm__msg" data-cm-msg hidden></p>
                    <div class="pb-cm__item" data-cm-item>
                        <img src="" alt="" data-cm-img hidden>
                        <div>
                            <p class="pb-cm__name" data-cm-name></p>
                            <p class="pb-cm__meta" data-cm-meta></p>
                            <p class="pb-cm__price" data-cm-price></p>
                        </div>
                    </div>
                    <div class="pb-cm__actions">
                        <button type="button" class="pb-cm__btn pb-cm__btn--ghost" data-cm-close>Lanjut belanja</button>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('cart.index') ? route('cart.index') : url('/cart') }}" class="pb-cm__btn" data-cm-cartlink>Lihat keranjang</a>
                    </div>
                </div>
            </div>
        @endauth
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
            // Dua elemen ini TIDAK ADA untuk guest (belum login) -> wajib null-check
            var addToCartButton = root.querySelector('[data-add-to-cart]');
            var hiddenVariantId = root.querySelector('[data-product-variant-id]');

            function setMainImage(src) {
                if (mainImage && src) mainImage.src = src;
            }

            function select(input) {
                if (!input) return;
                var canBuy = input.dataset.available === '1';

                if (hiddenVariantId) hiddenVariantId.value = input.value;
                if (addToCartButton && addToCartButton.tagName === 'BUTTON') addToCartButton.disabled = !canBuy;
                if (price) price.textContent = input.dataset.price;
                if (comparePrice) {
                    comparePrice.textContent = input.dataset.comparePrice || '';
                    comparePrice.hidden = !input.dataset.comparePrice;
                }
                if (soldOut) soldOut.hidden = canBuy;
                setMainImage(input.dataset.image);
            }

            root.querySelectorAll('input[name=variant]').forEach(function (input) {
                input.addEventListener('change', function () { select(input); });
                if (input.checked) select(input);
            });

            // Thumbnail
            root.querySelectorAll('[data-thumb]').forEach(function (button) {
                button.addEventListener('click', function () {
                    setMainImage(button.dataset.thumb);
                    root.querySelectorAll('[data-thumb]').forEach(function (b) {
                        b.classList.toggle('is-active', b === button);
                    });
                });
            });

            // Quantity stepper. FIX: value itu string, "1" + 1 = "11" -> harus parseInt dulu
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
                    qtyInput.value = clampQty(clampQty(qtyInput.value) - 1);
                });
                qtyWrap.querySelector('[data-qty-plus]').addEventListener('click', function () {
                    qtyInput.value = clampQty(clampQty(qtyInput.value) + 1);
                });
                qtyInput.addEventListener('change', function () {
                    qtyInput.value = clampQty(qtyInput.value);
                });
            }

            // Add to cart via AJAX -> tampilkan modal popup kalau berhasil
            var cartForm = root.querySelector('form.product-boxed-buyrow');
            var cartModal = document.querySelector('[data-cart-modal]');
            if (cartForm && cartModal && addToCartButton) {
                var cm = function (sel) { return cartModal.querySelector(sel); };
                var lastFocus = null, cmPrevOverflow = '';

                var closeCartModal = function () {
                    cartModal.hidden = true;
                    document.body.style.overflow = cmPrevOverflow;
                    document.removeEventListener('keydown', onCmKey);
                    if (lastFocus && lastFocus.focus) lastFocus.focus();
                };
                var onCmKey = function (e) { if (e.key === 'Escape') closeCartModal(); };

                var openCartModal = function (ok, message) {
                    cartModal.classList.toggle('pb-cm--error', !ok);
                    cm('[data-cm-title]').textContent = ok ? 'Berhasil masuk keranjang' : 'Gagal menambahkan';
                    cm('[data-cm-icon-ok]').hidden = !ok;
                    cm('[data-cm-icon-err]').hidden = ok;
                    cm('[data-cm-item]').hidden = !ok;
                    cm('[data-cm-cartlink]').hidden = !ok;

                    var msg = cm('[data-cm-msg]');
                    msg.hidden = !message;
                    msg.textContent = message || '';

                    if (ok) {
                        var chosen = root.querySelector('input[name=variant]:checked');
                        var qty = cartForm.querySelector('[data-qty-input]');
                        var multi = root.querySelectorAll('input[name=variant]').length > 1;
                        var meta = [];
                        if (multi && chosen && chosen.dataset.name) meta.push('Varian: ' + chosen.dataset.name);
                        meta.push('Jumlah: ' + (qty ? qty.value : 1));

                        cm('[data-cm-name]').textContent = (root.querySelector('.product-boxed-title') || {}).textContent || '';
                        cm('[data-cm-meta]').textContent = meta.join(' · ');
                        cm('[data-cm-price]').textContent = price ? price.textContent : '';
                        var im = cm('[data-cm-img]');
                        im.hidden = !(mainImage && mainImage.src);
                        if (mainImage && mainImage.src) im.src = mainImage.src;
                    }

                    lastFocus = document.activeElement;
                    cmPrevOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    cartModal.hidden = false;
                    document.addEventListener('keydown', onCmKey);
                    var focusEl = cm(ok ? '[data-cm-cartlink]' : '.pb-cm__btn--ghost');
                    if (focusEl) focusEl.focus();
                };

                cartModal.addEventListener('click', function (e) {
                    if (e.target === cartModal || e.target.closest('[data-cm-close]')) closeCartModal();
                });

                var restoreCartButton = function (label) {
                    addToCartButton.textContent = label;
                    var chosen = root.querySelector('input[name=variant]:checked');
                    addToCartButton.disabled = !(chosen && chosen.dataset.available === '1');
                };

                cartForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (addToCartButton.disabled || !hiddenVariantId || !hiddenVariantId.value) return;

                    var label = addToCartButton.textContent;
                    addToCartButton.disabled = true;
                    addToCartButton.textContent = 'Memproses...';

                    fetch(cartForm.action, {
                        method: 'POST',
                        body: new FormData(cartForm),
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (res) {
                        if (res.status === 419) {          // sesi/CSRF kedaluwarsa
                            location.reload();
                            return;
                        }
                        if (res.ok) {
                            openCartModal(true);
                            document.dispatchEvent(new CustomEvent('cart:added', {
                                detail: { variant: hiddenVariantId.value, quantity: cartForm.querySelector('[data-qty-input]').value }
                            }));
                            return;
                        }
                        return res.json().catch(function () { return {}; }).then(function (d) {
                            var m = d.message;
                            if (d.errors) {
                                var first = Object.keys(d.errors)[0];
                                if (first && d.errors[first] && d.errors[first][0]) m = d.errors[first][0];
                            }
                            openCartModal(false, m || 'Produk belum bisa dimasukkan ke keranjang. Coba lagi ya.');
                        });
                    }).catch(function () {
                        openCartModal(false, 'Koneksi bermasalah. Coba lagi ya.');
                    }).then(function () {
                        restoreCartButton(label);
                    });
                });
            }

            // Zoom / lightbox: toolbar zoom, fullscreen, tutup (+ panah kalau > 1 gambar)
            var zoomBtn = root.querySelector('[data-zoom]');
            if (zoomBtn && mainImage) {
                var svg = function (p) {
                    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + p + '</svg>';
                };
                var ICON = {
                    zoom:  svg('<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21M10.5 7.5v6M7.5 10.5h6"/>'),
                    full:  svg('<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>'),
                    close: svg('<path d="M5 5l14 14M19 5L5 19"/>'),
                    prev:  svg('<path d="M15 5l-7 7 7 7"/>'),
                    next:  svg('<path d="M9 5l7 7-7 7"/>')
                };
                var abs = function (u) { try { return new URL(u, location.href).href; } catch (e) { return u; } };

                var lb = null, imgEl = null, list = [], idx = 0, zoomed = false, prevOverflow = '';

                var setZoom = function (on, ev) {
                    zoomed = on;
                    lb.classList.toggle('is-zoomed', on);
                    if (on && ev) moveOrigin(ev);
                    if (!on) imgEl.style.transformOrigin = '';
                };
                var moveOrigin = function (ev) {
                    var r = imgEl.getBoundingClientRect();
                    var x = Math.min(100, Math.max(0, (ev.clientX - r.left) / r.width * 100));
                    var y = Math.min(100, Math.max(0, (ev.clientY - r.top) / r.height * 100));
                    imgEl.style.transformOrigin = x + '% ' + y + '%';
                };
                var show = function (i) {
                    idx = (i + list.length) % list.length;
                    imgEl.src = list[idx];
                    setZoom(false);
                };
                var onKey = function (e) {
                    if (e.key === 'Escape') close();
                    else if (e.key === 'ArrowLeft' && list.length > 1) show(idx - 1);
                    else if (e.key === 'ArrowRight' && list.length > 1) show(idx + 1);
                };
                var close = function () {
                    if (!lb) return;
                    lb.remove();
                    lb = null;
                    document.body.style.overflow = prevOverflow;
                    document.removeEventListener('keydown', onKey);
                    if (document.fullscreenElement && document.exitFullscreen) document.exitFullscreen();
                };
                var button = function (cls, icon, label, fn) {
                    var el = document.createElement('button');
                    el.type = 'button';
                    el.className = cls;
                    el.innerHTML = icon;
                    el.setAttribute('aria-label', label);
                    el.addEventListener('click', function (e) { e.stopPropagation(); fn(e); });
                    return el;
                };
                var open = function () {
                    close();

                    list = Array.prototype.map.call(root.querySelectorAll('[data-thumb]'), function (t) { return abs(t.dataset.thumb); });
                    var cur = abs(mainImage.src);
                    idx = list.indexOf(cur);
                    if (idx === -1) { list.unshift(cur); idx = 0; }

                    lb = document.createElement('div');
                    lb.className = 'pb-lb';

                    var bar = document.createElement('div');
                    bar.className = 'pb-lb__bar';
                    bar.appendChild(button('pb-lb__btn', ICON.zoom, 'Zoom', function (e) { setZoom(!zoomed, e); }));
                    if (lb.requestFullscreen) {
                        bar.appendChild(button('pb-lb__btn', ICON.full, 'Layar penuh', function () {
                            if (document.fullscreenElement) document.exitFullscreen(); else lb.requestFullscreen();
                        }));
                    }
                    bar.appendChild(button('pb-lb__btn', ICON.close, 'Tutup', close));
                    lb.appendChild(bar);

                    if (list.length > 1) {
                        lb.appendChild(button('pb-lb__nav pb-lb__nav--prev', ICON.prev, 'Sebelumnya', function () { show(idx - 1); }));
                        lb.appendChild(button('pb-lb__nav pb-lb__nav--next', ICON.next, 'Berikutnya', function () { show(idx + 1); }));
                    }

                    var stage = document.createElement('div');
                    stage.className = 'pb-lb__stage';
                    imgEl = document.createElement('img');
                    imgEl.className = 'pb-lb__img';
                    imgEl.src = list[idx];
                    imgEl.alt = mainImage.alt || '';
                    imgEl.addEventListener('click', function (e) { e.stopPropagation(); setZoom(!zoomed, e); });
                    stage.addEventListener('mousemove', function (e) { if (zoomed) moveOrigin(e); });
                    stage.addEventListener('click', close);   // klik area gelap = tutup
                    stage.appendChild(imgEl);
                    lb.appendChild(stage);

                    prevOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    document.body.appendChild(lb);
                    document.addEventListener('keydown', onKey);
                };
                zoomBtn.addEventListener('click', open);
                mainImage.addEventListener('click', open);
            }

            // Tab Deskripsi
            var tabsRoot = root.querySelector('[data-product-tabs]');
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
