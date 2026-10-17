@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? $product->name), 155))

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
