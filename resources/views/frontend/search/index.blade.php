@extends('layouts.app')

@section('title', 'Hasil Pencarian &mdash; Mercatoria')

@section('content')
    <div class="mx-auto max-w-[1200px]">
        <div class="rounded-xl border border-rule bg-white p-6">
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pencarian</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">
                Hasil Pencarian
                @if ($q !== '') untuk &ldquo;{{ $q }}&rdquo; @endif
            </h1>

            <form action="{{ route('search') }}" method="GET" class="mt-4 flex gap-2">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, karakter, game..."
                       class="flex-1 rounded-lg border border-rule bg-white px-4 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                    Cari
                </button>
            </form>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                    @if ($results->total() > 0)
                        Menampilkan {{ $results->firstItem() }}–{{ $results->lastItem() }} dari {{ $results->total() }} hasil
                    @else
                        Tidak ada hasil ditemukan.
                    @endif
                </p>
            </div>

            @if ($results->total() === 0 && isset($games))
                <div class="mt-4">
                    <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/50">Coba jelajahi game</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($games->take(8) as $g)
                            <a href="{{ route('search', ['game_id' => $g->id]) }}" class="rounded-full border border-rule px-3 py-1 text-xs text-ink/75 transition-colors hover:border-ink hover:text-ink">
                                {{ $g->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <form method="GET" class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-rule bg-white p-3">
            <input type="hidden" name="q" value="{{ $q }}">

            <select name="game_id" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Semua Game</option>
                @foreach ($games as $g)
                    <option value="{{ $g->id }}" @selected($filters['game_id'] == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>

            <select name="merch_category_id" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Kategori Merch</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['merch_category_id'] == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="tag" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Tag</option>
                @foreach ($tags as $t)
                    <option value="{{ $t->slug }}" @selected($filters['tag'] === $t->slug)>{{ $t->name }}</option>
                @endforeach
            </select>

            <label for="sort" class="ml-auto font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Urutkan</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Relevansi</option>
                <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
            </select>
        </form>

        @if ($results->isNotEmpty())
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                @foreach ($results as $product)
                    <x-product-card
                        :product="$product"
                        :shipping="$shipping"
                        :matched-variant="$matchedVariants[$product->id]"
                    />
                @endforeach
            </div>

            <div class="mt-6">
                {{ $results->links() }}
            </div>
        @endif
    </div>
@endsection
