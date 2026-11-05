@extends('layouts.app')

@section('title', 'Hasil Pencarian — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Pencarian — CSS biasa (tidak bergantung build Tailwind), selaras dengan au-* & hp-*/pp-*. */
        .sr-wrap, .sr-wrap *, .sr-wrap *::before, .sr-wrap *::after { box-sizing: border-box; }
        .sr-wrap { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .sr-wrap a { text-decoration: none; }

        .sr-head { padding: 36px 40px 40px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }

        .sr-search { display: flex; gap: 10px; margin-top: 24px; }
        .sr-search .au-input { flex: 1; margin-top: 0; }
        .sr-search .au-btn { display: inline-block; width: auto; height: 42px; padding: 0 28px; line-height: 40px; }

        .sr-count {
            margin: 24px 0 0; font-size: 11px; line-height: 16px; font-weight: 500;
            letter-spacing: .14em; text-transform: uppercase; color: #888;
        }

        .sr-suggest { margin: 20px 0 0; }
        .sr-suggest-h {
            margin: 0; font-size: 11px; line-height: 16px; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase; color: #333;
        }
        .sr-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .sr-chip {
            display: inline-block; height: 26px; padding: 0 12px;
            border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
            font-size: 11px; line-height: 24px; color: #333;
        }
        .sr-chip:hover { border-color: #000; }

        .sr-filters {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            margin: 16px 0 0; padding: 14px 16px;
            background: #fff; border: 1px solid #e1e1e1; border-radius: 0;
        }
        .sr-sel {
            display: inline-block; height: 34px; margin: 0; padding: 0 10px;
            border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
            font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #333; outline: 0; transition: border-color .15s;
        }
        .sr-sel:focus { border-color: #000; }
        .sr-narrow { width: 112px; }
        .sr-sort-label { margin: 0 0 0 auto; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }

        .sr-grid {
            display: grid; grid-template-columns: repeat(6, minmax(0, 1fr));
            column-gap: 16px; row-gap: 20px; margin-top: 16px;
        }
        .sr-pager { margin-top: 24px; }

        @media (max-width: 1100px) { .sr-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .sr-head { padding: 28px 20px 32px; }
            .sr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sr-search { flex-wrap: wrap; }
            .sr-sort-label { margin-left: 0; }
        }
    </style>

    <div class="sr-wrap">
        <div class="sr-head">
            <p class="au-eyebrow">Pencarian</p>
            <h1 class="au-title">
                Hasil Pencarian
                @if ($q !== '') untuk &ldquo;{{ $q }}&rdquo; @endif
            </h1>

            <form action="{{ route('search') }}" method="GET" class="sr-search">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, karakter, game..."
                       class="au-input">
                <button type="submit" class="au-btn">
                    Cari
                </button>
            </form>

            <p class="sr-count">
                @if ($results->total() > 0)
                    Menampilkan {{ $results->firstItem() }}&ndash;{{ $results->lastItem() }} dari {{ $results->total() }} hasil
                @else
                    Tidak ada hasil ditemukan.
                @endif
            </p>

            @if ($results->total() === 0 && isset($games))
                <div class="sr-suggest">
                    <p class="sr-suggest-h">Coba jelajahi game</p>
                    <div class="sr-chips">
                        @foreach ($games->take(8) as $g)
                            <a href="{{ route('search', ['game_id' => $g->id]) }}" class="sr-chip">
                                {{ $g->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <form method="GET" class="sr-filters">
            <input type="hidden" name="q" value="{{ $q }}">

            <select name="game_id" onchange="this.form.submit()" class="sr-sel">
                <option value="">Semua Game</option>
                @foreach ($games as $g)
                    <option value="{{ $g->id }}" @selected($filters['game_id'] == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>

            <select name="merch_category_id" onchange="this.form.submit()" class="sr-sel">
                <option value="">Kategori Merch</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['merch_category_id'] == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="tag" onchange="this.form.submit()" class="sr-sel">
                <option value="">Tag</option>
                @foreach ($tags as $t)
                    <option value="{{ $t->slug }}" @selected($filters['tag'] === $t->slug)>{{ $t->name }}</option>
                @endforeach
            </select>

            <select name="availability" onchange="this.form.submit()" class="sr-sel">
                <option value="">Ketersediaan</option>
                <option value="tersedia" @selected(request('availability') === 'tersedia')>Tersedia</option>
                <option value="kosong" @selected(request('availability') === 'kosong')>Kosong</option>
            </select>
            <input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="Harga min" class="sr-sel sr-narrow">
            <input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="Harga maks" class="sr-sel sr-narrow">

            <label for="sort" class="sr-sort-label">Urutkan</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="sr-sel">
                <option value="">Relevansi</option>
                <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                <option value="terpopuler" @selected(request('sort') === 'terpopuler')>Terpopuler</option>
                <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                <option value="abjad" @selected(request('sort') === 'abjad')>Abjad (A-Z)</option>
            </select>
            <button type="submit" class="au-btn is-sm">Terapkan</button>
        </form>

        @if ($results->isNotEmpty())
            <div class="sr-grid">
                @foreach ($results as $product)
                    <x-product-card
                        :product="$product"
                        :shipping="$shipping"
                        :matched-variant="$matchedVariants[$product->id]"
                    />
                @endforeach
            </div>

            <div class="sr-pager">
                {{ $results->links() }}
            </div>
        @endif
    </div>
@endsection
