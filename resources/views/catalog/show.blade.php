@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? $product->name), 155))
@section('meta_type', 'product')
@section('meta_image', $product->main_image_path ? \Illuminate\Support\Facades\Storage::url($product->main_image_path) : '')
@section('canonical_url', route('products.direct', $product->slug))

@section('content')
@php
    $pricingService = app(\App\Services\Cart\CartPricingService::class);
    $productPricing = $pricingService->calculate($product);
    $variantData = $product->variants->map(function ($variant) use ($product, $pricingService) {
        $pricing = $pricingService->calculate($product, $variant);

        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'availability' => $variant->availability,
            'attributes' => $variant->attributesArray(),
            'base_price' => $pricing['base_price'],
            'final_price' => $pricing['final_price'],
            'image' => $variant->displayImage() ? \Illuminate\Support\Facades\Storage::url($variant->displayImage()) : null,
        ];
    })->values();
    $attributeGroups = [];
    foreach ($variantData as $variantInfo) {
        $attributes = $variantInfo['attributes'];
        if ($attributes === []) {
            $attributes = ['Varian' => $variantInfo['name']];
        }
        foreach ($attributes as $attribute => $value) {
            $attributeGroups[$attribute] ??= [];
            if (! in_array($value, $attributeGroups[$attribute], true)) {
                $attributeGroups[$attribute][] = $value;
            }
        }
    }
    $selectedVariantId = (int) old('variant_id', request()->query('variant', 0));
    $selectedVariant = $variantData->firstWhere('id', $selectedVariantId);
    $availablePrices = $variantData->where('availability', 'tersedia')->pluck('final_price');
    $minimumVariantPrice = $availablePrices->isNotEmpty() ? $availablePrices->min() : null;
    $maximumVariantPrice = $availablePrices->isNotEmpty() ? $availablePrices->max() : null;
    $initialBasePrice = $selectedVariant['base_price'] ?? $productPricing['base_price'];
    $initialFinalPrice = $selectedVariant['final_price'] ?? $productPricing['final_price'];
    $initialImage = $selectedVariant['image'] ?? ($product->main_image_path ? \Illuminate\Support\Facades\Storage::url($product->main_image_path) : null);
    $requiresVariant = $product->type === 'variant';
@endphp
<div class="min-h-screen bg-[#F5F7FA] py-6 sm:py-8">
    <div class="mx-auto max-w-7xl px-4">
        @if ($remainingViews !== null)
            <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm font-semibold text-amber-950" role="status">
                ⚠️ Kuota jelajah katalog kamu tersisa {{ $remainingViews }} dari 10 produk. Yuk buat pesanan pertamamu untuk unlock akses penuh!
            </div>
        @endif
        <article class="grid gap-0 overflow-hidden rounded-2xl border border-border bg-white shadow-sm lg:grid-cols-[48fr_52fr]">
            <div class="p-4 sm:p-6 lg:p-7">
                <div class="relative aspect-square overflow-hidden rounded-xl bg-surface-2">
                    @if ($initialImage)
                        <button id="open-product-lightbox" type="button" class="group absolute inset-0 h-full w-full cursor-zoom-in" aria-label="Perbesar gambar produk">
                            <img id="product-primary-image" src="{{ $initialImage }}" alt="{{ $product->name }}" class="protected-image h-full w-full object-contain" draggable="false">
                            <span class="img-shield-overlay" aria-hidden="true"></span>
                        </button>
                    @else
                        <div class="flex h-full items-center justify-center text-text-light">Gambar belum tersedia</div>
                    @endif
                    <div class="pointer-events-none absolute left-3 top-3 z-10 flex flex-wrap gap-2">
                        @foreach ($product->tags as $tag)
                            @if (in_array(strtoupper($tag->name), ['PRESALE', 'LIMITED'], true))
                                <span class="rounded-full bg-[#0B1B3A] px-3 py-1 text-xs font-bold text-white">{{ strtoupper($tag->name) }}</span>
                            @endif
                        @endforeach
                        @if ($product->hasActiveDiscount())
                            <span class="rounded-full bg-[#E53935] px-3 py-1 text-xs font-bold text-white">DISKON</span>
                        @endif
                    </div>
                    @if ($initialImage)
                        <button id="product-lightbox-button" type="button" class="absolute right-3 top-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-xl shadow hover:bg-surface-2" aria-label="Buka gambar produk">🔍</button>
                    @endif
                </div>
            </div>

            <div class="border-t border-border p-5 sm:p-7 lg:border-l lg:border-t-0 lg:p-8">
                <nav aria-label="Breadcrumb" class="mb-4 text-sm text-text-muted">
                    <a href="{{ route('home') }}" class="hover:text-navy-500">Beranda</a>
                    <span aria-hidden="true"> / </span>
                    @if ($game)
                        <a href="{{ route('games.show', $game) }}" class="hover:text-navy-500">{{ $game->name }}</a>
                    @else
                        <a href="{{ route('catalog.index') }}" class="hover:text-navy-500">Katalog</a>
                    @endif
                    <span aria-hidden="true"> / </span><span>{{ $product->name }}</span>
                </nav>
                @if ($game)
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">[{{ $game->name }}]</p>
                @endif
                <h1 class="mt-1 text-2xl font-bold text-navy-500 sm:text-3xl">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-text-muted">SKU: {{ $product->sku }}</p>

                <div class="mt-5 rounded-xl bg-surface-2 p-4">
                    <div id="product-price-range" class="text-xl font-bold text-primary-700 {{ $requiresVariant && ! $selectedVariant ? '' : 'hidden' }}">
                        @if ($minimumVariantPrice !== null && $minimumVariantPrice !== $maximumVariantPrice)
                            {{ format_rupiah($minimumVariantPrice) }} – {{ format_rupiah($maximumVariantPrice) }}
                        @elseif ($minimumVariantPrice !== null)
                            {{ format_rupiah($minimumVariantPrice) }}
                        @else
                            Stok varian kosong
                        @endif
                    </div>
                    <del id="product-base-price" class="mr-2 text-sm text-text-muted {{ $initialBasePrice > $initialFinalPrice ? '' : 'hidden' }}">{{ format_rupiah($initialBasePrice) }}</del>
                    <span id="product-final-price" class="text-2xl font-bold text-primary-700 {{ $requiresVariant && ! $selectedVariant ? 'hidden' : '' }}">{{ format_rupiah($initialFinalPrice) }}</span>
                    <p id="product-availability" class="mt-2 text-sm font-medium {{ ($selectedVariant['availability'] ?? $product->availability) === 'tersedia' ? 'text-success-700' : 'text-text-muted' }}">
                        {{ ($selectedVariant['availability'] ?? $product->availability) === 'tersedia' ? 'Tersedia' : 'Stok kosong' }}
                    </p>
                </div>

                @if ($attributeGroups !== [])
                    <div class="mt-5 space-y-4" id="variant-options">
                        @foreach ($attributeGroups as $attribute => $values)
                            <fieldset data-attribute-group="{{ $attribute }}">
                                <legend class="mb-2 text-sm font-semibold text-navy-500">{{ $attribute }}</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($values as $value)
                                        @php
                                            $valueAvailable = $variantData->contains(function ($variantInfo) use ($attribute, $value) {
                                                $attributes = $variantInfo['attributes'] ?: ['Varian' => $variantInfo['name']];
                                                return ($attributes[$attribute] ?? null) === $value && $variantInfo['availability'] === 'tersedia';
                                            });
                                            $isInitiallySelected = $selectedVariant && (($selectedVariant['attributes'] ?: ['Varian' => $selectedVariant['name']])[$attribute] ?? null) === $value;
                                        @endphp
                                        <button type="button" data-variant-option data-option-key="{{ $attribute }}" data-option-value="{{ $value }}" @disabled(! $valueAvailable) aria-pressed="{{ $isInitiallySelected ? 'true' : 'false' }}" class="rounded-lg border px-3 py-2 text-sm font-medium transition {{ $isInitiallySelected ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-border bg-white text-text hover:border-primary-400' }} disabled:cursor-not-allowed disabled:opacity-40">
                                            {{ $value }}
                                        </button>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                        <button type="button" id="clear-variant-selection" class="text-sm font-medium text-text-muted underline hover:text-navy-500">✕ Clear</button>
                    </div>
                @endif

                @if ($errors->any())
                    <p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
                @endif

                @auth
                    @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
                        <x-profile-banner />
                    @endif
                    @if ($product->isPurchasable)
                        <form method="POST" action="{{ route('cart.add') }}" class="mt-5 space-y-4" id="product-add-form" data-requires-variant="{{ $requiresVariant ? 'true' : 'false' }}" data-profile-incomplete="{{ auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile() ? 'true' : 'false' }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if ($requiresVariant)
                                <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $selectedVariant['id'] ?? '' }}">
                            @endif
                            <div class="flex gap-3">
                                <div>
                                    <label class="sr-only" for="product-qty">Jumlah</label>
                                    <input id="product-qty" name="qty" type="number" min="1" max="99" value="{{ old('qty', 1) }}" required class="w-24 rounded-lg border-border">
                                </div>
                                <button id="add-to-cart-button" type="submit" @disabled(($requiresVariant && ! $selectedVariant) || (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())) class="flex-1 rounded-lg bg-[#4FC3F7] px-4 py-3 font-bold text-[#0B1B3A] hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-50">
                                    TAMBAH KE KERANJANG
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="mt-5 rounded-lg bg-surface-2 p-3 text-sm text-text-muted">Produk sedang tidak tersedia.</p>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-lg bg-[#4FC3F7] px-5 py-3 font-semibold text-[#0B1B3A] hover:bg-sky-400">Masuk untuk membeli</a>
                @endauth

                <dl class="mt-5 grid gap-2 border-t border-border pt-4 text-sm text-text-muted">
                    <div><dt class="inline font-semibold text-navy-500">SKU:</dt> <dd id="product-sku" class="inline">{{ $selectedVariant['sku'] ?? $product->sku }}</dd></div>
                    <div><dt class="inline font-semibold text-navy-500">Kategori:</dt> <dd class="inline">{{ $product->merchCategories->pluck('name')->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="inline font-semibold text-navy-500">Game:</dt> <dd class="inline">{{ $product->games->pluck('name')->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="inline font-semibold text-navy-500">Tag:</dt> <dd class="inline">{{ $product->tags->pluck('name')->join(', ') ?: '—' }}</dd></div>
                </dl>

                @auth
                    @if (auth()->user()->referral_code)
                        @php
                            $shareBaseUrl = $game ? route('games.products.show', [$game->slug, $product->slug]) : route('products.direct', $product->slug);
                            $shareUrl = $shareBaseUrl.'?ref='.urlencode(auth()->user()->referral_code);
                        @endphp
                        <div class="mt-5 rounded-xl border border-border bg-[#F5F7FA] p-4">
                            <h2 class="font-semibold text-navy-500">Bagikan produk ini</h2>
                            <p class="mt-1 text-sm text-text-muted">Dapatkan +5 koin untuk setiap IP unik dari Indonesia yang membuka link referral-mu.</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <button id="share-product" type="button" data-share-url="{{ $shareUrl }}" data-share-title="{{ $product->name }}" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-navy-500 hover:bg-surface-2">Bagikan link</button>
                                <span id="share-product-feedback" role="status" class="text-sm text-text-muted"></span>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </article>

        <section class="mt-8 rounded-2xl border border-border bg-white p-4 sm:p-6" aria-label="Informasi produk">
            <div role="tablist" aria-label="Detail produk" class="flex gap-6 border-b border-border">
                <button type="button" role="tab" id="product-description-tab" aria-controls="product-description-panel" aria-selected="true" class="border-b-2 border-primary-600 px-1 pb-3 font-semibold text-primary-700">Deskripsi</button>
                <button type="button" role="tab" id="product-info-tab" aria-controls="product-info-panel" aria-selected="false" class="border-b-2 border-transparent px-1 pb-3 font-semibold text-text-muted">Informasi Tambahan</button>
            </div>
            <div id="product-description-panel" role="tabpanel" aria-labelledby="product-description-tab" class="py-5">
                @if ($product->description_image_path)
                    <div class="mx-auto max-w-[800px]">
                        <div class="protected-image-wrap">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->description_image_path) }}" alt="Deskripsi {{ $product->name }}" class="protected-image h-auto w-full" draggable="false">
                            <span class="img-shield-overlay" aria-hidden="true"></span>
                        </div>
                    </div>
                @endif
                @if ($product->description)
                    <div class="mx-auto mt-5 max-w-[800px] whitespace-pre-line text-sm leading-7 text-text">{{ $product->description }}</div>
                @elseif (! $product->description_image_path)
                    <p class="py-8 text-center text-sm text-text-muted">Belum ada deskripsi produk.</p>
                @endif
            </div>
            <div id="product-info-panel" role="tabpanel" aria-labelledby="product-info-tab" class="hidden py-5">
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-semibold text-navy-500">Nama Produk</dt><dd class="mt-1 text-text-muted">{{ $product->name }}</dd></div>
                    <div><dt class="font-semibold text-navy-500">SKU</dt><dd id="product-info-sku" class="mt-1 text-text-muted">{{ $selectedVariant['sku'] ?? $product->sku }}</dd></div>
                    <div><dt class="font-semibold text-navy-500">Game / Developer</dt><dd class="mt-1 text-text-muted">{{ $product->games->pluck('name')->join(', ') }}{{ $product->games->flatMap->developers->pluck('name')->unique()->isNotEmpty() ? ' / '.$product->games->flatMap->developers->pluck('name')->unique()->join(', ') : '' }}</dd></div>
                    <div><dt class="font-semibold text-navy-500">Kategori Merch</dt><dd class="mt-1 text-text-muted">{{ $product->merchCategories->pluck('name')->join(', ') ?: '—' }}</dd></div>
                </dl>
            </div>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-10" aria-label="Produk terkait">
                <h2 class="text-xl font-bold text-navy-500">Produk Terkait</h2>
                <div class="mt-4 flex snap-x gap-4 overflow-x-auto pb-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        <a href="{{ route('products.direct', $relatedProduct->slug) }}" class="w-[40%] shrink-0 snap-start overflow-hidden rounded-xl border border-border bg-white hover:shadow-sm sm:w-[30%] lg:w-[16%]">
                            @if ($relatedProduct->main_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($relatedProduct->main_image_path) }}" alt="{{ $relatedProduct->name }}" class="aspect-square w-full bg-surface-2 object-cover" loading="lazy" draggable="false">
                            @else
                                <div class="flex aspect-square items-center justify-center bg-surface-2 text-xs text-text-muted">Gambar belum tersedia</div>
                            @endif
                            <div class="p-3"><span class="line-clamp-2 block text-sm font-medium text-navy-500">{{ $relatedProduct->name }}</span><span class="mt-2 block text-sm font-bold text-primary-700">{{ $relatedProduct->display_price }}</span></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@if ($initialImage)
    <dialog id="product-lightbox" class="max-h-[92vh] max-w-[92vw] rounded-xl bg-transparent p-0 backdrop:bg-black/80">
        <div class="relative flex max-h-[92vh] max-w-[92vw] items-center justify-center">
            <button type="button" id="close-product-lightbox" class="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white text-xl shadow" aria-label="Tutup gambar">×</button>
            <img id="product-lightbox-image" src="{{ $initialImage }}" alt="{{ $product->name }}" class="protected-image max-h-[90vh] max-w-[90vw] object-contain" draggable="false">
            <span class="img-shield-overlay" aria-hidden="true"></span>
        </div>
    </dialog>
@endif
@endsection

@push('scripts')
    <script>
        const productVariants = @js($variantData);
        const mainImage = document.getElementById('product-primary-image');
        const lightboxImage = document.getElementById('product-lightbox-image');
        const lightbox = document.getElementById('product-lightbox');
        const groups = [...document.querySelectorAll('[data-attribute-group]')];
        const optionButtons = [...document.querySelectorAll('[data-variant-option]')];
        const productForm = document.getElementById('product-add-form');
        const variantInput = document.getElementById('selected-variant-id');
        const addButton = document.getElementById('add-to-cart-button');
        const initialImageUrl = mainImage?.src || '';
        const selectedOptions = {};
        const initialVariant = productVariants.find(variant => variant.id === Number(variantInput?.value));
        if (initialVariant) {
            Object.assign(selectedOptions, initialVariant.attributes);
            if (Object.keys(initialVariant.attributes).length === 0) selectedOptions.Varian = initialVariant.name;
        }

        const setVariantImage = url => {
            if (!mainImage || !lightboxImage) return;
            const imageUrl = url || initialImageUrl;
            mainImage.src = imageUrl;
            lightboxImage.src = imageUrl;
        };
        const formatPrice = amount => new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', maximumFractionDigits: 0
        }).format(amount);
        const matchingVariants = () => productVariants.filter(variant => {
            const attributes = Object.keys(variant.attributes).length ? variant.attributes : { Varian: variant.name };
            return Object.entries(selectedOptions).every(([key, value]) => attributes[key] === value);
        });
        const completeSelection = () => groups.length > 0 && groups.every(group => selectedOptions[group.dataset.attributeGroup]);
        const updateVariantView = () => {
            if (!productForm || !variantInput) return;
            const candidates = matchingVariants();
            const selected = completeSelection() ? candidates.find(variant => variant.availability === 'tersedia') : null;
            variantInput.value = selected ? selected.id : '';
            const range = document.getElementById('product-price-range');
            const basePrice = document.getElementById('product-base-price');
            const finalPrice = document.getElementById('product-final-price');
            const availability = document.getElementById('product-availability');
            const sku = document.getElementById('product-sku');
            const infoSku = document.getElementById('product-info-sku');
            if (selected) {
                range?.classList.add('hidden');
                basePrice.textContent = formatPrice(selected.base_price);
                basePrice.classList.toggle('hidden', selected.base_price <= selected.final_price);
                finalPrice.textContent = formatPrice(selected.final_price);
                finalPrice.classList.remove('hidden');
                availability.textContent = 'Tersedia';
                availability.classList.remove('text-text-muted');
                availability.classList.add('text-success-700');
                if (sku) sku.textContent = selected.sku || '—';
                if (infoSku) infoSku.textContent = selected.sku || '—';
                setVariantImage(selected.image);
            } else {
                range?.classList.remove('hidden');
                basePrice.classList.add('hidden');
                finalPrice.classList.add('hidden');
                availability.textContent = candidates.length ? 'Varian tidak tersedia' : 'Pilih varian';
                availability.classList.remove('text-success-700');
                availability.classList.add('text-text-muted');
                if (sku) sku.textContent = 'Pilih varian';
                if (infoSku) infoSku.textContent = 'Pilih varian';
                setVariantImage(null);
            }
            optionButtons.forEach(button => {
                const selectedOption = selectedOptions[button.dataset.optionKey] === button.dataset.optionValue;
                button.setAttribute('aria-pressed', selectedOption ? 'true' : 'false');
                button.classList.toggle('border-primary-600', selectedOption);
                button.classList.toggle('bg-primary-50', selectedOption);
                button.classList.toggle('text-primary-700', selectedOption);
                button.classList.toggle('border-border', !selectedOption);
            });
            addButton.disabled = productForm.dataset.profileIncomplete === 'true' || !selected;
        };

        optionButtons.forEach(button => button.addEventListener('click', () => {
            const key = button.dataset.optionKey;
            const value = button.dataset.optionValue;
            const previous = selectedOptions[key];
            selectedOptions[key] = value;
            if (matchingVariants().length === 0) {
                if (previous === undefined) delete selectedOptions[key];
                else selectedOptions[key] = previous;
                return;
            }
            updateVariantView();
        }));
        document.getElementById('clear-variant-selection')?.addEventListener('click', () => {
            Object.keys(selectedOptions).forEach(key => delete selectedOptions[key]);
            updateVariantView();
        });
        if (productForm && variantInput) updateVariantView();

        const descriptionTab = document.getElementById('product-description-tab');
        const infoTab = document.getElementById('product-info-tab');
        const descriptionPanel = document.getElementById('product-description-panel');
        const infoPanel = document.getElementById('product-info-panel');
        const setTab = showDescription => {
            descriptionTab?.setAttribute('aria-selected', showDescription ? 'true' : 'false');
            infoTab?.setAttribute('aria-selected', showDescription ? 'false' : 'true');
            descriptionPanel?.classList.toggle('hidden', !showDescription);
            infoPanel?.classList.toggle('hidden', showDescription);
            descriptionTab?.classList.toggle('border-primary-600', showDescription);
            infoTab?.classList.toggle('border-primary-600', !showDescription);
            descriptionTab?.classList.toggle('text-primary-700', showDescription);
            infoTab?.classList.toggle('text-primary-700', !showDescription);
        };
        descriptionTab?.addEventListener('click', () => setTab(true));
        infoTab?.addEventListener('click', () => setTab(false));

        const openLightbox = () => lightbox?.showModal();
        document.getElementById('open-product-lightbox')?.addEventListener('click', openLightbox);
        document.getElementById('product-lightbox-button')?.addEventListener('click', openLightbox);
        document.getElementById('close-product-lightbox')?.addEventListener('click', () => lightbox?.close());
        lightbox?.addEventListener('click', event => {
            if (event.target === lightbox) lightbox.close();
        });

        const shareProductButton = document.getElementById('share-product');
        shareProductButton?.addEventListener('click', () => {
            const url = shareProductButton.dataset.shareUrl;
            const title = shareProductButton.dataset.shareTitle;
            const feedback = document.getElementById('share-product-feedback');

            if (navigator.share) {
                navigator.share({ title, url }).then(
                    () => { feedback.textContent = 'Link berhasil dibagikan.'; },
                    (error) => {
                        if (error.name !== 'AbortError') {
                            feedback.textContent = 'Link tidak dapat dibagikan.';
                        }
                    }
                );
                return;
            }

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(url).then(
                    () => { feedback.textContent = 'Link berhasil disalin.'; },
                    () => { feedback.textContent = 'Link tidak dapat disalin.'; }
                );
                return;
            }

            window.prompt('Salin link produk ini:', url);
        });

        if (typeof window.mercatoriaTrack === 'function') {
            window.mercatoriaTrack('view_product', {
                currency: 'IDR',
                value: @js((float) $product->final_price),
                items: [{
                    item_id: @js($product->sku),
                    item_name: @js($product->name),
                    item_category: @js($game?->name),
                    price: @js((float) $product->final_price),
                    quantity: 1
                }]
            });
        }
    </script>
@endpush

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => \Illuminate\Support\Str::limit(trim(strip_tags($product->description ?? '')), 500),
            'image' => $product->main_image_path ? url(\Illuminate\Support\Facades\Storage::url($product->main_image_path)) : null,
            'sku' => $product->sku,
            'url' => url()->current(),
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => number_format((float) $product->final_price, 2, '.', ''),
                'availability' => $product->isPurchasable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => url()->current(),
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $game?->name ?? 'Katalog', 'item' => $game ? route('games.show', $game) : route('catalog.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => url()->current()],
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush
