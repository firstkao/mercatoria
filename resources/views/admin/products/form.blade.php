@extends('admin.layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
@php
    $cfg = [
        'rate_rmb'       => rmb_rate(),
        'ongkir_cn_btm'  => (float) setting('ongkir_cn_btm_per_kg', 25000),
        'ongkir_btm_jkt' => (float) setting('ongkir_btm_jkt_per_kg', 60000),
        'profit_percent' => (float) setting('profit_percent', 11),
        'default_weight' => (float) setting('default_weight_gram', 500),
    ];
    $productTags = old('tags', $product->exists ? $product->tags->pluck('id')->all() : []);
    $orderCount = $product->exists ? $product->ordersCount() : 0;
    $developerList = $developers ?? [];
@endphp

<style>
    .pf, .pf *, .pf *::before, .pf *::after { box-sizing: border-box; }
    .pf {
        font-family: 'Roboto', system-ui, sans-serif;
        color: #333;
        -webkit-font-smoothing: antialiased;
    }
    .pf a { text-decoration: none; }

    .pf-box {
        position: relative;
        left: 50%;
        transform: translateX(-50%);
        width: min(1300px, calc(100vw - 48px));
        margin: 24px 0;
        background: #fff;
        border: 1px solid #e1e1e1;
    }

    .pf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid #e1e1e1;
        background: #fff;
    }
    .pf-header h1 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #000;
    }
    .pf-header-actions { display: flex; gap: 8px; }

    .pf-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
    }
    .pf-main { border-right: 1px solid #e1e1e1; min-width: 0; }
    .pf-side { background: #fafafa; }

    @media (max-width: 900px) {
        .pf-layout { grid-template-columns: 1fr; }
        .pf-main { border-right: 0; border-bottom: 1px solid #e1e1e1; }
    }

    .pf-section { padding: 24px; border-bottom: 1px solid #e1e1e1; }
    .pf-section:last-child { border-bottom: 0; }

    .pf-label {
        display: block;
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 600;
        color: #555;
    }
    .pf-input, .pf-select {
        width: 100%;
        height: 36px;
        padding: 0 12px;
        border: 1px solid #e1e1e1;
        background: #fff;
        font: 400 13px/36px 'Roboto', system-ui, sans-serif;
        color: #333;
    }
    .pf-input:focus, .pf-select:focus { outline: 0; border-color: #333; }

    .pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .pf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    @media (max-width: 700px) {
        .pf-grid-2, .pf-grid-3 { grid-template-columns: 1fr; }
    }

    .pf-tabs { display: flex; gap: 0; border-bottom: 1px solid #e1e1e1; }
    .pf-tab {
        padding: 12px 20px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: none;
        font: 400 13px/20px 'Roboto', system-ui, sans-serif;
        color: #888;
        cursor: pointer;
    }
    .pf-tab:hover { color: #333; }
    .pf-tab.is-on { color: #000; font-weight: 600; border-bottom-color: #000; }

    .pf-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 34px;
        padding: 0 16px;
        border: 1px solid #e1e1e1;
        background: #fff;
        font: 500 12px/34px 'Roboto', system-ui, sans-serif;
        color: #333;
        cursor: pointer;
    }
    .pf-btn:hover { border-color: #333; }
    .pf-btn.is-primary { background: #000; border-color: #000; color: #fff; }
    .pf-btn.is-primary:hover { background: #222; }
    .pf-btn.is-danger { color: #c62828; border-color: #ffcdd2; }
    .pf-btn.is-danger:hover { background: #ffebee; }

    .pf-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        height: 26px;
        padding: 0 10px;
        border: 1px solid #e1e1e1;
        background: #fff;
        font-size: 12px;
        color: #333;
    }
    .pf-chip.is-on { background: #000; color: #fff; border-color: #000; }
    .pf-chip-mod { font-size: 10px; color: #f57c00; font-weight: 600; }

    .pf-attr-box {
        margin-top: 12px;
        padding: 16px;
        border: 1px solid #e1e1e1;
        background: #fff;
    }
    .pf-attr-head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .pf-attr-values { display: flex; flex-wrap: wrap; gap: 6px; }

    .pf-err { margin: 4px 0 0; font-size: 11px; color: #c62828; }
    .pf-note { margin: 8px 0 0; font-size: 11px; color: #888; }

    .pf-side-section { padding: 16px 20px; border-bottom: 1px solid #e1e1e1; }
    .pf-side-section:last-child { border-bottom: 0; }
    .pf-side-label {
        margin: 0 0 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #888;
    }

    .pf-flash {
        margin: 0 0 16px;
        padding: 12px 16px;
        border: 1px solid #c8e6c9;
        background: #e8f5e9;
        font-size: 13px;
        color: #2e7d32;
    }
    .pf-flash.is-error { border-color: #ffcdd2; background: #ffebee; color: #c62828; }
</style>

@if (session('status'))
    <div class="pf-flash">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="pf-flash is-error">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ $product->exists ? route('office.products.update', $product) : route('office.products.store') }}"
      enctype="multipart/form-data"
      class="pf">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <input type="hidden" name="is_active" id="input-is-active"
           value="{{ old('is_active', $product->exists ? ($product->is_active ? 1 : 0) : 1) }}">
    <input type="hidden" name="remove_description_image" id="remove-desc-flag" value="0">
    <input type="hidden" name="is_split_box" id="input-is-split-box"
           value="{{ old('is_split_box', $product->exists ? ($product->is_split_box ? 1 : 0) : 0) }}">

    <div class="pf-box">
        <div class="pf-header">
            <h1>{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h1>
            <div class="pf-header-actions">
                @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                    <a href="{{ route('office.products.preview', $product) }}" target="_blank" class="pf-btn">Preview</a>
                @endif
                <button type="submit" onclick="document.getElementById('input-is-active').value='1'" class="pf-btn is-primary">Simpan</button>
            </div>
        </div>

        <div class="pf-layout">
            <div class="pf-main">
                {{-- Nama + Slug --}}
                <div class="pf-section">
                    <div class="pf-grid-2">
                        <div>
                            <label class="pf-label">Nama Produk *</label>
                            <input type="text" name="name" id="input-name" value="{{ old('name', $product->name) }}" required class="pf-input">
                            @error('name')<p class="pf-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pf-label">Slug</label>
                            <input type="text" name="slug" id="input-slug" value="{{ old('slug', $product->slug) }}" class="pf-input">
                            @error('slug')<p class="pf-err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div>
                    <div class="pf-tabs">
                        <button type="button" data-prod-tab="kalkulasi" class="pf-tab is-on">Kalkulasi Harga</button>
                        <button type="button" data-prod-tab="atribut" class="pf-tab">Atribut</button>
                        <button type="button" data-prod-tab="varian" class="pf-tab">Varian</button>
                    </div>

                    {{-- Panel Kalkulasi --}}
                    <div class="pf-section" data-prod-panel="kalkulasi">
                        <div class="pf-grid-3">
                            <div>
                                <label class="pf-label">SKU *</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="pf-input">
                                @error('sku')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Harga RMB *</label>
                                <input type="number" step="0.01" name="price_rmb" id="input-price-rmb" value="{{ old('price_rmb', $product->price_rmb) }}" required class="pf-input">
                                @error('price_rmb')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Berat (gram) *</label>
                                <input type="number" name="weight_gram" id="input-weight-gram" required min="1" value="{{ old('weight_gram', $product->weight_gram) }}" class="pf-input">
                                @error('weight_gram')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <p class="pf-note">Berat dipakai untuk hitung ongkir. Varian bisa override.</p>

                        <div style="margin-top:20px">
                            <p class="pf-side-label">Ongkir (Otomatis)</p>
                            <div class="pf-grid-3">
                                <div style="padding:12px;border:1px solid #e1e1e1">
                                    <p style="margin:0;font-size:11px;color:#888">Ongkir Lokal CN</p>
                                    <p style="margin:4px 0 0;font-weight:600" id="disp-ongkir-lokal">—</p>
                                </div>
                                <div style="padding:12px;border:1px solid #e1e1e1">
                                    <p style="margin:0;font-size:11px;color:#888">Ongkir CN → BTM</p>
                                    <p style="margin:4px 0 0;font-weight:600" id="disp-ongkir-cnbtm">—</p>
                                </div>
                                <div style="padding:12px;border:1px solid #e1e1e1">
                                    <p style="margin:0;font-size:11px;color:#888">Ongkir BTM → JKT</p>
                                    <p style="margin:4px 0 0;font-weight:600" id="disp-ongkir-btmjkt">—</p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:20px;padding:16px;background:#fafafa;border-left:3px solid #000">
                            <p class="pf-side-label">Preview Perhitungan</p>
                            <table style="width:100%;font-size:12px;margin-top:10px">
                                <tr><td style="padding:6px 0;color:#888">Harga Produk</td><td style="text-align:right;font-weight:600" id="calc-harga">—</td></tr>
                                <tr><td style="padding:6px 0;color:#888">Ongkir Lokal CN</td><td style="text-align:right" id="calc-lokal">—</td></tr>
                                <tr><td style="padding:6px 0;color:#888">Ongkir CN-BTM</td><td style="text-align:right" id="calc-cnbtm">—</td></tr>
                                <tr><td style="padding:6px 0;color:#888">Ongkir BTM-JKT</td><td style="text-align:right" id="calc-btmjkt">—</td></tr>
                                <tr style="border-top:1px solid #e1e1e1"><td style="padding:8px 0;font-weight:600">Total</td><td style="text-align:right;font-weight:700;font-size:14px" id="calc-rounded">—</td></tr>
                            </table>
                        </div>
                    </div>

                    {{-- Panel Atribut --}}
                    <div class="pf-section" data-prod-panel="atribut" style="display:none">
                        <div class="pf-grid-2">
                            <div>
                                <label class="pf-label">Tipe *</label>
                                <select name="type" id="input-type" required class="pf-select">
                                    <option value="single" @selected(old('type', $product->type ?: 'single') === 'single')>Single</option>
                                    <option value="variable" @selected(old('type', $product->type) === 'variable')>Variable</option>
                                </select>
                                @error('type')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Game *</label>
                                <select name="game_id" id="input-game-id" required class="pf-select">
                                    @foreach ($games as $game)
                                        <option value="{{ $game->id }}" @selected(old('game_id', $product->game_id) == $game->id)>{{ $game->name }}</option>
                                    @endforeach
                                </select>
                                @error('game_id')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Developer</label>
                                <select name="developer_id" class="pf-select">
                                    <option value="">— tanpa developer —</option>
                                    @foreach ($developerList as $dev)
                                        <option value="{{ $dev->id }}" @selected(old('developer_id', $product->developer_id) == $dev->id)>{{ $dev->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="pf-label">Kategori Merch</label>
                                <select name="merch_category_id" class="pf-select">
                                    <option value="">— auto —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($product->exists)
                            <div style="margin-top:24px">
                                <div style="display:flex;align-items:center;justify-content:space-between">
                                    <p class="pf-label" style="margin:0">Atribut</p>
                                    <button type="button" onclick="addAttributeRow()" class="pf-btn">+ Atribut</button>
                                </div>

                                <div id="attributes-list">
                                    @foreach ($product->attributes as $i => $attr)
                                        <div class="pf-attr-box" data-attr-index="{{ $i }}">
                                            <div class="pf-attr-head">
                                                <input type="text" name="attributes[{{ $i }}][name]" value="{{ $attr->name }}" class="pf-input" style="flex:1" placeholder="Nama atribut (cth: Karakter)">
                                                <input type="hidden" name="attributes[{{ $i }}][id]" value="{{ $attr->id }}">
                                                <button type="button" onclick="this.closest('.pf-attr-box').remove()" class="pf-btn is-danger">Hapus</button>
                                            </div>
                                            <div class="pf-attr-values">
                                                @foreach ($attr->values as $j => $val)
                                                    <div class="pf-chip">
                                                        <input type="hidden" name="attributes[{{ $i }}][values][{{ $j }}][id]" value="{{ $val->id }}">
                                                        <input type="text" name="attributes[{{ $i }}][values][{{ $j }}][value]" value="{{ $val->value }}" style="width:80px;border:0;background:transparent;font-size:12px" placeholder="Nilai">
                                                        <select name="attributes[{{ $i }}][values][{{ $j }}][modifier]" style="border:0;background:transparent;font-size:11px;width:50px">
                                                            <option value="0" @selected($val->modifier == 0)>—</option>
                                                            <option value="1" @selected($val->modifier == 1)>+1</option>
                                                            <option value="2" @selected($val->modifier == 2)>+2</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                                <button type="button" onclick="addValueRow(this, {{ $i }})" class="pf-chip">+ Nilai</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="pf-note">Simpan produk dulu untuk menambah atribut.</p>
                        @endif
                    </div>

                    {{-- Panel Varian --}}
                    <div class="pf-section" data-prod-panel="varian" style="display:none">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <p class="pf-label" style="margin:0">Varian Produk</p>
                            @if ($product->exists)
                                <button type="button" onclick="openAddVariantModal()" class="pf-btn is-primary">+ Varian</button>
                            @endif
                        </div>

                        @if ($product->exists)
                            @if ($product->variants->count())
                                <table style="width:100%;margin-top:16px;font-size:13px;border-collapse:collapse">
                                    <thead>
                                        <tr style="border-bottom:1px solid #e1e1e1">
                                            <th style="padding:10px;text-align:left;font-size:11px;color:#888;font-weight:600">SKU</th>
                                            <th style="padding:10px;text-align:left;font-size:11px;color:#888;font-weight:600">Nama</th>
                                            <th style="padding:10px;text-align:left;font-size:11px;color:#888;font-weight:600">Harga</th>
                                            <th style="padding:10px;text-align:left;font-size:11px;color:#888;font-weight:600">Berat</th>
                                            <th style="padding:10px;text-align:left;font-size:11px;color:#888;font-weight:600">Status</th>
                                            <th style="padding:10px;text-align:center;font-size:11px;color:#888;font-weight:600">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($product->variants as $variant)
                                            <tr style="border-bottom:1px solid #e1e1e1">
                                                <td style="padding:10px;font-size:12px;color:#888">{{ $variant->sku }}</td>
                                                <td style="padding:10px">{{ $variant->name }}</td>
                                                <td style="padding:10px">{{ $variant->price_rmb ? '¥'.number_format($variant->price_rmb, 2) : '—' }}</td>
                                                <td style="padding:10px">{{ $variant->weight_gram ? $variant->weight_gram.'g' : '—' }}</td>
                                                <td style="padding:10px">
                                                    <span class="pf-chip {{ $variant->availability === 'tersedia' ? 'is-on' : '' }}">{{ $variant->availability }}</span>
                                                </td>
                                                <td style="padding:10px;text-align:center">
                                                    <button type="button" onclick="editVariant({{ $variant->id }})" class="pf-btn">Edit</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="pf-note">Belum ada varian. Klik "+ Varian" untuk menambah.</p>
                            @endif
                        @else
                            <p class="pf-note">Simpan produk dulu untuk menambah varian.</p>
                        @endif
                    </div>
                </div>

                {{-- Deskripsi Gambar --}}
                <div class="pf-section">
                    <p class="pf-label">Deskripsi Produk (Gambar Panjang)</p>
                    <input type="file" id="desc-image-input" name="description_image" accept=".jpg,.jpeg,.png,.webp" style="margin-top:8px" class="pf-input" style="height:auto;padding:8px">
                    @if ($product->description_image_path)
                        <div style="margin-top:12px">
                            <img src="{{ asset('storage/'.$product->description_image_path) }}" style="max-width:200px;border:1px solid #e1e1e1">
                            <label style="display:block;margin-top:8px;font-size:12px">
                                <input type="checkbox" name="remove_description_image" value="1"> Hapus gambar
                            </label>
                        </div>
                    @endif
                    @error('description_image')<p class="pf-err">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="pf-side">
                <div class="pf-side-section">
                    <p class="pf-side-label">Status</p>
                    @if ($product->exists && $product->is_active)
                        <span class="pf-chip is-on">Published</span>
                    @else
                        <span class="pf-chip">Draft</span>
                    @endif
                    <button type="submit" onclick="document.getElementById('input-is-active').value='0'" class="pf-btn" style="width:100%;margin-top:10px">Simpan Draft</button>
                </div>

                <div class="pf-side-section">
                    <p class="pf-side-label">Ketersediaan</p>
                    <select name="availability" class="pf-select">
                        <option value="tersedia" @selected(old('availability', $product->availability ?: 'tersedia') === 'tersedia')>Tersedia</option>
                        <option value="kosong" @selected(old('availability') === 'kosong' || $product->availability === 'kosong')>Kosong</option>
                    </select>

                    <p class="pf-side-label" style="margin-top:16px">Store</p>
                    <select name="store_id" class="pf-select">
                        <option value="">— default game —</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" @selected(old('store_id', $product->store_id) == $store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($product->exists)
                    <div class="pf-side-section">
                        <p class="pf-side-label">Order</p>
                        <p style="margin:0;font-size:24px;font-weight:700">{{ $orderCount }}</p>
                    </div>
                @endif

                <div class="pf-side-section">
                    <p class="pf-side-label">Tag</p>
                    @foreach ($tags as $tag)
                        <label style="display:flex;align-items:center;gap:8px;margin:6px 0;font-size:13px">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $productTags))>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>

                <div class="pf-side-section">
                    <p class="pf-side-label">Foto Produk</p>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="pf-input" style="height:auto;padding:8px">
                    @if ($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}" style="width:100%;margin-top:8px;border:1px solid #e1e1e1">
                    @endif
                    @error('image')<p class="pf-err">{{ $message }}</p>@enderror
                </div>

                <div class="pf-side-section">
                    <p class="pf-side-label">Diskon</p>
                    <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $product->discount_amount) }}" placeholder="Nominal RMB" class="pf-input">
                    <div class="pf-grid-2" style="margin-top:8px">
                        <input type="date" name="discount_start" value="{{ old('discount_start', $product->discount_start?->toDateString()) }}" class="pf-input" style="font-size:12px">
                        <input type="date" name="discount_end" value="{{ old('discount_end', $product->discount_end?->toDateString()) }}" class="pf-input" style="font-size:12px">
                    </div>
                </div>

                @if ($product->exists)
                    <div class="pf-side-section">
                        <p class="pf-side-label">Split Box</p>
                        @if (! $product->is_split_box)
                            <button type="button" onclick="openSplitCloneModal()" class="pf-btn" style="width:100%">Buat Split Box</button>
                        @else
                            <span class="pf-chip is-on">Aktif</span>
                            @if ($product->parent_id)
                                <a href="{{ route('office.products.edit', $product->parent_id) }}" class="pf-btn" style="width:100%;margin-top:8px">Lihat Produk Asli</a>
                            @endif
                        @endif
                    </div>

                    <div class="pf-side-section">
                        <p class="pf-side-label">Aksi</p>
                        <form method="POST" action="{{ route('office.products.duplicate', $product) }}" style="margin:0">
                            @csrf
                            <button type="submit" class="pf-btn" style="width:100%">Salin Produk</button>
                        </form>
                        <button type="button" onclick="openDeleteModal()" class="pf-btn is-danger" style="width:100%;margin-top:8px">Hapus Produk</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>

{{-- Split Box Manager --}}
@if ($product->exists)
    <div style="width:min(1300px, calc(100vw - 48px));margin:0 auto 24px">
        <livewire:split-box-manager :product="$product" />
    </div>
@endif

<script>
(function () {
    // Tabs
    document.querySelectorAll('[data-prod-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-prod-tab]').forEach(function (t) { t.classList.remove('is-on'); });
            document.querySelectorAll('[data-prod-panel]').forEach(function (p) { p.style.display = 'none'; });
            tab.classList.add('is-on');
            document.querySelector('[data-prod-panel="' + tab.getAttribute('data-prod-tab') + '"]').style.display = '';
        });
    });

    // Attribute rows
    var attrIndex = {{ $product->exists ? $product->attributes->count() : 0 }};

    window.addAttributeRow = function () {
        var list = document.getElementById('attributes-list');
        if (!list) return;
        var html = '<div class="pf-attr-box">'
            + '<div class="pf-attr-head">'
            + '<input type="text" name="attributes[' + attrIndex + '][name]" class="pf-input" style="flex:1" placeholder="Nama atribut">'
            + '<button type="button" onclick="this.closest(\'.pf-attr-box\').remove()" class="pf-btn is-danger">Hapus</button>'
            + '</div>'
            + '<div class="pf-attr-values">'
            + '<button type="button" onclick="addValueRow(this, ' + attrIndex + ')" class="pf-chip">+ Nilai</button>'
            + '</div></div>';
        list.insertAdjacentHTML('beforeend', html);
        attrIndex++;
    };

    window.addValueRow = function (btn, attrIdx) {
        var container = btn.parentElement;
        var valIndex = container.querySelectorAll('.pf-chip').length;
        var html = '<div class="pf-chip">'
            + '<input type="text" name="attributes[' + attrIdx + '][values][' + valIndex + '][value]" style="width:80px;border:0;background:transparent;font-size:12px" placeholder="Nilai">'
            + '<select name="attributes[' + attrIdx + '][values][' + valIndex + '][modifier]" style="border:0;background:transparent;font-size:11px;width:50px">'
            + '<option value="0">—</option><option value="1">+1</option><option value="2">+2</option>'
            + '</select></div>';
        btn.insertAdjacentHTML('beforebegin', html);
    };
})();
</script>

@php
    $jsConfig = [
        'cfg' => $cfg,
        'productExists' => $product->exists,
        'variantBaseUrl' => $product->exists ? url('office/products/'.$product->id.'/variants') : '',
        'csrfToken' => csrf_token(),
    ];
@endphp
<script type="application/json" id="product-form-config">{!! json_encode($jsConfig) !!}</script>
<script src="{{ asset('js/admin/product-form.js') }}"></script>
@endsection
