@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? $product->name), 155))
@section('meta_type', 'product')
@section('meta_image', $product->main_image_path ? \Illuminate\Support\Facades\Storage::url($product->main_image_path) : '')
@section('canonical_url', route('products.direct', $product->slug))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-text-muted">
        <a href="{{ route('home') }}" class="hover:text-navy-500">Beranda</a>
        <span aria-hidden="true"> / </span>
        @if ($game)
            <a href="{{ route('games.show', $game) }}" class="hover:text-navy-500">{{ $game->name }}</a>
        @else
            <a href="{{ route('catalog.index') }}" class="hover:text-navy-500">Katalog</a>
        @endif
        <span aria-hidden="true"> / </span><span>{{ $product->name }}</span>
    </nav>

    <article class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-border bg-surface-2">
            @if ($product->main_image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->main_image_path) }}" alt="{{ $product->name }}" class="max-h-[640px] w-full object-contain">
            @else
                <div class="flex aspect-square items-center justify-center text-text-light">Gambar belum tersedia</div>
            @endif
        </div>

        <div>
            <div class="flex flex-wrap gap-2">
                @foreach ($product->tags as $tag)
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">{{ $tag->name }}</span>
                @endforeach
                @foreach ($product->merchCategories as $merchCategory)
                    <span class="rounded-full bg-surface-2 px-3 py-1 text-xs text-text-muted">{{ $merchCategory->name }}</span>
                @endforeach
            </div>
            <h1 class="mt-4 text-3xl font-bold text-navy-500">{{ $product->name }}</h1>
            @if ($game)
                <p class="mt-2 text-sm text-text-muted">{{ $game->name }}</p>
            @endif
            <p class="mt-5 text-2xl font-bold text-text">{{ $product->display_price }}</p>
            @if ($errors->any())
                <p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
            @endif
            <p class="mt-2 text-sm {{ $product->availability === 'tersedia' ? 'text-success-700' : 'text-text-muted' }}">
                {{ $product->availability === 'tersedia' ? 'Tersedia' : 'Stok kosong' }}
            </p>

            @if ($product->variants->isNotEmpty())
                <section class="mt-6">
                    <h2 class="font-semibold">Varian</h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($product->variants as $variant)
                            <li class="rounded-lg border border-border p-3">
                                <span class="font-medium">{{ $variant->name }}</span>
                                @if ($variant->availability !== 'tersedia')
                                    <span class="text-text-muted"> · Stok kosong</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($product->isPurchasable)
                @auth
                    <form method="POST" action="{{ route('cart.add') }}" class="mt-6 space-y-3 rounded-xl border border-border bg-white p-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if ($product->type === 'variant')
                            <label for="variant_id" class="block text-sm font-medium">Pilih varian</label>
                            <select id="variant_id" name="variant_id" required class="w-full rounded-lg border-border">
                                <option value="">Pilih varian</option>
                                @foreach ($product->variants->where('availability', 'tersedia') as $variant)
                                    <option value="{{ $variant->id }}" @selected(old('variant_id') == $variant->id)>
                                        {{ $variant->name }}{{ $variant->price > 0 ? ' · '.format_rupiah($variant->price) : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <div class="flex gap-3">
                            <label class="sr-only" for="product-qty">Jumlah</label>
                            <input id="product-qty" name="qty" type="number" min="1" max="99" value="{{ old('qty', 1) }}" required class="w-24 rounded-lg border-border">
                            <button type="submit" @disabled($product->type === 'variant' && $product->variants->where('availability', 'tersedia')->isEmpty()) class="flex-1 rounded-lg bg-primary-600 px-4 py-3 font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-6 inline-flex rounded-lg bg-primary-600 px-5 py-3 font-semibold text-white hover:bg-primary-700">Masuk untuk membeli</a>
                @endauth
            @endif

            @if ($product->description)
                <section class="prose prose-sm mt-8 max-w-none">
                    <h2>Deskripsi</h2>
                    <p>{{ $product->description }}</p>
                </section>
            @endif
        </div>
    </article>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-bold text-navy-500">Produk terkait</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                    <a href="{{ route('products.direct', $relatedProduct->slug) }}" class="rounded-xl border border-border bg-white p-4 hover:shadow-sm">
                        <span class="font-medium">{{ $relatedProduct->name }}</span>
                        <span class="mt-2 block text-sm text-text-muted">{{ $relatedProduct->display_price }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    <script>
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
