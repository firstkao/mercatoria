@extends('layouts.app')

@section('title', $title.' — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Katalog game / developer / semua produk — CSS biasa (tidak bergantung build Tailwind), selaras dengan au-* & hp-*/pp-*. */
        .cg-wrap, .cg-wrap *, .cg-wrap *::before, .cg-wrap *::after { box-sizing: border-box; }
        .cg-wrap { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .cg-wrap a { text-decoration: none; }

        .cg-crumb {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;
            margin: 0; font-size: 12px; line-height: 18px; color: #888;
        }
        .cg-crumb a { color: #888; }
        .cg-crumb a:hover { color: #222; text-decoration: underline; }
        .cg-crumb .is-current { color: #333; }

        .cg-head { max-width: 576px; margin: 16px auto 0; text-align: center; }
        .cg-logo {
            display: block; width: 64px; height: 64px; margin: 0 auto;
            border: 1px solid #e1e1e1; border-radius: 0; object-fit: cover;
        }
        .cg-title { margin: 12px 0 0; text-align: center; }
        .cg-title.is-head { margin-top: 12px; }
        .cg-sub { margin: 6px 0 0; font-size: 14px; line-height: 21px; color: #888; text-align: center; }

        .cg-layout { display: flex; align-items: flex-start; gap: 24px; margin-top: 28px; }
        .cg-side { display: flex; flex: none; flex-direction: column; gap: 16px; width: 25%; }
        .cg-main { flex: 1; min-width: 0; }

        .cg-card { padding: 16px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .cg-card-h {
            margin: 0; font-size: 11px; line-height: 16px; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase; color: #333;
        }
        .cg-list { margin: 10px 0 0; padding: 0; list-style: none; }
        .cg-list li + li { margin-top: 2px; }
        .cg-link {
            display: block; padding: 4px 8px;
            font-size: 14px; line-height: 21px; color: #555; transition: background .15s, color .15s;
        }
        .cg-link:hover { background: #f7f7f7; color: #000; }
        .cg-link.is-on { color: #000; font-weight: 700; }

        .cg-offers { margin: 10px 0 0; padding: 0; list-style: none; }
        .cg-offers li + li { margin-top: 8px; }
        .cg-offer { display: flex; align-items: center; gap: 10px; }
        .cg-offer:hover .cg-offer-name { color: #000; }
        .cg-thumb { flex: none; width: 40px; height: 40px; overflow: hidden; background: #f7f7f7; border: 1px solid #e1e1e1; }
        .cg-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .cg-offer-name {
            margin: 0; font-size: 12px; line-height: 17px; color: #555;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .cg-toolbar {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            margin: 0 0 16px;
        }
        .cg-count {
            margin: 0; font-size: 11px; line-height: 16px; font-weight: 500;
            letter-spacing: .14em; text-transform: uppercase; color: #888;
        }
        .cg-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .cg-sel {
            display: inline-block; height: 34px; margin: 0; padding: 0 10px;
            border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
            font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #333; outline: 0; transition: border-color .15s;
        }
        .cg-sel:focus { border-color: #000; }
        .cg-narrow { width: 112px; }
        .cg-sort-label { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }

        .cg-empty {
            padding: 48px 24px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0;
            text-align: center; font-size: 14px; line-height: 21px; color: #555;
        }

        .cg-grid {
            display: grid; grid-template-columns: repeat(6, minmax(0, 1fr));
            column-gap: 16px; row-gap: 20px;
        }
        .cg-pager { margin-top: 24px; }

        @media (max-width: 1100px) { .cg-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 1023px) {
            .cg-layout { flex-direction: column; }
            .cg-side { width: 100%; }
        }
        @media (max-width: 767px) { .cg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    </style>

    <div class="cg-wrap">

        {{-- ═══════════════════════════════════════════════
             BREADCRUMB — handle 3 konteks
             ═══════════════════════════════════════════════ --}}
        <nav class="cg-crumb">
            <a href="/">Beranda</a>
            <span>/</span>
            @if ($developer)
                <a href="{{ route('catalog.all') }}">Semua Produk</a>
                <span>/</span>
                <a href="/">Developer</a>
            @elseif ($game)
                <a href="{{ route('catalog.all') }}">Semua Produk</a>
                <span>/</span>
                <a href="/">Game</a>
            @else
                <a href="/">Produk</a>
            @endif
            <span>/</span>
            <span class="is-current">{{ $title }}</span>
        </nav>

        {{-- ═══════════════════════════════════════════════
             HEADER — handle 3 konteks
             ═══════════════════════════════════════════════ --}}
        @if ($game)
            {{-- Header Game --}}
            <div class="cg-head">
                @if ($game->logo_path ?? null)
                    <img src="{{ Storage::url($game->logo_path) }}" alt="{{ $game->name }}"
                         class="cg-logo">
                @endif
                <h1 class="au-title cg-title is-head">{{ $game->name }}</h1>
                <p class="au-eyebrow">Game</p>
            </div>

        @elseif ($developer)
            {{-- Header Developer --}}
            <div class="cg-head">
                @if ($developer->logo_path)
                    <img src="{{ Storage::url($developer->logo_path) }}" alt="{{ $developer->name }}"
                         class="cg-logo">
                @endif
                <h1 class="au-title cg-title is-head">{{ $developer->name }}</h1>
                <p class="au-eyebrow">Developer</p>
            </div>

        @else
            {{-- Header Semua Produk --}}
            <div class="cg-head">
                <h1 class="au-title cg-title">{{ $title }}</h1>
                <p class="cg-sub">Jelajahi seluruh koleksi Mercatoria</p>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════
             LAYOUT — sidebar + grid
             ═══════════════════════════════════════════════ --}}
        <div class="cg-layout">

            {{-- ═══ SIDEBAR ═══ --}}
            <aside class="cg-side">

                {{-- Navigasi Game — tampil kecuali di halaman developer --}}
                @unless ($developer)
                    <div class="cg-card">
                        <h2 class="cg-card-h">Kategori Game</h2>
                        <ul class="cg-list">
                            {{-- Link "Semua Produk" — highlighted kalau lagi di halaman ini --}}
                            @if (! $game)
                                <li>
                                    <a href="{{ route('catalog.all') }}" class="cg-link is-on">
                                        Semua Produk
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('catalog.all') }}" class="cg-link">
                                        Semua Produk
                                    </a>
                                </li>
                            @endif

                            @foreach ($games as $g)
                                <li>
                                    <a href="/{{ $g->slug }}"
                                       class="cg-link {{ ($game?->id ?? null) === $g->id ? 'is-on' : '' }}">
                                        {{ $g->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endunless

                {{-- Developer lain — tampil cuma di halaman developer --}}
                @if ($developer)
                    <div class="cg-card">
                        <h2 class="cg-card-h">Developer Lain</h2>
                        <ul class="cg-list">
                            @foreach (($footerDevelopers ?? collect()) as $dev)
                                @if ($dev->id !== $developer->id)
                                    <li>
                                        <a href="{{ route('catalog.developer', $dev) }}"
                                           class="cg-link">
                                            {{ $dev->name }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Kategori Merch — tampil cuma kalau ada game context --}}
                @if ($game)
                    <div class="cg-card">
                        <h2 class="cg-card-h">Kategori Merch</h2>
                        <ul class="cg-list">
                            <li>
                                <a href="/{{ $game->slug }}"
                                   class="cg-link {{ ! $activeCategory ? 'is-on' : '' }}">
                                    Semua
                                </a>
                            </li>
                            @foreach ($categories as $category)
                                <li>
                                    <a href="/{{ $game->slug }}/kategori/{{ $category->slug }}"
                                       class="cg-link {{ ($activeCategory?->id ?? null) === $category->id ? 'is-on' : '' }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Penawaran Terbaik — selalu tampil --}}
                @if ($bestOffers->isNotEmpty())
                    <div class="cg-card">
                        <h2 class="cg-card-h">Penawaran Terbaik</h2>
                        <ul class="cg-offers">
                            @foreach ($bestOffers as $offer)
                                <li>
                                    <a href="/{{ $offer->game->slug }}/{{ $offer->slug }}" class="cg-offer">
                                        <span class="cg-thumb">
                                            @if ($offer->image_path)
                                                <img src="{{ \App\Services\Image\ImageOptimizerService::url($offer->image_path, 'thumb') }}" alt="">
                                            @endif
                                        </span>
                                        <p class="cg-offer-name">{{ $offer->name }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>

            {{-- ═══ MAIN — grid produk (sama untuk semua konteks) ═══ --}}
            <div class="cg-main">
                <div class="cg-toolbar">
                    <p class="cg-count">
                        Menampilkan
                        {{ $products->total() > 0 ? ($products->firstItem()).'&ndash;'.($products->lastItem()) : 0 }}
                        dari {{ $products->total() }} hasil
                    </p>

                    <form method="GET" class="cg-filters">
                        <select name="tag" onchange="this.form.submit()" class="cg-sel">
                            <option value="">Semua Tag</option>
                            @foreach ($tags as $t)
                                <option value="{{ $t->slug }}" @selected(request('tag') === $t->slug)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <select name="availability" onchange="this.form.submit()" class="cg-sel">
                            <option value="">Ketersediaan</option>
                            <option value="tersedia" @selected(request('availability') === 'tersedia')>Tersedia</option>
                            <option value="kosong" @selected(request('availability') === 'kosong')>Kosong</option>
                        </select>
                        <input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="Harga min"
                               class="cg-sel cg-narrow">
                        <input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="Harga maks"
                               class="cg-sel cg-narrow">
                        <label for="sort" class="cg-sort-label">Urutkan</label>
                        <select name="sort" id="sort" onchange="this.form.submit()"
                                class="cg-sel">
                            <option value="">Relevansi</option>
                            <option value="termurah" @selected(request('sort') === 'termurah')>Termurah</option>
                            <option value="termahal" @selected(request('sort') === 'termahal')>Termahal</option>
                            <option value="terpopuler" @selected(request('sort') === 'terpopuler')>Terpopuler</option>
                            <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                            <option value="abjad" @selected(request('sort') === 'abjad')>Abjad (A-Z)</option>
                        </select>
                        <button type="submit" class="au-btn is-sm">Terapkan</button>
                    </form>
                </div>

                @if ($products->isEmpty())
                    <div class="cg-empty">
                        Tidak ada produk ditemukan.
                    </div>
                @else
                    <div class="cg-grid">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" :shipping="$shipping" />
                        @endforeach
                    </div>

                    <div class="cg-pager">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection