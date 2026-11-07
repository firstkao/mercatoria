@extends('admin.layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
@php
    $productTags = old('tags', $product->exists ? $product->tags->pluck('id')->all() : []);
    $orderCount = $product->exists ? $product->ordersCount() : 0;
    $developerList = $developers ?? [];
    $currentType = old('type', $product->type ?: 'single');
    $isSplitBox = $product->exists && $product->is_split_box;
    $isPublished = $product->exists && $product->is_active;
    $attrCount = $product->exists ? $product->attributes->count() : 0;
    $weightRequired = (int) setting('default_weight_gram', 0) <= 0;
@endphp

<style>
    .pf, .pf *, .pf *::before, .pf *::after { box-sizing: border-box; }
    .pf { font-family: 'Roboto', system-ui, sans-serif; font-size: 14px; line-height: 20px; color: #333; -webkit-font-smoothing: antialiased; }
    .pf a { text-decoration: none; color: inherit; }
    .pf-wrap { position: relative; left: 50%; transform: translateX(-50%); width: min(1300px, calc(100vw - 48px)); margin: 24px 0; }

    /* Header */
    .pf-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
    .pf-header h1 { margin: 0; font-size: 22px; line-height: 28px; font-weight: 500; color: #26222f; }
    .pf-header-actions { display: flex; flex-wrap: wrap; gap: 8px; }

    .pf-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 24px; align-items: start; }
    @media (max-width: 960px) { .pf-layout { grid-template-columns: 1fr; } }

    /* Kartu section */
    .pf-card { margin-bottom: 20px; border: 1px solid #ddd; background: #fff; }
    .pf-card-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 48px; padding: 0 20px; border-bottom: 1px solid #ddd; background: #fbfbfb; }
    .pf-card-h h2 { margin: 0; font-size: 16px; line-height: 47px; font-weight: 500; color: #26222f; }
    .pf-card-b { padding: 20px; }

    /* Form dasar */
    .pf-label { display: block; margin: 0 0 6px; font-size: 13px; line-height: 18px; font-weight: 500; color: #26222f; }
    .pf-input, .pf-select { width: 100%; height: 38px; padding: 0 12px; border: 1px solid #ddd; border-radius: 0; background: #fff; font: 400 14px/36px 'Roboto', system-ui, sans-serif; color: #26222f; }
    .pf-input:focus, .pf-select:focus { outline: 0; border-color: #26222f; }
    .pf-input:disabled { background: #f7f7f7; color: #999; }
    .pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .pf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    @media (max-width: 700px) { .pf-grid-2, .pf-grid-3 { grid-template-columns: 1fr; } }
    .pf-err { margin: 4px 0 0; font-size: 12px; color: #c62828; }
    .pf-note { margin: 8px 0 0; font-size: 12px; line-height: 17px; color: #888; }
    .pf-req { color: #c62828; font-style: normal; }

    /* Tombol */
    .pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 16px; border: 1px solid #ddd; border-radius: 2px; background: #fff; font: 500 13px/34px 'Roboto', system-ui, sans-serif; color: #26222f; cursor: pointer; transition: border-color .15s, background .15s; }
    .pf-btn:hover { border-color: #26222f; }
    .pf-btn.is-primary { background: #26222f; border-color: #26222f; color: #fff; }
    .pf-btn.is-primary:hover { background: #3a3547; }
    .pf-btn.is-danger { color: #c62828; border-color: #f3c4c4; }
    .pf-btn.is-danger:hover { background: #fff5f5; border-color: #c62828; }
    .pf-btn.is-sm { height: 30px; padding: 0 12px; font-size: 12px; line-height: 28px; }
    .pf-btn.is-block { width: 100%; }
    .pf-btn:disabled { opacity: .6; cursor: default; }

    /* Chip & status */
    .pf-chip { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border: 1px solid #ddd; background: #fff; font-size: 12px; color: #26222f; }
    .pf-chip.is-on { background: #43A047; border-color: #43A047; color: #fff; }

    /* Pesan */
    .pf-flash { margin: 0 0 16px; padding: 12px 16px; border: 1px solid #c8e6c9; background: #e8f5e9; font-size: 13px; color: #2e7d32; }
    .pf-flash.is-error { border-color: #f3c4c4; background: #fff5f5; color: #c62828; }
    .pf-flash.is-info { border-color: #ddd; background: #f7f6f7; color: #26222f; }
    .pf-flash[hidden] { display: none; }

    /* Data produk (gaya WooCommerce: tab vertikal) */
    .pf-type { height: 34px; width: auto; min-width: 180px; }
    .pf-pd { display: grid; grid-template-columns: 170px minmax(0, 1fr); }
    .pf-pd-tabs { border-right: 1px solid #ddd; background: #fbfbfb; }
    .pf-tab { display: block; width: 100%; padding: 12px 18px; border: 0; border-bottom: 1px solid #ddd; border-left: 3px solid transparent; background: none; text-align: left; font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #666; cursor: pointer; }
    .pf-tab:hover { color: #26222f; }
    .pf-tab.is-on { background: #fff; color: #26222f; font-weight: 500; border-left-color: #26222f; }
    .pf-tab[hidden] { display: none; }
    .pf-panel { padding: 20px; min-width: 0; }
    .pf-panel[hidden] { display: none; }
    @media (max-width: 700px) {
        .pf-pd { grid-template-columns: 1fr; }
        .pf-pd-tabs { display: flex; border-right: 0; border-bottom: 1px solid #ddd; }
        .pf-tab { border-bottom: 0; border-left: 0; border-bottom: 3px solid transparent; text-align: center; }
        .pf-tab.is-on { border-left-color: transparent; border-bottom-color: #26222f; }
    }

    /* Atribut */
    .pf-attr { margin-bottom: 14px; border: 1px solid #ddd; background: #fff; }
    .pf-attr-top { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid #ddd; background: #fbfbfb; }
    .pf-attr-top .pf-input { flex: 1 1 160px; max-width: 240px; }
    .pf-check { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #555; cursor: pointer; }
    .pf-check input { width: 15px; height: 15px; margin: 0; accent-color: #26222f; }
    .pf-attr-b { padding: 14px; }
    .pf-vals { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .pf-val { display: inline-flex; align-items: center; gap: 2px; height: 30px; padding: 0 4px 0 8px; border: 1px solid #ddd; background: #f7f7f7; }
    .pf-val input[type="text"] { height: 28px; padding: 0; border: 0; background: transparent; font: 400 13px/28px 'Roboto', system-ui, sans-serif; color: #26222f; outline: 0; }
    .pf-val select { border: 0; background: transparent; font-size: 11px; width: 46px; }
    .pf-val-x { width: 22px; height: 22px; padding: 0; border: 0; background: none; color: #999; font-size: 16px; line-height: 20px; cursor: pointer; }
    .pf-val-x:hover { color: #c62828; }
    .pf-val-add { flex: 1 1 200px; min-width: 160px; max-width: 280px; height: 30px; padding: 0 10px; border: 1px dashed #bbb; background: #fff; font: 400 13px/28px 'Roboto', system-ui, sans-serif; color: #26222f; }
    .pf-val-add:focus { outline: 0; border-color: #26222f; border-style: solid; }
    .pf-gen { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 18px; padding-top: 18px; border-top: 1px solid #ddd; }
    .pf-gen-hint { font-size: 13px; color: #888; }

    /* Varian */
    .pf-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 14px; padding: 12px; border: 1px solid #ddd; background: #fbfbfb; }
    .pf-bulk .pf-select, .pf-bulk .pf-input { width: auto; height: 34px; }
    .pf-bulk .pf-input { width: 120px; }
    .pf-table-wrap { overflow-x: auto; }
    .var-table { width: 100%; min-width: 640px; border-collapse: collapse; font-size: 13px; }
    .var-table th { padding: 10px 8px; text-align: left; font-size: 12px; font-weight: 500; color: #888; border-bottom: 1px solid #ddd; white-space: nowrap; }
    .var-table td { padding: 10px 8px; border-bottom: 1px solid #ddd; vertical-align: middle; }
    .var-table tbody tr:last-child td { border-bottom: 0; }
    .var-table .pf-input, .var-table .pf-select { height: 32px; font-size: 13px; line-height: 30px; }
    .var-table input[type="checkbox"] { width: 15px; height: 15px; accent-color: #26222f; }
    .var-table .is-missing { border-color: #c62828; }
    .pf-empty { padding: 36px 20px; border: 1px dashed #ccc; text-align: center; color: #888; }

    /* Sidebar */
    .pf-side-card { margin-bottom: 20px; border: 1px solid #ddd; background: #fff; }
    .pf-side-sec { padding: 16px 18px; border-bottom: 1px solid #ddd; }
    .pf-side-sec:last-child { border-bottom: 0; }
    .pf-side-label { margin: 0 0 8px; font-size: 12px; line-height: 16px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #888; }
    .pf-side-label + .pf-select, .pf-side-label + .pf-input { margin-bottom: 0; }
    .pf-side-gap { margin-top: 14px; }
    .pf-tag { display: flex; align-items: center; gap: 8px; margin: 6px 0; font-size: 13px; }
    .pf-tag input { accent-color: #26222f; }
    .pf-img { display: block; width: 100%; margin-top: 8px; border: 1px solid #ddd; }
</style>

<div class="pf-wrap pf">

    @if (session('status'))
        <div class="pf-flash">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="pf-flash is-error">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    <div class="pf-flash" id="pf-msg" hidden></div>

    <form method="POST" action="{{ $product->exists ? route('office.products.update', $product) : route('office.products.store') }}" enctype="multipart/form-data" id="product-form" novalidate>
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <input type="hidden" name="is_active" id="input-is-active" value="{{ old('is_active', $product->exists ? ($product->is_active ? 1 : 0) : 1) }}">
        <input type="hidden" name="attributes_present" value="1">
        <input type="hidden" name="is_split_box" value="{{ old('is_split_box', $product->exists ? ($product->is_split_box ? 1 : 0) : 0) }}">

        <div class="pf-header">
            <h1>{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h1>
            <div class="pf-header-actions">
                @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                    <a href="{{ route('office.products.preview', $product) }}" target="_blank" class="pf-btn">Preview</a>
                @endif
                <button type="submit" onclick="document.getElementById('input-is-active').value='0'" class="pf-btn">Simpan Draft</button>
                <button type="submit" onclick="document.getElementById('input-is-active').value='1'" class="pf-btn is-primary">{{ $isPublished ? 'Perbarui' : 'Terbitkan' }}</button>
            </div>
        </div>

        <div class="pf-layout">
            {{-- ═════════ KIRI ═════════ --}}
            <div>
                {{-- Informasi produk (selalu tampil, tanpa field wajib khusus produk simple) --}}
                <section class="pf-card">
                    <div class="pf-card-h"><h2>Informasi Produk</h2></div>
                    <div class="pf-card-b">
                        <div class="pf-grid-2">
                            <div>
                                <label class="pf-label">Nama Produk</label>
                                <input type="text" name="name" id="field-name" value="{{ old('name', $product->name === 'Draft tanpa judul' ? '' : $product->name) }}" class="pf-input" placeholder="Nama produk">
                                @error('name')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Slug</label>
                                <input type="text" name="slug" id="field-slug" value="{{ old('slug', $product->slug) }}" class="pf-input" placeholder="otomatis dari nama produk">
                                <input type="hidden" name="slug_auto" id="field-slug-auto" value="">
                                @error('slug')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="pf-label">Game <i class="pf-req">*</i></label>
                                <select name="game_id" id="field-game" class="pf-select">
                                    @foreach ($games as $game)
                                        <option value="{{ $game->id }}" @selected(old('game_id', $product->game_id) == $game->id)>{{ $game->name }}</option>
                                    @endforeach
                                </select>
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
                        </div>
                    </div>
                </section>

                {{-- Data produk (gaya WooCommerce) --}}
                <section class="pf-card">
                    <div class="pf-card-h">
                        <h2>Data Produk</h2>
                        <select name="type" id="input-type" class="pf-select pf-type" aria-label="Tipe produk">
                            <option value="single" @selected($currentType === 'single')>Produk Simple</option>
                            <option value="variable" @selected($currentType === 'variable')>Produk Variable</option>
                        </select>
                    </div>

                    <div class="pf-pd">
                        <div class="pf-pd-tabs" role="tablist">
                            <button type="button" class="pf-tab is-on" data-prod-tab="general" data-for="single">General</button>
                            <button type="button" class="pf-tab" data-prod-tab="atribut" data-for="variable" hidden>Atribut</button>
                            <button type="button" class="pf-tab" data-prod-tab="varian" data-for="variable" hidden>Varian</button>
                        </div>

                        <div>
                            {{-- General: hanya Produk Simple --}}
                            <div class="pf-panel" data-prod-panel="general">
                                <div class="pf-grid-3">
                                    <div>
                                        <label class="pf-label">SKU <i class="pf-req">*</i></label>
                                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" data-simple-only required class="pf-input">
                                        @error('sku')<p class="pf-err">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="pf-label">Harga RMB <i class="pf-req">*</i></label>
                                        <input type="number" step="0.01" min="0" name="price_rmb" value="{{ old('price_rmb', $product->price_rmb) }}" data-simple-only required class="pf-input">
                                        @error('price_rmb')<p class="pf-err">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="pf-label">Berat (gram) <i class="pf-req">*</i></label>
                                        <input type="number" min="1" name="weight_gram" value="{{ old('weight_gram', $product->weight_gram) }}" data-simple-only required class="pf-input">
                                        @error('weight_gram')<p class="pf-err">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Atribut --}}
                            <div class="pf-panel" data-prod-panel="atribut" hidden>
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap">
                                    <p style="margin:0;font-size:13px;color:#888">Tambah atribut (misal: Karakter), lalu isi nilainya (Luke, Artem, ...).</p>
                                    <button type="button" id="btn-add-attr" class="pf-btn is-sm">+ Tambah Atribut</button>
                                </div>

                                <div id="attributes-list" data-attr-count="{{ $attrCount }}">
                                    @if ($product->exists)
                                        @foreach ($product->attributes as $i => $attr)
                                            <div class="pf-attr" data-attr="{{ $i }}" data-next="{{ $attr->values->count() }}">
                                                <div class="pf-attr-top">
                                                    <input type="text" name="attributes[{{ $i }}][name]" value="{{ $attr->name }}" class="pf-input" placeholder="Nama atribut">
                                                    <input type="hidden" name="attributes[{{ $i }}][id]" value="{{ $attr->id }}">
                                                    <button type="button" class="pf-btn is-danger is-sm" data-remove-attr style="margin-left:auto">Hapus</button>
                                                </div>
                                                <div class="pf-attr-b">
                                                    <div class="pf-vals">
                                                        @foreach ($attr->values as $j => $val)
                                                            <span class="pf-val" data-val>
                                                                <input type="hidden" name="attributes[{{ $i }}][values][{{ $j }}][id]" value="{{ $val->id }}">
                                                                <input type="text" name="attributes[{{ $i }}][values][{{ $j }}][value]" value="{{ $val->value }}" size="{{ max(4, mb_strlen($val->value)) }}">
                                                                @if ($isSplitBox)
                                                                    <select name="attributes[{{ $i }}][values][{{ $j }}][modifier]">
                                                                        <option value="0" @selected($val->modifier == 0)>—</option>
                                                                        <option value="1" @selected($val->modifier == 1)>+1</option>
                                                                        <option value="2" @selected($val->modifier == 2)>+2</option>
                                                                    </select>
                                                                @else
                                                                    <input type="hidden" name="attributes[{{ $i }}][values][{{ $j }}][modifier]" value="0">
                                                                @endif
                                                                <button type="button" class="pf-val-x" data-remove-val aria-label="Hapus nilai">&times;</button>
                                                            </span>
                                                        @endforeach
                                                        <input type="text" class="pf-val-add" data-val-add placeholder="Tambah nilai, tekan Enter" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="pf-gen">
                                    <button type="button" id="btn-generate" class="pf-btn is-primary">Simpan &amp; Generate Varian</button>
                                    <span class="pf-gen-hint" id="gen-hint"></span>
                                </div>
                            </div>

                            {{-- Varian --}}
                            <div class="pf-panel" data-prod-panel="varian" hidden>
                                @if ($product->exists && $product->type === 'variable' && $product->variants->count())
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                                        <p style="margin:0;font-size:15px;font-weight:500;color:#26222f">Varian ({{ $product->variants->count() }})</p>
                                        <p style="margin:0;font-size:12px;color:#888">Harga &amp; berat diisi per varian.</p>
                                    </div>

                                    <div class="pf-bulk">
                                        <select id="bulk-action" class="pf-select" aria-label="Aksi massal">
                                            <option value="price">Set Harga RMB</option>
                                            <option value="weight">Set Berat (gram)</option>
                                            <option value="availability">Set Ketersediaan</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" id="bulk-value" class="pf-input" placeholder="Nilai">
                                        <select id="bulk-avail" class="pf-select" hidden>
                                            <option value="tersedia">Tersedia</option>
                                            <option value="kosong">Kosong</option>
                                        </select>
                                        <button type="button" id="btn-bulk" class="pf-btn is-sm">Terapkan</button>
                                        <span class="pf-gen-hint">ke varian yang dicentang (atau semua bila tidak ada yang dicentang)</span>
                                    </div>

                                    <div class="pf-table-wrap">
                                        <table class="var-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:28px"><input type="checkbox" id="check-all-variants" aria-label="Pilih semua varian"></th>
                                                    <th>Varian</th>
                                                    <th>SKU</th>
                                                    <th>Harga RMB</th>
                                                    <th>Berat (gram)</th>
                                                    <th>Stok</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($product->variants as $variant)
                                                    <tr data-variant-row>
                                                        <td><input type="checkbox" class="variant-check" value="{{ $variant->id }}"></td>
                                                        <td>
                                                            <div style="font-weight:500;color:#26222f">{{ $variant->name }}</div>
                                                            <div style="font-size:12px;color:#888;margin-top:2px">
                                                                @foreach ($variant->attributeValues as $av)
                                                                    <span>{{ $av->attribute->name }}: {{ $av->value }}</span>@if (!$loop->last), @endif
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                        <td style="font-size:12px;color:#666">{{ $variant->sku }}</td>
                                                        @php
                                                            $vPrice = $variant->price_rmb ?? $product->price_rmb;
                                                            $vWeight = $variant->weight_gram ?? $product->weight_gram;
                                                        @endphp
                                                        <td><input type="number" step="0.01" min="0" name="variants[{{ $variant->id }}][price_rmb]" value="{{ (float) $vPrice > 0 ? $vPrice : '' }}" class="pf-input" style="width:110px" data-v-price placeholder="Wajib"></td>
                                                        <td><input type="number" min="1" name="variants[{{ $variant->id }}][weight_gram]" value="{{ (int) $vWeight > 0 ? $vWeight : '' }}" class="pf-input" style="width:100px" data-v-weight placeholder="{{ $weightRequired ? 'Wajib' : 'Default' }}"></td>
                                                        <td>
                                                            <select name="variants[{{ $variant->id }}][availability]" class="pf-select" style="width:110px" data-v-avail>
                                                                <option value="tersedia" @selected($variant->availability === 'tersedia')>Tersedia</option>
                                                                <option value="kosong" @selected($variant->availability === 'kosong')>Kosong</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="pf-empty">
                                        <p style="margin:0;color:#555">Belum ada varian.</p>
                                        <p style="margin:6px 0 0;font-size:13px">Buka tab <b>Atribut</b>, isi atribut &amp; nilainya, lalu klik <b>Simpan &amp; Generate Varian</b>.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Deskripsi --}}
                <section class="pf-card">
                    <div class="pf-card-h"><h2>Deskripsi Produk (Gambar Panjang)</h2></div>
                    <div class="pf-card-b">
                        <input type="file" name="description_image" accept=".jpg,.jpeg,.png,.webp" class="pf-input" style="height:auto;padding:8px">
                        @if ($product->description_image_path)
                            <div style="margin-top:12px">
                                <img src="{{ asset('storage/'.$product->description_image_path) }}" style="max-width:200px;border:1px solid #ddd" alt="">
                                <label class="pf-check" style="display:flex;margin-top:8px">
                                    <input type="checkbox" name="remove_description_image" value="1"> Hapus gambar
                                </label>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            {{-- ═════════ SIDEBAR ═════════ --}}
            <aside>
                <div class="pf-side-card">
                    <div class="pf-side-sec">
                        <p class="pf-side-label">Status</p>
                        @if ($isPublished)
                            <span class="pf-chip is-on">Published</span>
                        @else
                            <span class="pf-chip">Draft</span>
                        @endif
                    </div>
                    <div class="pf-side-sec">
                        <p class="pf-side-label">Ketersediaan</p>
                        <select name="availability" class="pf-select">
                            <option value="tersedia" @selected(old('availability', $product->availability ?: 'tersedia') === 'tersedia')>Tersedia</option>
                            <option value="kosong" @selected(old('availability') === 'kosong' || $product->availability === 'kosong')>Kosong</option>
                        </select>
                        <p class="pf-side-label pf-side-gap">Store</p>
                        <select name="store_id" class="pf-select">
                            <option value="">— default game —</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('store_id', $product->store_id) == $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <p class="pf-side-label pf-side-gap">Kategori</p>
                        <select name="merch_category_id" class="pf-select">
                            <option value="">— auto —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($product->exists)
                        <div class="pf-side-sec">
                            <p class="pf-side-label">Order</p>
                            <p style="margin:0;font-size:24px;line-height:30px;font-weight:500;color:#26222f">{{ $orderCount }}</p>
                        </div>
                    @endif
                    <div class="pf-side-sec">
                        <p class="pf-side-label">Tag</p>
                        @foreach ($tags as $tag)
                            <label class="pf-tag">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $productTags))>
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                    <div class="pf-side-sec">
                        <p class="pf-side-label">Foto Produk</p>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="pf-input" style="height:auto;padding:8px">
                        @if ($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" class="pf-img" alt="">
                        @endif
                    </div>
                    <div class="pf-side-sec">
                        <p class="pf-side-label">Diskon</p>
                        <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $product->discount_amount) }}" placeholder="Nominal RMB" class="pf-input">
                        <div class="pf-grid-2" style="margin-top:8px;gap:8px">
                            <input type="date" name="discount_start" value="{{ old('discount_start', $product->discount_start?->toDateString()) }}" class="pf-input" style="font-size:12px">
                            <input type="date" name="discount_end" value="{{ old('discount_end', $product->discount_end?->toDateString()) }}" class="pf-input" style="font-size:12px">
                        </div>
                    </div>
                    @if ($product->exists)
                        <div class="pf-side-sec">
                            <p class="pf-side-label">Split Box</p>
                            @if (! $product->is_split_box)
                                <a href="#split-box-manager" class="pf-btn is-block">Kelola Split Box</a>
                            @else
                                <span class="pf-chip is-on">Aktif</span>
                                @if ($product->parent_id)
                                    <a href="{{ route('office.products.edit', $product->parent_id) }}" class="pf-btn is-block" style="margin-top:8px">Lihat Produk Asli</a>
                                @endif
                            @endif
                        </div>
                        <div class="pf-side-sec">
                            <p class="pf-side-label">Aksi</p>
                            <button type="submit" form="form-duplicate" class="pf-btn is-block">Salin Produk</button>
                            <button type="button" onclick="if(confirm('Hapus produk ini?')) document.getElementById('form-delete').submit()" class="pf-btn is-danger is-block" style="margin-top:8px">Hapus Produk</button>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </form>

    @if ($product->exists)
        <form id="form-duplicate" method="POST" action="{{ route('office.products.duplicate', $product) }}" style="display:none">@csrf</form>
        <form id="form-delete" method="POST" action="{{ route('office.products.destroy', $product) }}" style="display:none">
            @csrf @method('DELETE')
        </form>
        <div id="split-box-manager" style="margin-top:4px">
            <livewire:split-box-manager :product="$product" />
        </div>
    @endif
</div>

<script>
(function () {
    var form = document.getElementById('product-form');
    var isSplitBox = {{ $isSplitBox ? 'true' : 'false' }};
    var productExists = {{ $product->exists ? 'true' : 'false' }};
    var generateUrl = '{{ $product->exists ? route('office.products.generate-variations', $product) : '' }}';
    var editUrlTemplate = '{{ route('office.products.edit', ['product' => '__ID__']) }}';
    var csrf = '{{ csrf_token() }}';
    var weightRequired = {{ $weightRequired ? 'true' : 'false' }};
    var isPublished = {{ $isPublished ? 'true' : 'false' }};
    var typeSel = document.getElementById('input-type');
    var msg = document.getElementById('pf-msg');
    var attrList = document.getElementById('attributes-list');
    var attrIndex = parseInt(attrList.getAttribute('data-attr-count'), 10) || 0;

    /* ───────── Pesan inline (pengganti alert) ───────── */
    function flash(kind, text) {
        msg.hidden = false;
        msg.className = 'pf-flash' + (kind === 'error' ? ' is-error' : kind === 'info' ? ' is-info' : '');
        msg.innerHTML = '';
        (Array.isArray(text) ? text : [text]).forEach(function (t) {
            var d = document.createElement('div'); d.textContent = t; msg.appendChild(d);
        });
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearFlash() { msg.hidden = true; }

    /* ───────── Tab & tipe produk ───────── */
    var tabs = document.querySelectorAll('[data-prod-tab]');
    var panels = document.querySelectorAll('[data-prod-panel]');

    function openTab(name) {
        tabs.forEach(function (t) { t.classList.toggle('is-on', t.getAttribute('data-prod-tab') === name); });
        panels.forEach(function (p) { p.hidden = p.getAttribute('data-prod-panel') !== name; });
    }

    function applyType() {
        var variable = typeSel.value === 'variable';
        tabs.forEach(function (t) { t.hidden = t.getAttribute('data-for') === (variable ? 'single' : 'variable'); });
        // SKU / harga / berat produk hanya untuk Simple (Variable: diisi per varian)
        document.querySelectorAll('[data-simple-only]').forEach(function (el) { el.disabled = variable; });
        var current = document.querySelector('[data-prod-tab].is-on');
        if (!current || current.hidden) openTab(variable ? 'atribut' : 'general');
        else openTab(current.getAttribute('data-prod-tab'));
        updateHint();
    }

    tabs.forEach(function (t) { t.addEventListener('click', function () { openTab(t.getAttribute('data-prod-tab')); }); });
    typeSel.addEventListener('change', applyType);

    /* ───────── Atribut & nilai ───────── */
    function valueHtml(i, j, text) {
        var mod = isSplitBox
            ? '<select name="attributes[' + i + '][values][' + j + '][modifier]"><option value="0">—</option><option value="1">+1</option><option value="2">+2</option></select>'
            : '<input type="hidden" name="attributes[' + i + '][values][' + j + '][modifier]" value="0">';
        return '<span class="pf-val" data-val>'
            + '<input type="text" name="attributes[' + i + '][values][' + j + '][value]" size="' + Math.max(4, text.length) + '">'
            + mod
            + '<button type="button" class="pf-val-x" data-remove-val aria-label="Hapus nilai">&times;</button></span>';
    }

    function addValue(box, raw) {
        var i = box.getAttribute('data-attr');
        var added = false;
        String(raw).split(/[|,\n]/).forEach(function (piece) {
            var text = piece.trim();
            if (!text) return;
            var exists = Array.prototype.some.call(box.querySelectorAll('.pf-val input[type="text"]'), function (inp) {
                return inp.value.trim().toLowerCase() === text.toLowerCase();
            });
            if (exists) return;
            var j = parseInt(box.getAttribute('data-next'), 10) || 0;
            box.setAttribute('data-next', j + 1);
            var adder = box.querySelector('[data-val-add]');
            adder.insertAdjacentHTML('beforebegin', valueHtml(i, j, text));
            adder.previousElementSibling.querySelector('input[type="text"]').value = text;
            added = true;
        });
        if (added) updateHint();
    }

    function addAttribute() {
        var i = attrIndex++;
        attrList.insertAdjacentHTML('beforeend',
            '<div class="pf-attr" data-attr="' + i + '" data-next="0">'
            + '<div class="pf-attr-top">'
            + '<input type="text" name="attributes[' + i + '][name]" class="pf-input" placeholder="Nama atribut">'
            + '<button type="button" class="pf-btn is-danger is-sm" data-remove-attr style="margin-left:auto">Hapus</button>'
            + '</div>'
            + '<div class="pf-attr-b"><div class="pf-vals">'
            + '<input type="text" class="pf-val-add" data-val-add placeholder="Tambah nilai, tekan Enter" autocomplete="off">'
            + '</div></div></div>');
        var box = attrList.lastElementChild;
        box.querySelector('.pf-attr-top input[type="text"]').focus();
        updateHint();
    }

    document.getElementById('btn-add-attr').addEventListener('click', addAttribute);

    attrList.addEventListener('click', function (e) {
        var rmVal = e.target.closest('[data-remove-val]');
        if (rmVal) { rmVal.closest('[data-val]').remove(); updateHint(); return; }
        var rmAttr = e.target.closest('[data-remove-attr]');
        if (rmAttr) { rmAttr.closest('.pf-attr').remove(); updateHint(); }
    });
    attrList.addEventListener('keydown', function (e) {
        var adder = e.target.closest('[data-val-add]');
        if (!adder) return;
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addValue(adder.closest('.pf-attr'), adder.value);
            adder.value = '';
        }
    });
    attrList.addEventListener('blur', function (e) {
        var adder = e.target.closest && e.target.closest('[data-val-add]');
        if (adder && adder.value.trim()) { addValue(adder.closest('.pf-attr'), adder.value); adder.value = ''; }
    }, true);
    attrList.addEventListener('change', updateHint);
    attrList.addEventListener('input', function (e) { if (e.target.matches('.pf-val input[type="text"]')) updateHint(); });

    /* Hitung jumlah kombinasi varian */
    function variationSets() {
        var sets = [];
        attrList.querySelectorAll('.pf-attr').forEach(function (box) {
            var nameInput = box.querySelector('.pf-attr-top input[type="text"]');
            var vals = Array.prototype.filter.call(box.querySelectorAll('.pf-val input[type="text"]'), function (i) { return i.value.trim(); });
            if (nameInput && nameInput.value.trim() && vals.length) sets.push(vals.length);
        });
        return sets;
    }
    function updateHint() {
        var hint = document.getElementById('gen-hint');
        if (!hint) return;
        var sets = variationSets();
        var total = sets.length ? sets.reduce(function (a, b) { return a * b; }, 1) : 0;
        hint.textContent = total ? 'Akan membuat ' + total + ' varian' : 'Isi minimal satu atribut beserta nilainya.';
    }

    /* ───────── Simpan atribut → generate varian (tanpa SKU/harga/berat) ───────── */
    function errorsFrom(data) {
        var out = [];
        if (data && data.errors) Object.keys(data.errors).forEach(function (k) { out = out.concat(data.errors[k]); });
        else if (data && data.message) out.push(data.message);
        return out.length ? out : ['Gagal menyimpan.'];
    }

    function saveDraft() {
        var fd = new FormData(form);
        if (!productExists) fd.set('is_active', '0');   // produk baru dibuat sebagai draft
        return fetch(form.action, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (res) {
            var json = (res.headers.get('content-type') || '').indexOf('json') > -1;
            if (res.status === 422) return res.json().then(function (d) { throw { list: errorsFrom(d) }; });
            if (!res.ok) throw { list: ['Gagal menyimpan (HTTP ' + res.status + ').'] };
            return json ? res.json() : {};
        });
    }

    function runGenerate(url) {
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) throw { list: [data.message || 'Gagal generate varian.'] };
                return data;
            });
    }

    function goVarian() { window.location.hash = 'varian'; window.location.reload(); }

    var btnGen = document.getElementById('btn-generate');
    btnGen.addEventListener('click', function () {
        clearFlash();
        var problems = [];
        if (!document.getElementById('field-game').value) problems.push('Pilih game.');
        if (!variationSets().length) problems.push('Tambahkan minimal satu atribut beserta nilainya.');
        if (problems.length) { flash('error', problems); return; }

        var label = btnGen.textContent;
        btnGen.disabled = true;
        btnGen.textContent = 'Menyimpan atribut…';

        saveDraft().then(function (data) {
            if (!productExists) {
                if (!data.product_id) throw { list: ['Produk tersimpan, tapi respons server tidak berisi product_id.'] };
                window.location.href = editUrlTemplate.replace('__ID__', data.product_id) + '?generate=1';
                return null;
            }
            btnGen.textContent = 'Membuat varian…';
            return runGenerate(generateUrl).then(goVarian);
        }).catch(function (err) {
            flash('error', err && err.list ? err.list : [(err && err.message) || 'Terjadi kesalahan.']);
            btnGen.disabled = false;
            btnGen.textContent = label;
        });
    });

    @if (request('generate') && $product->exists)
    /* Produk baru baru saja disimpan sebagai draft → langsung generate */
    document.addEventListener('DOMContentLoaded', function () {
        flash('info', 'Membuat varian…');
        runGenerate(generateUrl).then(function () {
            history.replaceState(null, '', window.location.pathname);
            goVarian();
        }).catch(function (err) {
            flash('error', err && err.list ? err.list : ['Gagal generate varian.']);
        });
    });
    @endif

    /* ───────── Varian: aksi massal ───────── */
    var bulkAction = document.getElementById('bulk-action');
    if (bulkAction) {
        var bulkValue = document.getElementById('bulk-value');
        var bulkAvail = document.getElementById('bulk-avail');
        bulkAction.addEventListener('change', function () {
            var isAvail = bulkAction.value === 'availability';
            bulkValue.hidden = isAvail; bulkAvail.hidden = !isAvail;
            bulkValue.step = bulkAction.value === 'weight' ? '1' : '0.01';
        });
        document.getElementById('btn-bulk').addEventListener('click', function () {
            var rows = Array.prototype.slice.call(document.querySelectorAll('[data-variant-row]'));
            var picked = rows.filter(function (r) { return r.querySelector('.variant-check').checked; });
            if (picked.length) rows = picked;
            var action = bulkAction.value;
            if (action !== 'availability' && bulkValue.value === '') { flash('error', 'Isi nilai terlebih dahulu.'); return; }
            clearFlash();
            rows.forEach(function (r) {
                if (action === 'price') r.querySelector('[data-v-price]').value = bulkValue.value;
                if (action === 'weight') r.querySelector('[data-v-weight]').value = bulkValue.value;
                if (action === 'availability') r.querySelector('[data-v-avail]').value = bulkAvail.value;
            });
        });
    }
    var checkAll = document.getElementById('check-all-variants');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.variant-check').forEach(function (c) { c.checked = checkAll.checked; });
        });
    }

    /* ───────── Validasi saat Terbitkan ───────── */
    form.addEventListener('submit', function (e) {
        clearFlash();
        var publish = document.getElementById('input-is-active').value === '1';
        var problems = [];

        if (publish && !document.getElementById('field-name').value.trim()) problems.push('Nama produk wajib diisi untuk menerbitkan.');

        if (typeSel.value === 'single') {
            if (publish) {
                form.querySelectorAll('[data-simple-only]').forEach(function (el) {
                    if (!el.value.trim()) problems.push('Lengkapi SKU, harga, dan berat produk.');
                });
            }
        } else if (publish) {
            var rows = document.querySelectorAll('[data-variant-row]');
            if (!rows.length) {
                problems.push('Produk variable belum punya varian. Generate varian dulu atau simpan sebagai draft.');
            } else {
                var missing = 0;
                rows.forEach(function (r) {
                    ['[data-v-price]', '[data-v-weight]'].forEach(function (sel) {
                        var el = r.querySelector(sel);
                        var needed = sel === '[data-v-price]' || weightRequired;
                        var bad = needed && (!el.value.trim() || parseFloat(el.value) <= 0);
                        el.classList.toggle('is-missing', bad);
                        if (bad) missing++;
                    });
                });
                if (missing) problems.push('Lengkapi harga' + (weightRequired ? ' dan berat' : '') + ' di semua varian (' + missing + ' kolom masih kosong).');
            }
        }

        // Duplikat pesan
        problems = problems.filter(function (p, i) { return problems.indexOf(p) === i; });
        if (problems.length) {
            e.preventDefault();
            if (typeSel.value === 'variable' && document.querySelector('.is-missing')) openTab('varian');
            flash('error', problems);
        }
    });

    /* ───────── Slug otomatis dari nama (seperti WooCommerce: berhenti setelah produk terbit / slug diubah manual) ───────── */
    var nameEl = document.getElementById('field-name');
    var slugEl = document.getElementById('field-slug');
    var slugAutoEl = document.getElementById('field-slug-auto');

    function slugify(s) {
        return String(s).normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    var slugManual = isPublished
        || (slugEl.value.trim() !== ''
            && slugEl.value.trim() !== slugify(nameEl.value)
            && !/^draft-tanpa-judul(-\d+)?$/.test(slugEl.value.trim()));

    function syncSlug() {
        slugAutoEl.value = slugManual ? '' : '1';
        if (!slugManual) slugEl.value = slugify(nameEl.value);
    }
    nameEl.addEventListener('input', function () { if (!slugManual) syncSlug(); });
    slugEl.addEventListener('input', function () {
        slugManual = slugEl.value.trim() !== '';   // dikosongkan = kembali otomatis
        syncSlug();
    });
    syncSlug();

    /* ───────── Init ───────── */
    applyType();
    var hash = window.location.hash.replace('#', '');
    if (typeSel.value === 'variable' && (hash === 'varian' || hash === 'atribut')) openTab(hash);
    else if (typeSel.value === 'variable' && document.querySelector('[data-variant-row]')) openTab('varian');
})();
</script>
@endsection
