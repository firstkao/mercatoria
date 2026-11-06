@extends('layouts.app')

@section('title', 'Hasil Pencarian — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        .sr-wrap, .sr-wrap *, .sr-wrap *::before, .sr-wrap *::after { box-sizing: border-box; }
        .sr-wrap {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .sr-wrap a { text-decoration: none; color: inherit; }

        .sr-head {
            padding: 36px 40px 32px;
            background: #fff;
        }
        .sr-title {
            margin: 0;
            font-size: 32px;
            line-height: 40px;
            font-weight: 500;
            color: #26222f;
        }
        .sr-search {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .sr-search input {
            flex: 1;
            height: 44px;
            padding: 0 14px;
            border: 1px solid #ddd;
            font: 400 15px/44px 'Roboto', system-ui, sans-serif;
            outline: 0;
        }
        .sr-search input:focus { border-color: #26222f; }
        .sr-search button {
            height: 44px;
            padding: 0 28px;
            border: 0;
            background: #26222f;
            color: #fff;
            font: 700 14px/44px 'Roboto', system-ui, sans-serif;
            letter-spacing: .02em;
            text-transform: uppercase;
            cursor: pointer;
        }
        .sr-search button:hover { background: #3a3547; }

        .sr-count {
            margin: 16px 0 0;
            font-size: 15px;
            line-height: 22px;
            color: #6f6a72;
        }

        .sr-suggest { margin-top: 20px; }
        .sr-suggest-h {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #26222f;
        }
        .sr-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .sr-chip {
            display: inline-block;
            height: 32px;
            padding: 0 14px;
            border: 1px solid #ddd;
            background: #fff;
            font-size: 13px;
            line-height: 30px;
            color: #333;
        }
        .sr-chip:hover { border-color: #26222f; }

        .sr-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 24px;
            padding: 16px 20px;
            background: #fff;
        }
        .sr-sel {
            height: 38px;
            padding: 0 12px;
            border: 1px solid #ddd;
            background: #fff;
            font: 400 14px/38px 'Roboto', system-ui, sans-serif;
            color: #333;
            outline: 0;
            cursor: pointer;
        }
        .sr-sel:focus { border-color: #26222f; }
        .sr-filters button {
            height: 38px;
            padding: 0 20px;
            border: 0;
            background: #26222f;
            color: #fff;
            font: 700 13px/38px 'Roboto', system-ui, sans-serif;
            letter-spacing: .02em;
            text-transform: uppercase;
            cursor: pointer;
        }
        .sr-filters button:hover { background: #3a3547; }

        .sr-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            column-gap: 20px;
            row-gap: 44px;
            margin-top: 28px;
        }
        .sr-pager { margin-top: 48px; }

        @media (max-width: 1360px) { .sr-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (max-width: 1180px) { .sr-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .sr-head { padding: 24px 16px; }
            .sr-title { font-size: 26px; line-height: 32px; }
            .sr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sr-search { flex-wrap: wrap; }
        }
    </style>

    <div class="sr-wrap">
        <div class="sr-head">
            <h1 class="sr-title">
                Hasil Pencarian
                @if ($q !== '') untuk “{{ $q }}” @endif
            </h1>

            <form action="{{ route('search') }}" method="GET" class="sr-search">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, karakter, game...">
                <button type="submit">Cari</button>
            </form>

            <p class="sr-count">
                @if ($results->total() > 0)
                    Menampilkan {{ $results->firstItem() }}–{{ $results->lastItem() }} dari {{ $results->total() }} hasil
                @else
                    Tidak ada hasil ditemukan.
                @endif
            </p>

            @if ($results->total() === 0 && isset($games))
                <div class="sr-suggest">
                    <p class="sr-suggest-h">Coba jelajahi game</p>
                    <div class="sr-chips">
                        @foreach ($games->take(8) as $g)
                            <a href="{{ route('search', ['game_id' => $g->id]) }}" class="sr-chip">{{ $g->name }}</a>
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

            <select name="developer_id" onchange="this.form.submit()" class="sr-sel">
                <option value="">Semua Developer</option>
                @foreach ($developers ?? [] as $dev)
                    <option value="{{ $dev->id }}" @selected(($filters['developer_id'] ?? null) == $dev->id)>{{ $dev->name }}</option>
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

            <select name="sort" onchange="this.form.submit()" class="sr-sel">
                <option value="">Pengurutan standar</option>
                <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                <option value="terpopuler" @selected(request('sort') === 'terpopuler')>Terpopuler</option>
                <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                <option value="abjad" @selected(request('sort') === 'abjad')>Abjad (A-Z)</option>
            </select>

            <button type="submit">Terapkan</button>
        </form>

        @if ($results->isNotEmpty())
            <div class="sr-grid">
                @foreach ($results as $product)
                    <x-product-card
                        :product="$product"
                        :shipping="$shipping"
                        :matched-variant="$matchedVariants[$product->id] ?? null"
                        :simple="true"
                    />
                @endforeach
            </div>

            <div class="sr-pager">
                {{ $results->links() }}
            </div>
        @endif
    </div>
@endsection
