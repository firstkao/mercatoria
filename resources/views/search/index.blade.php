@extends('layouts.app')

@section('title', 'Hasil pencarian: '.$search)

@section('content')
@php
    $searchService = app(\App\Services\Product\ProductSearchService::class);
    $activeSort = $filters['sort'] ?? 'default';
    $sortOptions = ['default' => 'Pengurutan standar', 'price_asc' => 'Termurah', 'price_desc' => 'Termahal', 'popular' => 'Terpopuler', 'latest' => 'Terbaru', 'name' => 'Abjad'];
    $activeFilters = collect(['game', 'category', 'tag', 'availability', 'min_price', 'max_price'])
        ->filter(fn ($key) => filled($filters[$key] ?? null))
        ->count();
    $pill = 'rounded-full border border-border bg-white py-1.5 pl-3 pr-8 text-xs';
    $total = $products->total();
@endphp
<div class="bg-surface-2">
<div class="mx-auto max-w-[1200px] px-4 py-6 md:px-6 lg:px-8">
    <form method="GET" action="{{ route('catalog.search') }}" id="search-form" class="border border-border bg-white p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <h1 class="text-base font-bold uppercase text-[#111] md:text-lg">🔍 Hasil Pencarian untuk: "{{ $search }}"</h1>
            <nav aria-label="Breadcrumb" class="text-xs text-text-muted">
                <a href="{{ route('home') }}" class="hover:text-primary-400">Beranda</a> » Pencarian » {{ $search }}
            </nav>
        </div>

        <div class="mt-3 flex gap-2">
            <div class="relative min-w-0 flex-1">
                <label for="search-q" class="sr-only">Kata kunci pencarian</label>
                <input id="search-q" name="q" type="search" value="{{ $search }}" maxlength="100" required
                    class="w-full border-border pl-9 pr-9 text-sm focus:border-primary-300 focus:ring-primary-300">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm" aria-hidden="true">🔍</span>
                <button type="button" id="search-clear" aria-label="Hapus kata kunci" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-text-muted">✕</button>
            </div>
            <button type="submit" class="bg-primary-300 px-5 text-xs font-bold uppercase text-white hover:bg-primary-400">Cari</button>
        </div>
        <p class="mt-2 text-xs text-text-muted">ℹ️ Menampilkan produk dengan nama produk maupun nama varian/karakter yang cocok dengan "{{ $search }}".</p>

        <div class="mt-4 border-t border-border pt-4">
            <div id="quick-filter" class="fixed inset-0 z-50 hidden bg-black/40 lg:static lg:z-auto lg:block lg:bg-transparent">
                <div class="absolute inset-x-0 bottom-0 space-y-3 bg-white p-4 lg:static lg:flex lg:flex-wrap lg:items-center lg:gap-2 lg:space-y-0 lg:p-0">
                    <div class="flex items-center justify-between lg:hidden">
                        <h2 class="font-bold">Filter Cepat</h2>
                        <button type="button" data-filter-close class="px-2 text-xl" aria-label="Tutup filter">✕</button>
                    </div>
                    <span class="hidden text-[11px] font-bold uppercase text-text-muted lg:inline">Filter cepat:</span>
                    <select name="game" aria-label="Game" data-auto-submit class="{{ $pill }} w-full lg:w-auto">
                        <option value="">🎮 Semua Game ({{ $total }})</option>
                        @foreach ($games as $catalogGame)
                            <option value="{{ $catalogGame->slug }}" @selected(($filters['game'] ?? '') === $catalogGame->slug)>{{ $catalogGame->name }}</option>
                        @endforeach
                    </select>
                    <select name="category" aria-label="Kategori Merch" data-auto-submit class="{{ $pill }} w-full lg:w-auto">
                        <option value="">🏷️ Kategori Merch</option>
                        @foreach ($categories as $merchCategory)
                            <option value="{{ $merchCategory->slug }}" @selected(($filters['category'] ?? '') === $merchCategory->slug)>{{ $merchCategory->name }}</option>
                        @endforeach
                    </select>
                    <select name="tag" aria-label="Tag" data-auto-submit class="{{ $pill }} w-full lg:w-auto">
                        <option value="">✨ Tag: Semua</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->slug }}" @selected(($filters['tag'] ?? '') === $tag->slug)>Tag: {{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <select name="availability" aria-label="Ketersediaan" data-auto-submit class="{{ $pill }} w-full lg:w-auto">
                        <option value="">Ketersediaan</option>
                        <option value="tersedia" @selected(($filters['availability'] ?? '') === 'tersedia')>Tersedia</option>
                        <option value="kosong" @selected(($filters['availability'] ?? '') === 'kosong')>Kosong</option>
                    </select>
                    <details class="relative w-full lg:w-auto" @if (filled($filters['min_price'] ?? null) || filled($filters['max_price'] ?? null)) open @endif>
                        <summary class="{{ $pill }} cursor-pointer list-none">💰 Harga ▾</summary>
                        <div class="mt-2 flex items-center gap-2 border border-border bg-white p-3 lg:absolute lg:z-10 lg:w-72 lg:shadow-md">
                            <input type="number" name="min_price" min="0" step="1000" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min" aria-label="Harga minimum" class="w-full border-border text-xs">
                            <span>–</span>
                            <input type="number" name="max_price" min="0" step="1000" value="{{ $filters['max_price'] ?? '' }}" placeholder="Maks" aria-label="Harga maksimum" class="w-full border-border text-xs">
                            <button type="submit" class="bg-primary-300 px-3 py-2 text-xs font-bold text-white">OK</button>
                        </div>
                    </details>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-3 text-sm">
                <p class="text-text-muted">
                    @if ($total <= $products->perPage())
                        Menampilkan semua {{ $total }} hasil
                    @else
                        Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $total }} hasil
                    @endif
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" data-filter-open class="border border-border px-3 py-1.5 text-xs lg:hidden">⚡ Filter Cepat ({{ $activeFilters }})</button>
                    <label for="sort" class="sr-only">Pengurutan</label>
                    <select id="sort" name="sort" data-auto-submit class="border-border text-xs">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($activeSort === $value)>{{ $value === 'default' ? 'Pengurutan standar' : $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="mx-auto mt-6 max-w-xl border border-border bg-white p-8 text-center" data-search-empty>
                <p class="text-6xl" aria-hidden="true">🔍</p>
                <p class="mt-3 font-semibold">Tidak ada produk atau varian yang cocok dengan "{{ $search }}"</p>
                <div class="mt-4 text-left text-sm text-text-muted">
                    <p class="font-semibold">Tips Pencarian:</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li>Coba ketik nama karakter (mis. Tartaglia, Vyn)</li>
                        <li>Coba ketik judul game atau jenis merch</li>
                    </ul>
                </div>
                <div class="mt-5 flex flex-wrap justify-between gap-3">
                    <a href="{{ route('catalog.search', ['q' => $search]) }}" class="border border-border px-4 py-2 text-xs font-bold">Reset Filter</a>
                    <a href="{{ route('home') }}#game-favorit" class="bg-primary-300 px-4 py-2 text-xs font-bold text-white hover:bg-primary-400">Jelajahi Semua Game →</a>
                </div>
            </div>
        @else
            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6" data-search-grid>
                @foreach ($products as $product)
                    <x-product-card :product="$product" :matched-variant="$searchService->matchedVariant($product, $search)" />
                @endforeach
            </div>
            <x-pagination-squares :paginator="$products" />
        @endif
    </form>
</div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('search-form');
            if (!form) return;
            const input = document.getElementById('search-q');
            form.querySelectorAll('[data-auto-submit]').forEach(field => field.addEventListener('change', () => form.requestSubmit()));
            document.getElementById('search-clear')?.addEventListener('click', () => { input.value = ''; input.focus(); });
            const sheet = document.getElementById('quick-filter');
            form.querySelector('[data-filter-open]')?.addEventListener('click', () => sheet.classList.remove('hidden'));
            form.querySelector('[data-filter-close]')?.addEventListener('click', () => sheet.classList.add('hidden'));
        })();
    </script>
@endpush
