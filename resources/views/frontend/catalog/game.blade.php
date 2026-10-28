@extends('layouts.app')

@section('title', $title.' — Mercatoria')

@section('content')
    <nav class="mb-4 text-sm text-gray-500">
        <a href="/" class="hover:text-black">Beranda</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $title }}</span>
    </nav>

    <h1 class="text-2xl font-semibold">{{ $title }}</h1>

    <div class="mt-6 flex flex-col gap-6 lg:flex-row">
        <aside class="w-full shrink-0 space-y-4 lg:w-1/4">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold">Kategori Game</h2>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($games as $g)
                        <li>
                            <a href="/{{ $g->slug }}"
                               class="block rounded px-2 py-1 hover:bg-gray-50 {{ ($game?->id ?? null) === $g->id ? 'font-semibold text-black' : 'text-gray-600' }}">
                                {{ $g->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($game)
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <h2 class="text-sm font-semibold">Kategori Merch</h2>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>
                            <a href="/{{ $game->slug }}"
                               class="block rounded px-2 py-1 hover:bg-gray-50 {{ ! $activeCategory ? 'font-semibold text-black' : 'text-gray-600' }}">
                                Semua
                            </a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="/{{ $game->slug }}/kategori/{{ $category->slug }}"
                                   class="block rounded px-2 py-1 hover:bg-gray-50 {{ ($activeCategory?->id ?? null) === $category->id ? 'font-semibold text-black' : 'text-gray-600' }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($bestOffers->isNotEmpty())
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <h2 class="text-sm font-semibold">Penawaran Terbaik</h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($bestOffers as $offer)
                            <li>
                                <a href="/{{ $offer->game->slug }}/{{ $offer->slug }}" class="flex items-center gap-2 text-sm hover:text-black">
                                    <span class="h-10 w-10 shrink-0 overflow-hidden rounded bg-gray-100">
                                        @if ($offer->image_path)
                                            <img src="{{ Storage::url($offer->image_path) }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <span class="line-clamp-2 text-xs">{{ $offer->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        <div class="flex-1">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-500">
                    Menampilkan
                    {{ $products->total() > 0 ? ($products->firstItem()).'–'.($products->lastItem()) : 0 }}
                    dari {{ $products->total() }} hasil
                </p>

                <form method="GET" class="flex items-center gap-2 text-sm">
                    <label for="sort" class="text-gray-500">Urutkan</label>
                    <select name="sort" id="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-black focus:outline-none">
                        <option value="">Pengurutan standar</option>
                        <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                        <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                    </select>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="rounded-xl bg-white p-10 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-100">
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
