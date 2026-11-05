@extends('layouts.app')

@section('title', $product->name.' — MERCATORIA')

@section('content')
    @php
        $mainPath = $variant?->image_path ?? $product->image_path;
        $shareUrl = request()->getSchemeAndHttpHost().'/'.$game->slug.'/'.$product->slug.(auth()->user() ? '?ref='.auth()->user()->referral_code : '');
        $thumbs = collect();
        if ($product->image_path) {
            $thumbs->push(['path' => $product->image_path, 'label' => 'Produk']);
        }
        foreach ($product->variants as $v) {
            if ($v->image_path && $v->image_path !== $product->image_path) {
                $thumbs->push(['path' => $v->image_path, 'label' => $v->name]);
            }
        }
        $flagTag = $product->tags->first();
        $flagClass = $flagTag ? match ($flagTag->slug) {
            'presale' => 'is-presale',
            'new' => 'is-new',
            'limited' => 'is-limited',
            default => ''
        } : '';
        $coins = $pricing['coins'] ?? intdiv((int) $pricing['final_price_bersih'], 100);

        // SEO: AppServiceProvider selalu share $seo dengan title = nama app, sehingga @section('title')
        // diabaikan layout. Kalau controller belum kirim $seo sendiri, isi dari data produk di sini.
        if (($seo['title'] ?? null) === config('app.name', 'MERCATORIA')) {
            $productUrl = url('/'.$game->slug.'/'.$product->slug);
            $seo = array_merge($seo ?? [], [
                'title'       => $product->name.' — MERCATORIA',
                'description' => 'Beli '.$product->name.' ('.$game->name.') di MERCATORIA. Harga '.format_rp($pricing['final_price_bersih']).', sudah termasuk ongkir CN-ID & bea.',
                'url'         => $productUrl,
                'canonical'   => $productUrl,
                'image'       => $mainPath ? url(Storage::url($mainPath)) : '',
            ]);
        }
    @endphp

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Background halaman produk = beranda (hanya aktif di halaman ini) */
        body { background: #f7f7f7; }

        .pp-wrap, .pp-modal-overlay {
            --pp-font: 'Roboto', system-ui, sans-serif;
            --pp-ink: #000;
            --pp-line: #e1e1e1;
            --pp-blue: #02a1ea;
        }
        .pp-wrap {
            font-family: var(--pp-font);
            color: #333;
            -webkit-font-smoothing: antialiased;
        }
        .pp-wrap *, .pp-wrap *::before, .pp-wrap *::after { box-sizing: border-box; }
        .pp-wrap a { color: inherit; text-decoration: none; }

        .pp-box {
            max-width: 1140px;
            margin: -22px auto 24px;
            padding: 36px 40px 60px;
            background: #fff;
            border: 0;
            border-radius: 0;
        }

        .pp-top {
            display: grid;
            grid-template-columns: 48% 1fr;
            column-gap: 36px;
            align-items: start;
        }

        /* ── Gallery ── */
        .pp-gallery {
            position: relative;
            overflow: hidden;
        }
        .pp-gallery > .pp-img {
            display: block; width: 100%; aspect-ratio: 1/1;
            object-fit: contain; cursor: zoom-in; background: #fff;
            /* ═══ 1. Zoom on hover ═══ */
            transition: transform .25s ease;
        }
        .pp-gallery:hover > .pp-img {
            transform: scale(1.8);
            cursor: zoom-in;
        }
        /* Zoom lens indicator */
        .pp-zoom-lens {
            position: absolute;
            top: 10px; right: 11px; z-index: 2;
            width: 26px; height: 26px; border: 0; border-radius: 50%;
            background: #fff; color: #111; display: flex;
            align-items: center; justify-content: center;
            cursor: zoom-in; box-shadow: 0 0 3px rgba(0,0,0,.15);
            pointer-events: none;
        }
        .pp-noimg {
            display: flex; align-items: center; justify-content: center;
            aspect-ratio: 1/1; font-size: 11px; letter-spacing: .15em;
            text-transform: uppercase; color: #aaa;
        }

        .pp-flag {
            position: absolute; top: 0; left: 0; z-index: 2;
            height: 18px; padding: 0 14px 0 8px; line-height: 18px;
            font-size: 10px; font-weight: 500; letter-spacing: 0;
            text-transform: uppercase; color: #fff; background: #FF4949;
            clip-path: polygon(0 0, 100% 0, calc(100% - 8px) 100%, 0 100%);
        }
        .pp-flag.is-presale { background: #3986C6; color: #fff; }
        .pp-flag.is-new { background: #4FC3F7; color: #fff; }
        .pp-flag.is-limited { background: #FF4949; color: #fff; }

        .pp-thumbs { display: flex; gap: 6px; margin-top: 10px; overflow-x: auto; }
        .pp-thumbs button {
            flex: none; width: 56px; height: 56px; padding: 0;
            border: 1px solid var(--pp-line); background: #fff; cursor: pointer;
        }
        .pp-thumbs button.is-on { border-color: #333; }
        .pp-thumbs img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* Lightbox */
        .pp-lightbox-hint {
            position: absolute; top: 20px; left: 50%; transform: translateX(-50%);
            padding: 8px 22px;
            background: rgba(255,255,255,.92); color: #333;
            border-radius: 20px;
            font-family: var(--pp-font); font-size: 13px; font-weight: 500;
            white-space: nowrap;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
            z-index: 11;
        }
        .pp-lightbox-close {
            position: absolute; top: 18px; right: 18px; z-index: 11;
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            background: #fff; color: #555;
            border: 1.5px solid #ccc; border-radius: 50%;
            font-size: 20px; font-weight: 400; line-height: 1;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
            transition: all .15s;
        }
        .pp-lightbox-close:hover {
            background: #f5f5f5; color: #111; border-color: #999;
        }

        /* ── Info ── */
        .pp-crumb { margin: 0; font-size: 12px; line-height: 18px; color: #888; }
        .pp-crumb a:hover { color: #222; }

        .pp-title {
            margin: 8px 0 0; font-size: 22px; line-height: 27px;
            font-weight: 500; color: var(--pp-ink);
        }

        .pp-price {
            margin: 10px 0 0; font-size: 22px; line-height: 26px;
            font-weight: 600; color: var(--pp-blue);
        }
        .pp-price s {
            display: block; font-size: 13px; line-height: 16px;
            font-weight: 400; color: rgba(0,0,0,.4);
        }

        .pp-chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0 0; }
        .pp-chip {
            display: inline-block; height: 22px; padding: 0 10px;
            border: 1px solid var(--pp-line); border-radius: 2px; background: #fff;
            font-size: 11px; line-height: 20px; color: #333;
        }
        a.pp-chip:hover, .pp-chip.is-on { border-color: #333; }
        .pp-chip.is-on { background: #333; color: #fff; }

        .pp-hr { height: 0; margin: 20px 0 0; border: 0; border-top: 1px solid var(--pp-line); }
        .pp-hr.is-2 { margin-top: 14px; }

        .pp-coins {
            margin: 14px 0 0; font-size: 14px; line-height: 20px; color: #333;
        }
        .pp-coins b { font-weight: 700; color: var(--pp-ink); }

        .pp-soldout { margin: 8px 0 0; font-size: 12px; font-weight: 600; color: #9e9e9e; }

        .pp-buy {
            display: flex; align-items: stretch; gap: 10px;
            margin: 12px 0 0; height: 30px;
        }
        .pp-qty { display: flex; height: 30px; }
        .pp-qty > * {
            width: 30px; height: 30px; border: 1px solid var(--pp-line);
            background: #fff; font: 400 13px/28px var(--pp-font);
            color: #666; text-align: center; padding: 0;
        }
        .pp-qty > * + * { border-left: 0; }
        .pp-qty button { cursor: pointer; }
        .pp-qty input {
            width: 44px; -moz-appearance: textfield; outline: 0;
        }
        .pp-qty input::-webkit-outer-spin-button,
        .pp-qty input::-webkit-inner-spin-button {
            -webkit-appearance: none; margin: 0;
        }
        .pp-add {
            height: 30px; padding: 0 24px; border: 0; border-radius: 2px;
            background: #908f95; color: #fff; cursor: pointer;
            font: 700 11px/30px var(--pp-font); letter-spacing: .06em;
            text-transform: uppercase;
        }
        .pp-add:hover { background: #77767c; }

        /* ═══ 2. Meta links — biru saat hover, tanpa underline ═══ */
        .pp-meta {
            margin: 14px 0 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 24px;
        }
        .pp-meta-item {
            font-size: 12px; line-height: 22px; color: #555;
        }
        .pp-meta-item .pp-meta-label {
            color: #888; margin-right: 4px; font-weight: 600;
        }
        .pp-meta-item a {
            color: #333;
            text-decoration: none;
            transition: color .15s;
        }
        .pp-meta-item a:hover {
            color: #02a1ea;
        }

        /* ═══ Share buttons — COMPACT 1 baris ═══ */
        .pp-share {
            margin-top: 18px;
            padding: 14px 16px;
            background: #fafafa;
            border: 1px solid var(--pp-line);
            border-radius: 6px;
        }
        .pp-share-title {
            margin: 0 0 8px;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .12em;
            color: #555;
            display: flex; align-items: center; gap: 6px;
        }
        .pp-share-title::before {
            content: '';
            display: inline-block;
            width: 3px; height: 14px;
            background: var(--pp-ink);
            border-radius: 2px;
        }
        .pp-share-note {
            margin: 0 0 10px; font-size: 11px; color: #777; line-height: 16px;
        }
        .pp-share-note a { text-decoration: underline; text-underline-offset: 2px; color: #333; }

        /* Compact row — flex nowrap, gap kecil, auto-shrink */
        .pp-share-btns {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
        }
        .pp-sbtn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            height: 24px;
            padding: 0 8px;
            border: 1px solid var(--pp-line);
            border-radius: 3px;
            background: #fff;
            font-family: var(--pp-font);
            font-size: 10px;
            font-weight: 600;
            color: #555;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s;
            flex-shrink: 0;
        }
        .pp-sbtn:hover {
            border-color: var(--pp-ink);
            color: var(--pp-ink);
        }
        .pp-sbtn svg { flex-shrink: 0; }

        /* Payment */
        .pp-pay {
            position: relative; margin-top: 22px; height: 58px;
            border: 1px solid var(--pp-line); border-radius: 3px;
            display: flex; align-items: center; justify-content: center;
            gap: 32px; padding-top: 6px;
        }
        .pp-pay > b {
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            padding: 0 10px; background: #fff;
            font-size: 13px; line-height: 18px; font-weight: 700;
            color: #222; white-space: nowrap;
        }
        .pp-pay-logo img {
            height: 28px; width: auto;
            filter: brightness(0) opacity(.75);
            transition: opacity .15s;
        }
        .pp-pay-logo img:hover { opacity: 1; }
        .pp-pay-logo.shopee img { height: 24px; }

        .pp-tabs { margin-top: 44px; border-top: 1px solid #e6e6e6; }
        .pp-tab {
            display: inline-block; margin-top: -2px; padding: 6px 0 0;
            border-top: 2px solid #2b2b2b;
            font-size: 13px; line-height: 18px; font-weight: 600; color: #333;
        }

        .pp-desc {
            display: block; width: 100%; max-width: 860px;
            margin: 17px auto 0;
        }

        .pp-rel-h {
            margin: 48px 0 0; font-size: 28px; line-height: 34px;
            font-weight: 500; color: #312e39;
        }
        .pp-rel {
            display: grid; grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px; margin-top: 20px;
        }

        /* ═══ 4. Modal — Cart Added ═══ */
        .pp-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }
        .pp-modal-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }
        .pp-modal {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            max-width: 420px; width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            transform: translateY(20px) scale(.96);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1);
        }
        .pp-modal-overlay.is-open .pp-modal {
            transform: translateY(0) scale(1);
        }
        .pp-modal-icon {
            width: 56px; height: 56px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #E8F5E9;
            display: flex; align-items: center; justify-content: center;
        }
        .pp-modal-icon svg { color: #43A047; }
        .pp-modal h3 {
            margin: 0 0 8px;
            font-family: var(--pp-font); font-size: 18px;
            font-weight: 600; color: var(--pp-ink);
        }
        .pp-modal p {
            margin: 0 0 6px;
            font-family: var(--pp-font); font-size: 13px; color: #777; line-height: 20px;
        }
        .pp-modal .pp-modal-product {
            display: flex; align-items: center; gap: 12px;
            padding: 12px;
            background: #f8f8f8;
            border-radius: 8px;
            margin: 16px 0;
            text-align: left;
        }
        .pp-modal .pp-modal-product img {
            width: 48px; height: 48px;
            object-fit: cover; border-radius: 6px;
            border: 1px solid var(--pp-line);
        }
        .pp-modal .pp-modal-product .pp-modal-pinfo {
            flex: 1; min-width: 0;
        }
        .pp-modal .pp-modal-product .pp-modal-pname {
            font-family: var(--pp-font); font-size: 13px;
            font-weight: 600; color: var(--pp-ink);
            overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pp-modal .pp-modal-product .pp-modal-pprice {
            font-family: var(--pp-font); font-size: 12px; color: #888; margin-top: 2px;
        }
        .pp-modal .pp-modal-product .pp-modal-pqty {
            font-family: var(--pp-font); font-size: 11px; color: #999;
            background: #eee; padding: 2px 8px; border-radius: 10px;
            white-space: nowrap;
        }
        .pp-modal-btns {
            display: flex; gap: 10px; margin-top: 20px;
        }
        .pp-modal-btns a, .pp-modal-btns button {
            flex: 1;
            height: 38px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 6px;
            font-family: var(--pp-font); font-size: 13px; font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
        }
        .pp-modal-btns .pp-btn-continue {
            background: #fff; border: 1.5px solid var(--pp-line);
            color: var(--pp-ink);
        }
        .pp-modal-btns .pp-btn-continue:hover {
            border-color: var(--pp-ink);
        }
        .pp-modal-btns .pp-btn-cart {
            background: var(--pp-ink); border: 1.5px solid var(--pp-ink);
            color: #fff;
        }
        .pp-modal-btns .pp-btn-cart:hover {
            background: #2a3040;
        }

        @media (max-width: 767px) {
            .pp-box { margin: 16px 12px; padding: 16px 16px 40px; }
            .pp-top { grid-template-columns: 1fr; row-gap: 20px; }
            .pp-meta { grid-template-columns: 1fr; }
            .pp-rel { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .pp-rel-h { font-size: 22px; line-height: 28px; }
            .pp-pay { gap: 20px; }
            .pp-pay-logo img { height: 22px; }
            .pp-modal { padding: 24px 20px; }
        }
    </style>

    <div class="pp-wrap">

        @if (in_array($userState ?? '', ['crush', 'ex', 'ons']) && isset($kuotaViewTotal))
            <div style="max-width:1140px;margin:16px auto 0;padding:10px 16px;border:1px solid #fcd34d;background:#fffbeb;font-size:13px;color:#78350f;">
                Kuota jelajah katalog kamu tersisa {{ ($kuotaViewTotal - ($kuotaViewUsed ?? 0)) }} dari {{ $kuotaViewTotal }} produk.
            </div>
        @endif

        <div class="pp-box">

            <div class="pp-top">

                {{-- KIRI: Gallery --}}
                <div>
                    <div class="pp-gallery" id="pp-gallery">
                        @if ($flagTag)
                            <span class="pp-flag {{ $flagClass }}">{{ $flagTag->name }}</span>
                        @endif
                        @if ($mainPath)
                            <img id="product-main-img" class="pp-img"
                                 src="{{ \App\Services\Image\ImageOptimizerService::url($mainPath, 'compressed') }}"
                                 alt="{{ $product->name }}"
                                 onclick="document.getElementById('product-lightbox').showModal()">
                            <div class="pp-zoom-lens">
                                <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                            </div>
                        @else
                            <div class="pp-noimg">Tanpa Foto</div>
                        @endif
                    </div>

                    @if ($thumbs->count() > 1)
                        <div class="pp-thumbs">
                            @foreach ($thumbs as $thumb)
                                <button type="button" class="{{ $mainPath === $thumb['path'] ? 'is-on' : '' }}" title="{{ $thumb['label'] }}"
                                        onclick="var u='{{ \App\Services\Image\ImageOptimizerService::url($thumb['path'], 'compressed') }}';document.getElementById('product-main-img').src=u;document.getElementById('lightbox-img').src=u;">
                                    <img src="{{ \App\Services\Image\ImageOptimizerService::url($thumb['path'], 'thumb') }}" alt="{{ $thumb['label'] }}">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    @if ($mainPath)
                        <dialog id="product-lightbox" class="m-0 h-full w-full max-h-none max-w-none bg-transparent p-0">
                            <div class="fixed inset-0 flex items-center justify-center bg-black/85 p-4"
                                 onclick="if(event.target===this)this.closest('dialog').close()">
                                <div class="pp-lightbox-hint">Klik ✕ atau di area gelap untuk menutup</div>
                                <button type="button" class="pp-lightbox-close"
                                        onclick="document.getElementById('product-lightbox').close()"
                                        aria-label="Tutup">
                                    ✕
                                </button>
                                <img id="lightbox-img" src="{{ Storage::url($mainPath) }}"
                                     alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                        </dialog>
                    @endif
                </div>

                {{-- KANAN: Info --}}
                <div>
                    <nav class="pp-crumb">
                        <a href="/">Beranda</a> / <a href="/{{ $game->slug }}">{{ $game->name }}</a> / {{ $product->name }}
                    </nav>

                    <h1 class="pp-title">{{ $product->name }}</h1>

                    <p class="pp-price">
                        @if ($product->hasActiveDiscount())
                            <s>{{ format_rp($pricing['base_price_bersih']) }}</s>
                        @endif
                        {{ format_rp($pricing['final_price_bersih']) }}
                    </p>

                    @if ($product->is_split_box && $product->splitBox)
                        <div style="margin-top:10px"><livewire:split-box-picker :product="$product" /></div>
                    @elseif ($product->variants->isNotEmpty())
                        <div class="pp-chips">
                            @foreach ($product->variants as $v)
                                <a class="pp-chip {{ ($variant?->id ?? null) === $v->id ? 'is-on' : '' }}"
                                   href="/{{ $game->slug }}/{{ $product->slug }}?variant={{ $v->id }}">{{ $v->name }}</a>
                            @endforeach
                            @if ($variant)
                                <a class="pp-chip" href="/{{ $game->slug }}/{{ $product->slug }}">Clear</a>
                            @endif
                        </div>
                    @endif

                    <hr class="pp-hr">

                    @if ($coins > 0)
                        <p class="pp-coins">Dapatkan <b>{{ number_format($coins, 0, ',', '.') }}</b> Koin dari produk ini.</p>
                    @endif
                    @if ($product->availability !== 'tersedia')
                        <p class="pp-soldout">Stok kosong</p>
                    @endif

                    {{-- ═══ 4. Form + AJAX — gak redirect, muncul modal ═══ --}}
                    @unless ($product->is_split_box && $product->splitBox)
                        <form method="POST" action="{{ route('cart.store') }}" class="pp-buy" id="pp-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if ($variant)
                                <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                            @endif
                            <div class="pp-qty">
                                <button type="button" data-qty="-1" aria-label="Kurangi">-</button>
                                <input type="number" name="qty" id="qty" value="1" min="1" max="999">
                                <button type="button" data-qty="1" aria-label="Tambah">+</button>
                            </div>
                            <button type="submit" class="pp-add" id="pp-add-btn">Tambah ke Keranjang</button>
                        </form>
                    @endunless

                    <hr class="pp-hr is-2">

                    {{-- Meta 2 kolom --}}
                    <div class="pp-meta">
                        <div class="pp-meta-item">
                            <span class="pp-meta-label">SKU:</span> {{ $variant?->sku ?? $product->sku }}
                        </div>
                        <div class="pp-meta-item">
                            <span class="pp-meta-label">Game:</span> <a href="/{{ $game->slug }}">{{ $game->name }}</a>
                        </div>
                        @if ($product->tags->isNotEmpty())
                            <div class="pp-meta-item">
                                <span class="pp-meta-label">Tag:</span>
                                @foreach ($product->tags as $tag)
                                    <a href="/product-tag/{{ $tag->slug }}">{{ $tag->name }}</a>{{ $loop->last ? '' : ', ' }}
                                @endforeach
                            </div>
                        @endif
                        @if ($product->developer)
                            <div class="pp-meta-item">
                                <span class="pp-meta-label">Developer:</span> <a href="{{ route('catalog.developer', $product->developer) }}">{{ $product->developer->name }}</a>
                            </div>
                        @endif
                    </div>

                    {{-- ═══ 3. Share — WA + Threads, hapus Email ═══ --}}
                    <div class="pp-share">
                        <p class="pp-share-title">Bagikan Produk Ini</p>
                        @auth
                            <p class="pp-share-note">Bagikan &amp; dapatkan <strong>+5 koin</strong> per IP Indonesia unik saat link dibuka.</p>
                        @else
                            <p class="pp-share-note">
                                <a href="{{ route('login') }}">Masuk</a> untuk membagikan link referral dan mendapat koin.
                            </p>
                        @endauth
                        <div class="pp-share-btns">
                            <button type="button" data-copy="{{ $shareUrl }}" class="pp-sbtn" title="Salin link">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></svg>
                                <span data-label>Salin Link</span>
                            </button>
                            <a href="https://wa.me/?text={{ urlencode($product->name . ' — ' . $shareUrl) }}" target="_blank" rel="noopener" class="pp-sbtn" title="WhatsApp">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="pp-sbtn" title="Facebook">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></svg>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($product->name) }}" target="_blank" rel="noopener" class="pp-sbtn" title="X / Twitter">
                                <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                                X
                            </a>
                            <a href="https://www.threads.net/intent/post?text={{ urlencode($product->name . ' — ' . $shareUrl) }}" target="_blank" rel="noopener" class="pp-sbtn" title="Threads">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.59 12c.025 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.96-.065-1.18.408-2.265 1.33-3.064.88-.76 2.119-1.207 3.583-1.291a13.6 13.6 0 0 1 3.02.142c-.126-.742-.375-1.332-.743-1.757-.51-.586-1.308-.883-2.359-.89h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044c3.194.02 5.097 1.975 5.287 5.388.108.046.216.094.321.142 1.49.7 2.58 1.761 3.154 3.07.797 1.82.871 4.79-1.548 7.158-1.85 1.81-4.094 2.628-7.277 2.65Zm1.003-11.69c-.242 0-.487.007-.739.021-1.836.093-2.98.947-2.916 2.143.067 1.256 1.452 1.839 2.784 1.791 1.224-.045 2.818-.553 3.086-3.71a10.5 10.5 0 0 0-2.215-.245z"/></svg>
                                Threads
                            </a>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="pp-pay">
                        <b>Jaminan Pembayaran Aman</b>
                        <div class="pp-pay-logo">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                 alt="Bank BCA" loading="lazy">
                        </div>
                        <div class="pp-pay-logo">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e0/QRIS_Logo.svg"
                                 alt="QRIS" loading="lazy">
                        </div>
                        <div class="pp-pay-logo">
                            <img src="https://shopeepay.com/static/media/shopeePayLogo.e0c80b4be312ff7fc6bd1220fd56142d.svg"
                                 alt="ShopeePay" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pp-tabs"><span class="pp-tab">Deskripsi</span></div>

            @if ($product->description_image_path)
                <img class="pp-desc" loading="lazy"
                     src="{{ \App\Services\Image\ImageOptimizerService::url($product->description_image_path, 'compressed') }}"
                     alt="Deskripsi {{ $product->name }}">
            @endif

            @if ($related->isNotEmpty())
                <h2 class="pp-rel-h">Produk Terkait</h2>
                <div class="pp-rel">
                    @foreach ($related as $relatedProduct)
                        <x-product-card :product="$relatedProduct" :shipping="$shipping" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ 4. Modal "Ditambahkan ke Keranjang" ═══ --}}
    <div class="pp-modal-overlay" id="pp-cart-modal">
        <div class="pp-modal">
            <div class="pp-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h3>Berhasil Ditambahkan!</h3>
            <p>Produk sudah masuk ke keranjang belanja kamu.</p>

            <div class="pp-modal-product">
                @if ($mainPath)
                    <img src="{{ \App\Services\Image\ImageOptimizerService::url($mainPath, 'thumb') }}" alt="{{ $product->name }}">
                @endif
                <div class="pp-modal-pinfo">
                    <div class="pp-modal-pname">{{ $product->name }}</div>
                    <div class="pp-modal-pprice">{{ format_rp($pricing['final_price_bersih']) }}</div>
                </div>
                <div class="pp-modal-pqty" id="pp-modal-qty">×1</div>
            </div>

            <div class="pp-modal-btns">
                <button type="button" class="pp-btn-continue" id="pp-modal-close">Lanjut Belanja</button>
                <a href="{{ route('cart.index') }}" class="pp-btn-cart">Lihat Keranjang</a>
            </div>
        </div>
    </div>

    @if (\Illuminate\Support\Facades\Route::has('protected-images.show'))
        <script src="{{ asset('js/image-protection.js') }}" defer></script>
    @endif

    <script>
        (function () {
            var qty = document.getElementById('qty');

            // Qty +/- buttons
            document.querySelectorAll('[data-qty]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!qty) return;
                    var n = (parseInt(qty.value, 10) || 1) + parseInt(btn.getAttribute('data-qty'), 10);
                    qty.value = Math.min(999, Math.max(1, n));
                });
            });

            // Copy link
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!(navigator.clipboard && navigator.clipboard.writeText)) return;
                    navigator.clipboard.writeText(btn.getAttribute('data-copy'));
                    var l = btn.querySelector('[data-label]');
                    if (!l) return;
                    l.textContent = 'Tersalin!';
                    setTimeout(function () { l.textContent = 'Salin Link'; }, 2000);
                });
            });

            // ═══ 1. Hover zoom — follow mouse position ═══
            var gallery = document.getElementById('pp-gallery');
            var mainImg = document.getElementById('product-main-img');
            if (gallery && mainImg) {
                gallery.addEventListener('mousemove', function (e) {
                    var rect = gallery.getBoundingClientRect();
                    var x = ((e.clientX - rect.left) / rect.width * 100);
                    var y = ((e.clientY - rect.top) / rect.height * 100);
                    mainImg.style.transformOrigin = x + '% ' + y + '%';
                });
                gallery.addEventListener('mouseleave', function () {
                    mainImg.style.transformOrigin = 'center center';
                });
            }

            // ═══ 4. AJAX Cart + Modal ═══
            var cartForm = document.getElementById('pp-cart-form');
            var modal = document.getElementById('pp-cart-modal');
            var modalClose = document.getElementById('pp-modal-close');
            var modalQty = document.getElementById('pp-modal-qty');

            if (cartForm && modal) {
                cartForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    var btn = document.getElementById('pp-add-btn');
                    var originalText = btn.textContent;
                    btn.textContent = 'Menambahkan...';
                    btn.disabled = true;

                    var formData = new FormData(cartForm);

                    fetch(cartForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function (res) {
                        // Belum login → arahkan ke halaman login
                        if (res.status === 401) { window.location.href = '{{ route('login') }}'; return null; }
                        // Gagal (validasi/stok/dll) → submit normal supaya pesan error tampil
                        if (!res.ok) { cartForm.submit(); return null; }
                        return res.json();
                    })
                    .then(function (data) {
                        if (!data) return;

                        // Perbarui angka keranjang di header (desktop + mobile)
                        document.querySelectorAll('[data-cart-total]').forEach(function (el) {
                            el.textContent = data.total_formatted;
                            el.style.display = '';
                        });
                        document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                            el.textContent = data.count;
                            el.style.display = '';
                        });

                        // Qty di modal + buka modal
                        if (modalQty) {
                            modalQty.textContent = '×' + (parseInt(qty.value, 10) || 1);
                        }
                        modal.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                    })
                    .catch(function () {
                        // Fallback: submit normal
                        cartForm.submit();
                    })
                    .finally(function () {
                        btn.textContent = originalText;
                        btn.disabled = false;
                    });
                });

                // Close modal
                function closeModal() {
                    modal.classList.remove('is-open');
                    document.body.style.overflow = '';
                }

                if (modalClose) {
                    modalClose.addEventListener('click', closeModal);
                }

                // Click overlay = close
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });

                // ESC = close
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            }
        })();
    </script>
@endsection
