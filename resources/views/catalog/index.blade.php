@extends('layouts.app')

@section('title', $title)
@section('meta_description', \Illuminate\Support\Str::limit(trim(strip_tags($game?->description ?? $developer?->description ?? $title)), 155))

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => url()->current()],
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')
@php
    $activeSort = $filters['sort'] ?? 'default';
    $sortOptions = ['default' => 'Pengurutan standar', 'price_asc' => 'Termurah', 'price_desc' => 'Termahal', 'popular' => 'Terpopuler', 'latest' => 'Terbaru', 'name' => 'A-Z'];
    $currentMin = (int) ($filters['min_price'] ?? $priceMin);
    $currentMax = (int) ($filters['max_price'] ?? $priceMax);
    $sectionLabel = $developer ? 'Developer' : 'Produk';
    $selectClass = 'w-full border-border text-xs';
@endphp
<div class="bg-surface-2">
<form method="GET" action="{{ url()->current() }}" id="catalog-form" class="mx-auto grid max-w-[1200px] gap-6 px-4 py-6 md:px-6 lg:grid-cols-[270px_minmax(0,1fr)] lg:px-8">
    @if ($search)
        <input type="hidden" name="q" value="{{ $search }}">
    @endif

    <div class="space-y-4">
        <div id="filter-sheet" class="fixed inset-0 z-50 hidden bg-black/40 lg:static lg:z-auto lg:block lg:bg-transparent" data-filter-sheet>
            <div class="absolute inset-x-0 bottom-0 max-h-[85vh] space-y-4 overflow-y-auto bg-surface-2 p-4 lg:static lg:max-h-none lg:overflow-visible lg:bg-transparent lg:p-0">
                <div class="flex items-center justify-between lg:hidden">
                    <h2 class="font-bold">Filter &amp; Kategori</h2>
                    <button type="button" data-filter-close class="px-2 text-xl" aria-label="Tutup filter">✕</button>
                </div>

                <section class="border border-border bg-white p-4" aria-labelledby="game-category-title">
                    <h2 id="game-category-title" class="border-b border-border pb-2 text-sm font-bold text-[#111]">Kategori Game</h2>
                    <ul class="mt-3 space-y-1.5 text-[12.5px]">
                        @forelse ($games as $catalogGame)
                            <li>
                                <a href="{{ route('games.show', $catalogGame->slug) }}"
                                    @class(['flex justify-between gap-2 hover:text-primary-400', 'font-bold text-primary-400' => $game?->is($catalogGame), 'text-text' => ! $game?->is($catalogGame)])>
                                    <span>{{ $catalogGame->name }}</span>
                                    <span class="text-text-light">({{ $catalogGame->products_count }})</span>
                                </a>
                            </li>
                        @empty
                            <li class="text-text-light">Belum ada game.</li>
                        @endforelse
                    </ul>

                    <h3 class="mt-4 border-t border-border pt-3 text-sm font-bold text-[#111]">Kategori Merch &amp; Tag</h3>
                    <div class="mt-2 space-y-2">
                        <select name="category" aria-label="Kategori Merch" data-auto-submit class="{{ $selectClass }}">
                            <option value="">Semua Merch</option>
                            @foreach ($categories as $merchCategory)
                                <option value="{{ $merchCategory->slug }}" @selected(($filters['category'] ?? '') === $merchCategory->slug)>{{ $merchCategory->name }}</option>
                            @endforeach
                        </select>
                        <select name="tag" aria-label="Tag" data-auto-submit class="{{ $selectClass }}">
                            <option value="">Semua Tag</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->slug }}" @selected(($filters['tag'] ?? '') === $tag->slug)>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <select name="availability" aria-label="Ketersediaan" data-auto-submit class="{{ $selectClass }}">
                            <option value="">Semua Ketersediaan</option>
                            <option value="tersedia" @selected(($filters['availability'] ?? '') === 'tersedia')>Tersedia</option>
                            <option value="kosong" @selected(($filters['availability'] ?? '') === 'kosong')>Kosong</option>
                        </select>
                    </div>
                </section>

                <section class="border border-border bg-white p-4" aria-labelledby="price-filter-title">
                    <h2 id="price-filter-title" class="border-b border-border pb-2 text-sm font-bold text-[#111]">Filter Harga</h2>
                    <div class="mt-4" data-price-slider data-min="{{ $priceMin }}" data-max="{{ $priceMax }}">
                        <div class="relative h-6">
                            <div class="absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 bg-border"></div>
                            <input type="range" data-range-low min="{{ $priceMin }}" max="{{ $priceMax }}" step="1000" value="{{ $currentMin }}" aria-label="Harga minimum"
                                class="pointer-events-none absolute inset-0 h-6 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:bg-primary-300 [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary-300">
                            <input type="range" data-range-high min="{{ $priceMin }}" max="{{ $priceMax }}" step="1000" value="{{ $currentMax }}" aria-label="Harga maksimum"
                                class="pointer-events-none absolute inset-0 h-6 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:bg-primary-300 [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary-300">
                        </div>
                        <input type="hidden" name="min_price" data-price-min-input value="{{ $currentMin }}">
                        <input type="hidden" name="max_price" data-price-max-input value="{{ $currentMax }}">
                        <div class="mt-2 flex justify-between text-xs text-text-muted">
                            <span data-price-low-label>Rp{{ number_format($currentMin, 0, ',', '.') }}</span>
                            <span data-price-high-label>Rp{{ number_format($currentMax, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <button type="submit" class="mt-3 block w-full bg-primary-300 py-2 text-xs font-bold uppercase tracking-wide text-white hover:bg-primary-400">Terapkan</button>
                </section>
            </div>
        </div>

        <div class="hidden lg:block">
            @include('catalog.partials.side-widgets', ['suffix' => 'desktop'])
        </div>
    </div>

    <div class="min-w-0 bg-white p-4 md:p-6">
        <header class="text-center">
            <h1 class="text-2xl font-bold text-[#111]">{{ $title }}</h1>
            <nav aria-label="Breadcrumb" class="mt-1 text-xs text-text-muted">
                <a href="{{ route('home') }}" class="hover:text-primary-400">Beranda</a>
                <span aria-hidden="true">»</span>
                <a href="{{ $developer ? route('home') : route('catalog.index') }}" class="hover:text-primary-400">{{ $sectionLabel }}</a>
                <span aria-hidden="true">»</span>
                <span>{{ $title }}</span>
            </nav>
            @if ($game?->description)
                <p class="mx-auto mt-2 max-w-3xl text-sm text-text-muted">{{ $game->description }}</p>
            @elseif ($developer?->description)
                <p class="mx-auto mt-2 max-w-3xl text-sm text-text-muted">{{ $developer->description }}</p>
            @endif
        </header>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-sm">
            <p class="text-text-muted">Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} hasil</p>
            <div class="flex items-center gap-2">
                <button type="button" data-filter-open class="border border-border px-3 py-1.5 text-xs lg:hidden">☰ Filter &amp; Kategori</button>
                <label for="sort" class="sr-only">Pengurutan</label>
                <select id="sort" name="sort" data-auto-submit class="border-border text-xs">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($activeSort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="mt-6 border border-dashed border-border px-6 py-12 text-center">
                <h2 class="text-lg font-semibold">Produk belum tersedia</h2>
                <p class="mt-1 text-sm text-text-muted">Coba ubah filter atau pilih kategori game lain.</p>
            </div>
        @else
            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6" data-catalog-grid>
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <x-pagination-squares :paginator="$products" />
        @endif
    </div>

    <div class="lg:hidden">
        @include('catalog.partials.side-widgets', ['suffix' => 'mobile'])
    </div>
</form>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('catalog-form');
            if (!form) return;
            form.querySelectorAll('[data-auto-submit]').forEach(field => field.addEventListener('change', () => form.requestSubmit()));

            const sheet = form.querySelector('[data-filter-sheet]');
            form.querySelector('[data-filter-open]')?.addEventListener('click', () => sheet.classList.remove('hidden'));
            form.querySelector('[data-filter-close]')?.addEventListener('click', () => sheet.classList.add('hidden'));

            const slider = form.querySelector('[data-price-slider]');
            if (!slider) return;
            const low = slider.querySelector('[data-range-low]');
            const high = slider.querySelector('[data-range-high]');
            const minInput = slider.querySelector('[data-price-min-input]');
            const maxInput = slider.querySelector('[data-price-max-input]');
            const format = value => 'Rp' + Number(value).toLocaleString('id-ID');
            const sync = () => {
                if (Number(low.value) > Number(high.value)) [low.value, high.value] = [high.value, low.value];
                minInput.value = low.value;
                maxInput.value = high.value;
                slider.querySelector('[data-price-low-label]').textContent = format(low.value);
                slider.querySelector('[data-price-high-label]').textContent = format(high.value);
            };
            low.addEventListener('input', sync);
            high.addEventListener('input', sync);
            form.addEventListener('submit', () => {
                if (minInput.value === slider.dataset.min) minInput.disabled = true;
                if (maxInput.value === slider.dataset.max) maxInput.disabled = true;
            });
        })();
    </script>
@endpush
