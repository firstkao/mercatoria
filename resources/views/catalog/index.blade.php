@extends('layouts.app')

@section('title', $title)

@section('content')
<section class="bg-surface-2">
    <div class="mx-auto max-w-7xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="mb-3 text-sm text-text-muted">
            <a href="{{ route('home') }}" class="hover:text-navy-500">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('catalog.index') }}" class="hover:text-navy-500">Katalog</a>
            @if ($game)
                <span aria-hidden="true"> / </span><span>{{ $game->name }}</span>
            @endif
            @if ($category)
                <span aria-hidden="true"> / </span><span>{{ $category->name }}</span>
            @endif
            @if ($developer)
                <span aria-hidden="true"> / </span><span>{{ $developer->name }}</span>
            @endif
        </nav>
        <h1 class="text-3xl font-bold text-navy-500">{{ $title }}</h1>
        @if ($game?->description)
            <p class="mt-2 max-w-3xl text-text-muted">{{ $game->description }}</p>
        @elseif ($developer?->description)
            <p class="mt-2 max-w-3xl text-text-muted">{{ $developer->description }}</p>
        @endif

        <form method="GET" action="{{ route('catalog.search') }}" class="mt-5 flex max-w-2xl gap-2">
            <label for="catalog-search" class="sr-only">Cari produk atau SKU</label>
            <input id="catalog-search" name="q" value="{{ $search }}" type="search" maxlength="100"
                   placeholder="Cari merchandise atau SKU..."
                   class="min-w-0 flex-1 rounded-lg border-border bg-white focus:border-primary-400 focus:ring-primary-400">
            <x-button type="submit">Cari</x-button>
        </form>
    </div>
</section>

<div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 lg:grid-cols-[240px_minmax(0,1fr)]">
    <aside class="space-y-5">
        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="font-semibold">Game</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($games as $catalogGame)
                    <li>
                        <a href="{{ route('games.show', $catalogGame) }}" class="flex justify-between gap-2 text-text-muted hover:text-navy-500">
                            <span>{{ $catalogGame->name }}</span>
                            <span class="text-text-light">{{ $catalogGame->products_count }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-text-light">Belum ada game.</li>
                @endforelse
            </ul>
        </section>

        @if ($developers->isNotEmpty())
            <section class="rounded-xl border border-border bg-white p-4">
                <h2 class="font-semibold">Developer</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($developers as $catalogDeveloper)
                        <li>
                            <a href="{{ route('developers.show', $catalogDeveloper->slug) }}" class="text-text-muted hover:text-navy-500">
                                {{ $catalogDeveloper->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($bestOffers->isNotEmpty())
            <section class="rounded-xl border border-border bg-white p-4">
                <h2 class="font-semibold">Penawaran Terbaik</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($bestOffers as $offer)
                        <li>
                            <a href="{{ route('products.direct', $offer->slug) }}" class="font-medium text-navy-500 hover:underline">{{ $offer->name }}</a>
                            <p class="text-text-muted">{{ $offer->display_price }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </aside>

    <section>
        <form method="GET" action="{{ url()->current() }}" class="mb-5 grid gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-2 xl:grid-cols-7">
            @if ($search)
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            <div>
                <label for="sort" class="mb-1 block text-xs font-medium text-text-muted">Urutkan</label>
                <select id="sort" name="sort" class="w-full rounded-lg border-border text-sm">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Terbaru</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Termurah</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Termahal</option>
                    <option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Terpopuler</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Abjad</option>
                </select>
            </div>
            <div>
                <label for="tag" class="mb-1 block text-xs font-medium text-text-muted">Tag</label>
                <select id="tag" name="tag" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua tag</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->slug }}" @selected(($filters['tag'] ?? '') === $tag->slug)>{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category" class="mb-1 block text-xs font-medium text-text-muted">Kategori</label>
                <select id="category" name="category" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $merchCategory)
                        <option value="{{ $merchCategory->slug }}" @selected(($filters['category'] ?? '') === $merchCategory->slug)>{{ $merchCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="availability" class="mb-1 block text-xs font-medium text-text-muted">Ketersediaan</label>
                <select id="availability" name="availability" class="w-full rounded-lg border-border text-sm">
                    <option value="">Semua</option>
                    <option value="tersedia" @selected(($filters['availability'] ?? '') === 'tersedia')>Tersedia</option>
                    <option value="kosong" @selected(($filters['availability'] ?? '') === 'kosong')>Kosong</option>
                </select>
            </div>
            <div>
                <label for="min_price" class="mb-1 block text-xs font-medium text-text-muted">Harga minimum (Rp)</label>
                <input id="min_price" name="min_price" type="number" min="0" step="1000" value="{{ $filters['min_price'] ?? '' }}"
                       class="w-full rounded-lg border-border text-sm">
            </div>
            <div>
                <label for="max_price" class="mb-1 block text-xs font-medium text-text-muted">Harga maksimum (Rp)</label>
                <input id="max_price" name="max_price" type="number" min="0" step="1000" value="{{ $filters['max_price'] ?? '' }}"
                       class="w-full rounded-lg border-border text-sm">
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="ghost" class="w-full">Terapkan Filter</x-button>
            </div>
        </form>

        <p class="mb-4 text-sm text-text-muted">
            Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} hasil
        </p>

        @if ($products->isEmpty())
            <div class="rounded-xl border border-dashed border-border bg-white px-6 py-12 text-center">
                <h2 class="text-lg font-semibold">Produk belum tersedia</h2>
                <p class="mt-1 text-sm text-text-muted">Coba ubah kata kunci atau filter pencarian.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @php($productGame = $product->games->first())
                    <article class="overflow-hidden rounded-xl border border-border bg-white transition hover:shadow-md">
                        <a href="{{ $productGame ? route('games.products.show', [$productGame->slug, $product->slug]) : route('products.direct', $product->slug) }}"
                           class="block">
                            @if ($product->main_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->main_image_path) }}" alt="{{ $product->name }}" loading="lazy" class="aspect-square w-full object-cover">
                            @else
                                <div class="flex aspect-square items-center justify-center bg-surface-2 text-sm text-text-light">Gambar belum tersedia</div>
                            @endif
                            <div class="p-3">
                                @if ($product->tags->isNotEmpty())
                                    <span class="mb-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold" style="background-color: {{ $product->tags->first()->color }}20; color: {{ $product->tags->first()->color }}">
                                        {{ $product->tags->first()->name }}
                                    </span>
                                @endif
                                <h2 class="line-clamp-2 min-h-10 font-medium text-text">{{ $product->name }}</h2>
                                <p class="mt-2 font-semibold text-navy-500">{{ $product->display_price }}</p>
                                <p class="mt-1 text-xs {{ $product->availability === 'tersedia' ? 'text-success-700' : 'text-text-muted' }}">
                                    {{ $product->availability === 'tersedia' ? 'Tersedia' : 'Stok kosong' }}
                                </p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
</div>
@endsection
