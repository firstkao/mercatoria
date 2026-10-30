@extends('layouts.app')

@section('title', $title.' — Mercatoria')

@section('content')
    <nav class="mb-2 flex items-center justify-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">
        <a href="/" class="transition-colors hover:text-ink">Beranda</a>
        <span class="text-ink/35">/</span>
        <a href="/" class="transition-colors hover:text-ink">Produk</a>
        <span class="text-ink/35">/</span>
        <span class="text-ink">{{ $title }}</span>
    </nav>

    <h1 class="text-center font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $title }}</h1>

    <div class="mt-6 flex flex-col gap-6 lg:flex-row">
        <aside class="w-full shrink-0 space-y-4 lg:w-1/4">
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Kategori Game</h2>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($games as $g)
                        <li>
                            <a href="/{{ $g->slug }}"
                               class="block rounded px-2 py-1 transition-colors hover:bg-paper {{ ($game?->id ?? null) === $g->id ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink' }}">
                                {{ $g->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($game)
                <div class="rounded-xl border border-rule bg-white p-4">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Kategori Merch</h2>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>
                            <a href="/{{ $game->slug }}"
                               class="block rounded px-2 py-1 transition-colors hover:bg-paper {{ ! $activeCategory ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink' }}">
                                Semua
                            </a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="/{{ $game->slug }}/kategori/{{ $category->slug }}"
                                   class="block rounded px-2 py-1 transition-colors hover:bg-paper {{ ($activeCategory?->id ?? null) === $category->id ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink' }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($bestOffers->isNotEmpty())
                <div class="rounded-xl border border-rule bg-white p-4">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Penawaran Terbaik</h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($bestOffers as $offer)
                            <li>
                                <a href="/{{ $offer->game->slug }}/{{ $offer->slug }}" class="flex items-center gap-2 text-sm transition-colors hover:text-ink">
                                    <span class="h-10 w-10 shrink-0 overflow-hidden rounded bg-paper">
                                        @if ($offer->image_path)
                                            <img src="{{ Storage::url($offer->image_path) }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <span class="line-clamp-2 text-xs text-ink/75">{{ $offer->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        <div class="flex-1">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                    Menampilkan
                    {{ $products->total() > 0 ? ($products->firstItem()).'&ndash;'.($products->lastItem()) : 0 }}
                    dari {{ $products->total() }} hasil
                </p>

                <form method="GET" class="flex items-center gap-2 text-sm">
                    <label for="sort" class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Urutkan</label>
                    <select name="sort" id="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <option value="">Relevansi</option>
                        <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                        <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                        <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                    </select>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/45">
                    Tidak ada produk ditemukan.
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :shipping="$shipping" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
