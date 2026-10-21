@extends('layouts.app')

@section('title', 'Hasil pencarian: '.$search)

@section('content')
@php($searchService = app(\App\Services\Product\ProductSearchService::class))
<section class="bg-surface-2">
    <div class="mx-auto max-w-[1200px] px-4 py-8">
        <div class="rounded-xl border border-border bg-white p-5">
            <p class="text-sm text-text-muted">Hasil pencarian untuk</p>
            <h1 class="mt-1 text-2xl font-bold text-navy-500">“{{ $search }}”</h1>
            <p class="mt-1 text-sm text-text-muted">{{ $products->total() }} produk ditemukan (nama produk, varian/karakter, game, dan developer).</p>
        </div>

        <form method="GET" action="{{ route('catalog.search') }}" class="mt-4 flex flex-wrap items-end gap-3 rounded-xl border border-border bg-white p-4">
            <input type="hidden" name="q" value="{{ $search }}">
            <div class="min-w-40 flex-1">
                <label for="game" class="mb-1 block text-xs font-medium">Game</label>
                <select id="game" name="game" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua game</option>
                    @foreach ($games as $catalogGame)
                        <option value="{{ $catalogGame->slug }}" @selected(($filters['game'] ?? '') === $catalogGame->slug)>{{ $catalogGame->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-40 flex-1">
                <label for="category" class="mb-1 block text-xs font-medium">Kategori</label>
                <select id="category" name="category" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $merchCategory)
                        <option value="{{ $merchCategory->slug }}" @selected(($filters['category'] ?? '') === $merchCategory->slug)>{{ $merchCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36 flex-1">
                <label for="availability" class="mb-1 block text-xs font-medium">Ketersediaan</label>
                <select id="availability" name="availability" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua</option>
                    <option value="tersedia" @selected(($filters['availability'] ?? '') === 'tersedia')>Tersedia</option>
                    <option value="kosong" @selected(($filters['availability'] ?? '') === 'kosong')>Stok kosong</option>
                </select>
            </div>
            <div class="min-w-36 flex-1">
                <label for="sort" class="mb-1 block text-xs font-medium">Urutkan</label>
                <select id="sort" name="sort" class="w-full rounded-lg border-border text-sm">
                    @foreach (['latest' => 'Terbaru', 'popular' => 'Terpopuler', 'price_asc' => 'Harga terendah', 'price_desc' => 'Harga tertinggi', 'name' => 'Nama A-Z'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-button type="submit" variant="ghost">Terapkan Filter</x-button>
        </form>

        @if ($products->isEmpty())
            <div class="mt-6 rounded-xl border border-border bg-white p-8 text-center">
                <p class="font-semibold">Produk tidak ditemukan.</p>
                <p class="mt-1 text-sm text-text-muted">Coba kata kunci lain atau ubah filter.</p>
            </div>
        @else
            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                @foreach ($products as $product)
                    @php($productGame = $product->games->first())
                    @php($matchedVariant = $searchService->matchedVariant($product, $search))
                    @php($productUrl = $productGame ? route('games.products.show', [$productGame->slug, $product->slug]) : route('products.direct', $product->slug))
                    <article class="overflow-hidden rounded-xl border border-border bg-white transition hover:shadow-md">
                        <a href="{{ $productUrl }}{{ $matchedVariant ? '?variant='.$matchedVariant->id : '' }}" class="block">
                            @if ($product->main_image_path)
                                <x-protected-image :src="\Illuminate\Support\Facades\Storage::url($product->main_image_path)" :alt="$product->name" loading="lazy" class="aspect-square w-full object-cover" />
                            @else
                                <div class="flex aspect-square items-center justify-center bg-surface-2 text-sm text-text-light">Gambar belum tersedia</div>
                            @endif
                            <div class="p-3">
                                @if ($matchedVariant)
                                    <span class="mb-2 inline-block rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">🎯 Varian: {{ $matchedVariant->name }}</span>
                                @endif
                                <h2 class="line-clamp-2 min-h-10 text-sm font-medium text-text">{{ $product->name }}</h2>
                                <p class="mt-2 text-sm font-semibold text-navy-500">{{ $product->display_price }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
</section>
@endsection
