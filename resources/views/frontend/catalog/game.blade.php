@extends('layouts.app')

@section('title', $title.' — Mercatoria')

@section('content')
    <nav class="mb-2 flex items-center justify-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">
        <a href="/" class="transition-colors hover:text-ink">Beranda</a>
        <span class="text-ink/35">/</span>
        @if ($developer)
            <a href="/" class="transition-colors hover:text-ink">Developer</a>
        @else
            <a href="/" class="transition-colors hover:text-ink">Produk</a>
        @endif
        <span class="text-ink/35">/</span>
        <span class="text-ink">{{ $title }}</span>
    </nav>

    {{-- [NEW] Header developer --}}
    @if ($developer)
        <div class="mx-auto max-w-xl text-center">
            @if ($developer->logo_path)
                <img src="{{ Storage::url($developer->logo_path) }}" alt="{{ $developer->name }}"
                     class="mx-auto h-16 w-16 rounded-xl border border-rule object-cover">
            @endif
            <h1 class="mt-3 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $developer->name }}</h1>
            <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.16em] text-ink/50">Developer</p>
        </div>
    @else
        <h1 class="text-center font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $title }}</h1>
    @endif

    <div class="mt-6 flex flex-col gap-6 lg:flex-row">
        <aside class="w-full shrink-0 space-y-4 lg:w-1/4">
            {{-- Kategori Game — sembunyikan di halaman developer --}}
            @unless ($developer)
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
            @endunless

            {{-- [NEW] Developer lain --}}
            @if ($developer)
                <div class="rounded-xl border border-rule bg-white p-4">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Developer Lain</h2>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach (($footerDevelopers ?? collect()) as $dev)
                            @if ($dev->id !== $developer->id)
                                <li>
                                    <a href="{{ route('catalog.developer', $dev) }}"
                                       class="block rounded px-2 py-1 text-ink/65 transition-colors hover:bg-paper hover:text-ink">
                                        {{ $dev->name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

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

                <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
                    <select name="tag" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                        <option value="">Semua Tag</option>
                        @foreach ($tags as $t)
                            <option value="{{ $t->slug }}" @selected(request('tag') === $t->slug)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <select name="availability" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                        <option value="">Ketersediaan</option>
                        <option value="tersedia" @selected(request('availability') === 'tersedia')>Tersedia</option>
                        <option value="kosong" @selected(request('availability') === 'kosong')>Kosong</option>
                    </select>
                    <input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="Harga min"
                           class="w-28 rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                    <input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="Harga maks"
                           class="w-28 rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                    <label for="sort" class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Urutkan</label>
                    <select name="sort" id="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <option value="">Relevansi</option>
                        <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                        <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                        <option value="terpopuler" @selected(request('sort') === 'terpopuler')>Terpopuler</option>
                        <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                        <option value="abjad" @selected(request('sort') === 'abjad')>Abjad (A-Z)</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-black px-3 py-1.5 text-sm font-semibold text-white">Terapkan</button>
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