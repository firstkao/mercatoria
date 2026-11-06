@extends('admin.layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
@php
    $productTags = old('tags', $product->exists ? $product->tags->pluck('id')->all() : []);
    $orderCount = $product->exists ? $product->ordersCount() : 0;
    $developerList = $developers ?? [];
    $currentType = old('type', $product->type ?: 'single');
    $isSplitBox = $product->exists && $product->is_split_box;
@endphp

<style>
    .pf, .pf *, .pf *::before, .pf *::after { box-sizing: border-box; }
    .pf { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
    .pf a { text-decoration: none; }
    .pf-box { position: relative; left: 50%; transform: translateX(-50%); width: min(1300px, calc(100vw - 48px)); margin: 24px 0; background: #fff; border: 1px solid #e1e1e1; }
    .pf-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e1e1e1; }
    .pf-header h1 { margin: 0; font-size: 16px; font-weight: 600; color: #000; }
    .pf-header-actions { display: flex; gap: 8px; }
    .pf-layout { display: grid; grid-template-columns: 1fr 300px; }
    .pf-main { border-right: 1px solid #e1e1e1; min-width: 0; }
    .pf-side { background: #fafafa; }
    @media (max-width: 900px) { .pf-layout { grid-template-columns: 1fr; } .pf-main { border-right: 0; border-bottom: 1px solid #e1e1e1; } }
    .pf-section { padding: 24px; border-bottom: 1px solid #e1e1e1; }
    .pf-section:last-child { border-bottom: 0; }
    .pf-label { display: block; margin: 0 0 6px; font-size: 12px; font-weight: 600; color: #555; }
    .pf-input, .pf-select { width: 100%; height: 36px; padding: 0 12px; border: 1px solid #e1e1e1; background: #fff; font: 400 13px/36px 'Roboto', system-ui, sans-serif; color: #333; }
    .pf-input:focus, .pf-select:focus { outline: 0; border-color: #333; }
    .pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .pf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    @media (max-width: 700px) { .pf-grid-2, .pf-grid-3 { grid-template-columns: 1fr; } }
    .pf-tabs { display: flex; gap: 0; border-bottom: 1px solid #e1e1e1; }
    .pf-tab { padding: 12px 20px; border: 0; border-bottom: 2px solid transparent; background: none; font: 400 13px/20px 'Roboto', system-ui, sans-serif; color: #888; cursor: pointer; }
    .pf-tab:hover { color: #333; }
    .pf-tab.is-on { color: #000; font-weight: 600; border-bottom-color: #000; }
    .pf-tab.is-disabled { opacity: .4; pointer-events: none; }
    .pf-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 16px; border: 1px solid #e1e1e1; background: #fff; font: 500 12px/34px 'Roboto', system-ui, sans-serif; color: #333; cursor: pointer; }
    .pf-btn:hover { border-color: #333; }
    .pf-btn.is-primary { background: #000; border-color: #000; color: #fff; }
    .pf-btn.is-primary:hover { background: #222; }
    .pf-btn.is-danger { color: #c62828; border-color: #ffcdd2; }
    .pf-btn.is-danger:hover { background: #ffebee; }
    .pf-chip { display: inline-flex; align-items: center; gap: 4px; height: 28px; padding: 0 8px; border: 1px solid #e1e1e1; background: #fff; font-size: 12px; color: #333; }
    .pf-chip.is-on { background: #000; color: #fff; border-color: #000; }
    .pf-attr-box { margin-top: 12px; padding: 16px; border: 1px solid #e1e1e1; background: #fff; }
    .pf-err { margin: 4px 0 0; font-size: 11px; color: #c62828; }
    .pf-note { margin: 8px 0 0; font-size: 11px; color: #888; }
    .pf-side-section { padding: 16px 20px; border-bottom: 1px solid #e1e1e1; }
    .pf-side-section:last-child { border-bottom: 0; }
    .pf-side-label { margin: 0 0 10px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #888; }
    .pf-flash { margin: 0 0 16px; padding: 12px 16px; border: 1px solid #c8e6c9; background: #e8f5e9; font-size: 13px; color: #2e7d32; }
    .pf-flash.is-error { border-color: #ffcdd2; background: #ffebee; color: #c62828; }
    .var-table { width: 100%; font-size: 13px; border-collapse: collapse; }
    .var-table th { padding: 10px; text-align: left; font-size: 11px; color: #888; font-weight: 600; border-bottom: 1px solid #e1e1e1; }
    .var-table td { padding: 10px; border-bottom: 1px solid #e1e1e1; vertical-align: middle; }
    .var-table input, .var-table select { height: 30px; font-size: 12px; }
    .type-selector { display: flex; gap: 12px; margin-bottom: 20px; }
    .type-option { flex: 1; padding: 16px; border: 2px solid #e1e1e1; cursor: pointer; text-align: center; transition: border-color .15s; }
    .type-option:hover { border-color: #999; }
    .type-option.is-on { border-color: #000; background: #fafafa; }
    .type-option strong { display: block; margin-bottom: 4px; }
    .type-option span { font-size: 12px; color: #888; }
</style>

@if (session('status'))
    <div class="pf-flash">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="pf-flash is-error">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ $product->exists ? route('office.products.update', $product) : route('office.products.store') }}" enctype="multipart/form-data" class="pf" id="product-form">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <input type="hidden" name="is_active" id="input-is-active" value="{{ old('is_active', $product->exists ? ($product->is_active ? 1 : 0) : 1) }}">
    <input type="hidden" name="is_split_box" value="{{ old('is_split_box', $product->exists ? ($product->is_split_box ? 1 : 0) : 0) }}">
    <input type="hidden" name="type" id="input-type" value="{{ $currentType }}">

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
                {{-- Type Selector --}}
                <div class="pf-section">
                    <p class="pf-label">Tipe Produk</p>
                    <div class="type-selector">
                        <div class="type-option {{ $currentType === 'single' ? 'is-on' : '' }}" data-type="single" onclick="setProductType('single')">
                            <strong>Simple</strong>
                            <span>Produk tanpa varian</span>
                        </div>
                        <div class="type-option {{ $currentType === 'variable' ? 'is-on' : '' }}" data-type="variable" onclick="setProductType('variable')">
                            <strong>Variable</strong>
                            <span>Produk dengan atribut & varian</span>
                        </div>
                    </div>

                    <div class="pf-grid-2">
                        <div>
                            <label class="pf-label">Nama Produk *</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="pf-input">
                            @error('name')<p class="pf-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pf-label">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="pf-input">
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div>
                    <div class="pf-tabs">
                        <button type="button" data-prod-tab="general" class="pf-tab is-on">General</button>
                        <button type="button" data-prod-tab="atribut" class="pf-tab" id="tab-atribut">Atribut</button>
                        <button type="button" data-prod-tab="varian" class="pf-tab" id="tab-varian">Varian</button>
                    </div>

                    {{-- General --}}
                    <div class="pf-section" data-prod-panel="general">
                        <div class="pf-grid-3">
                            <div>
                                <label class="pf-label">SKU *</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="pf-input">
                            </div>
                            <div>
                                <label class="pf-label">Harga RMB *</label>
                                <input type="number" step="0.01" name="price_rmb" value="{{ old('price_rmb', $product->price_rmb) }}" required class="pf-input">
                            </div>
                            <div>
                                <label class="pf-label">Berat (gram) *</label>
                                <input type="number" name="weight_gram" required min="1" value="{{ old('weight_gram', $product->weight_gram) }}" class="pf-input">
                            </div>
                        </div>
                        <div class="pf-grid-2" style="margin-top:16px">
                            <div>
                                <label class="pf-label">Game *</label>
                                <select name="game_id" required class="pf-select">
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
                        <p class="pf-note">Harga & berat ini jadi default untuk semua varian.</p>
                    </div>

                    {{-- Atribut --}}
                    <div class="pf-section" data-prod-panel="atribut" style="display:none">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                            <div>
                                <p style="margin:0;font-size:14px;font-weight:600">Atribut</p>
                                <p style="margin:4px 0 0;font-size:11px;color:#888">Tambah atribut untuk produk variable</p>
                            </div>
                            <button type="button" onclick="addAttributeRow()" class="pf-btn">+ Tambah Atribut</button>
                        </div>

                        <div id="attributes-list">
                            @if ($product->exists)
                                @foreach ($product->attributes as $i => $attr)
                                    <div class="pf-attr-box">
                                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap">
                                            <input type="text" name="attributes[{{ $i }}][name]" value="{{ $attr->name }}" class="pf-input" style="flex:1;min-width:150px;max-width:200px" placeholder="Nama atribut">
                                            <input type="hidden" name="attributes[{{ $i }}][id]" value="{{ $attr->id }}">
                                            <label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px">
                                                <input type="checkbox" name="attributes[{{ $i }}][visible]" value="1" checked> Tampilkan
                                            </label>
                                            <label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px">
                                                <input type="checkbox" name="attributes[{{ $i }}][variation]" value="1" checked> Untuk varian
                                            </label>
                                            <button type="button" onclick="this.closest('.pf-attr-box').remove()" class="pf-btn is-danger" style="margin-left:auto">Hapus</button>
                                        </div>
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">
                                            @foreach ($attr->values as $j => $val)
                                                <div class="pf-chip" style="padding-right:4px">
                                                    <input type="hidden" name="attributes[{{ $i }}][values][{{ $j }}][id]" value="{{ $val->id }}">
                                                    <input type="text" name="attributes[{{ $i }}][values][{{ $j }}][value]" value="{{ $val->value }}" style="width:80px;border:0;background:transparent;font-size:12px">
                                                    @if ($isSplitBox)
                                                        <select name="attributes[{{ $i }}][values][{{ $j }}][modifier]" style="border:0;background:transparent;font-size:11px;width:46px">
                                                            <option value="0" @selected($val->modifier == 0)>—</option>
                                                            <option value="1" @selected($val->modifier == 1)>+1</option>
                                                            <option value="2" @selected($val->modifier == 2)>+2</option>
                                                        </select>
                                                    @else
                                                        <input type="hidden" name="attributes[{{ $i }}][values][{{ $j }}][modifier]" value="0">
                                                    @endif
                                                    <button type="button" onclick="this.closest('.pf-chip').remove()" style="border:0;background:none;color:#999;cursor:pointer;padding:0 4px">&times;</button>
                                                </div>
                                            @endforeach
                                            <button type="button" onclick="addValueRow(this, {{ $i }})" class="pf-chip">+ Tambah</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div style="margin-top:16px">
                            <button type="button" onclick="generateVariations(this)" class="pf-btn is-primary">Generate Varian</button>
                        </div>
                    </div>

                    {{-- Varian --}}
                    <div class="pf-section" data-prod-panel="varian" style="display:none">
                        @if ($product->exists && $product->type === 'variable' && $product->variants->count())
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                                <p style="margin:0;font-size:14px;font-weight:600">Varian ({{ $product->variants->count() }})</p>
                                <div style="display:flex;gap:8px">
                                    <select id="bulk-action" class="pf-select" style="width:auto;height:34px">
                                        <option value="">Bulk Actions</option>
                                        <option value="price">Set Harga</option>
                                        <option value="weight">Set Berat</option>
                                        <option value="delete">Hapus</option>
                                    </select>
                                    <button type="button" onclick="applyBulkAction()" class="pf-btn">Terapkan</button>
                                </div>
                            </div>
                            <table class="var-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px"><input type="checkbox" id="check-all-variants"></th>
                                        <th>Varian</th>
                                        <th>SKU</th>
                                        <th>Harga</th>
                                        <th>Berat</th>
                                        <th>Stok</th>
                                        <th style="width:60px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->variants as $variant)
                                        <tr>
                                            <td><input type="checkbox" class="variant-check" value="{{ $variant->id }}"></td>
                                            <td>
                                                <div style="font-weight:500">{{ $variant->name }}</div>
                                                <div style="font-size:11px;color:#888;margin-top:2px">
                                                    @foreach ($variant->attributeValues as $av)
                                                        <span>{{ $av->attribute->name }}: {{ $av->value }}</span>@if (!$loop->last), @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td style="font-size:12px;color:#666">{{ $variant->sku }}</td>
                                            <td><input type="number" step="0.01" name="variants[{{ $variant->id }}][price_rmb]" value="{{ $variant->price_rmb ?? $product->price_rmb }}" class="pf-input" style="width:80px"></td>
                                            <td><input type="number" name="variants[{{ $variant->id }}][weight_gram]" value="{{ $variant->weight_gram ?? $product->weight_gram }}" class="pf-input" style="width:70px"></td>
                                            <td>
                                                <select name="variants[{{ $variant->id }}][availability]" class="pf-select" style="width:90px;font-size:12px">
                                                    <option value="tersedia" @selected($variant->availability === 'tersedia')>Tersedia</option>
                                                    <option value="kosong" @selected($variant->availability === 'kosong')>Kosong</option>
                                                </select>
                                            </td>
                                            <td><button type="button" class="pf-btn" style="height:28px;padding:0 10px;font-size:11px">Edit</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div style="padding:40px;text-align:center;border:1px dashed #e1e1e1">
                                <p style="margin:0;color:#888">Belum ada varian.</p>
                                <p style="margin:8px 0 0;font-size:12px;color:#aaa">Tambah atribut di tab Atribut, lalu klik "Generate Varian".</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pf-section">
                    <p class="pf-label">Deskripsi Produk (Gambar Panjang)</p>
                    <input type="file" name="description_image" accept=".jpg,.jpeg,.png,.webp" class="pf-input" style="height:auto;padding:8px;margin-top:8px">
                    @if ($product->description_image_path)
                        <div style="margin-top:12px">
                            <img src="{{ asset('storage/'.$product->description_image_path) }}" style="max-width:200px;border:1px solid #e1e1e1">
                            <label style="display:block;margin-top:8px;font-size:12px">
                                <input type="checkbox" name="remove_description_image" value="1"> Hapus gambar
                            </label>
                        </div>
                    @endif
                </div>
            </div>

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
                    <p class="pf-side-label" style="margin-top:16px">Kategori</p>
                    <select name="merch_category_id" class="pf-select">
                        <option value="">— auto —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
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
                            <button type="button" class="pf-btn" style="width:100%">Buat Split Box</button>
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
                        <button type="button" onclick="if(confirm('Hapus produk ini?')) document.getElementById('form-delete').submit()" class="pf-btn is-danger" style="width:100%;margin-top:8px">Hapus Produk</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>

@if ($product->exists)
    <form id="form-delete" method="POST" action="{{ route('office.products.destroy', $product) }}" style="display:none">
        @csrf @method('DELETE')
    </form>
    <div style="width:min(1300px, calc(100vw - 48px));margin:0 auto 24px">
        <livewire:split-box-manager :product="$product" />
    </div>
@endif

<script>
(function () {
    var isSplitBox = {{ $isSplitBox ? 'true' : 'false' }};
    var productExists = {{ $product->exists ? 'true' : 'false' }};
    var generateUrl = '{{ $product->exists ? route('office.products.generate-variations', $product) : '' }}';
    var editUrlTemplate = '{{ route('office.products.edit', ['product' => '__ID__']) }}';

    window.setProductType = function (type) {
        document.querySelectorAll('.type-option').forEach(function (o) {
            o.classList.toggle('is-on', o.getAttribute('data-type') === type);
        });
        document.getElementById('input-type').value = type;

        var isVariable = type === 'variable';
        var tabAtribut = document.getElementById('tab-atribut');
        var tabVarian = document.getElementById('tab-varian');
        if (tabAtribut) tabAtribut.classList.toggle('is-disabled', !isVariable);
        if (tabVarian) tabVarian.classList.toggle('is-disabled', !isVariable);

        if (!isVariable) {
            var generalTab = document.querySelector('[data-prod-tab="general"]');
            if (generalTab) generalTab.click();
        }
    };

    document.querySelectorAll('[data-prod-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.classList.contains('is-disabled')) return;
            document.querySelectorAll('[data-prod-tab]').forEach(function (t) { t.classList.remove('is-on'); });
            document.querySelectorAll('[data-prod-panel]').forEach(function (p) { p.style.display = 'none'; });
            tab.classList.add('is-on');
            var panel = document.querySelector('[data-prod-panel="' + tab.getAttribute('data-prod-tab') + '"]');
            if (panel) panel.style.display = '';
        });
    });

    setProductType('{{ $currentType }}');

    var attrIndex = {{ $product->exists ? $product->attributes->count() : 0 }};

    window.addAttributeRow = function () {
        var list = document.getElementById('attributes-list');
        if (!list) return;
        var html = '<div class="pf-attr-box">'
            + '<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap">'
            + '<input type="text" name="attributes[' + attrIndex + '][name]" class="pf-input" style="flex:1;min-width:150px;max-width:200px" placeholder="Nama atribut">'
            + '<label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px"><input type="checkbox" name="attributes[' + attrIndex + '][visible]" value="1" checked> Tampilkan</label>'
            + '<label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px"><input type="checkbox" name="attributes[' + attrIndex + '][variation]" value="1" checked> Untuk varian</label>'
            + '<button type="button" onclick="this.closest(\'.pf-attr-box\').remove()" class="pf-btn is-danger" style="margin-left:auto">Hapus</button>'
            + '</div>'
            + '<div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">'
            + '<button type="button" onclick="addValueRow(this, ' + attrIndex + ')" class="pf-chip">+ Tambah</button>'
            + '</div></div>';
        list.insertAdjacentHTML('beforeend', html);
        attrIndex++;
    };

    window.addValueRow = function (btn, attrIdx) {
        var container = btn.parentElement;
        var valIndex = container.querySelectorAll('.pf-chip').length;
        var modifierHtml = isSplitBox
            ? '<select name="attributes[' + attrIdx + '][values][' + valIndex + '][modifier]" style="border:0;background:transparent;font-size:11px;width:46px"><option value="0">—</option><option value="1">+1</option><option value="2">+2</option></select>'
            : '<input type="hidden" name="attributes[' + attrIdx + '][values][' + valIndex + '][modifier]" value="0">';
        var html = '<div class="pf-chip" style="padding-right:4px">'
            + '<input type="text" name="attributes[' + attrIdx + '][values][' + valIndex + '][value]" style="width:80px;border:0;background:transparent;font-size:12px" placeholder="Nilai">'
            + modifierHtml
            + '<button type="button" onclick="this.closest(\'.pf-chip\').remove()" style="border:0;background:none;color:#999;cursor:pointer;padding:0 4px">&times;</button>'
            + '</div>';
        btn.insertAdjacentHTML('beforebegin', html);
    };

    window.generateVariations = function (btn) {
    btn.disabled = true;
    var originalText = btn.textContent;
    btn.textContent = 'Generating...';

    var form = document.getElementById('product-form');
    var formData = new FormData(form);

    // Debug
    console.log('Sending:');
    for (var p of formData.entries()) console.log(p[0], p[1]);

    if (productExists) {
        // Existing product: hit generate endpoint
        fetch(generateUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal');
            }
        })
        .catch(function (err) { alert('Error: ' + err.message); })
        .finally(function () { btn.disabled = false; btn.textContent = originalText; });
    } else {
        // New product: save first
        formData.set('is_active', '0');
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.product_id) {
                location.href = editUrlTemplate.replace('__ID__', data.product_id) + '?generate=1';
            } else {
                alert(JSON.stringify(data.errors || data.message || 'Gagal'));
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(function (err) {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }
};

    function doGenerate(btn, originalText, url) {
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                alert(data.message);
                window.location.href = window.location.pathname;
            } else {
                alert(data.message || 'Gagal');
            }
        })
        .catch(function (err) {
            alert('Gagal: ' + err.message);
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }

    @if (request('generate') && $product->exists)
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.querySelector('button[onclick*="generateVariations"]');
        if (btn) doGenerate(btn, 'Generate Varian', generateUrl);
    });
    @endif

    window.applyBulkAction = function () {
        var action = document.getElementById('bulk-action').value;
        var ids = Array.from(document.querySelectorAll('.variant-check:checked')).map(function (c) { return c.value; });
        if (!action || !ids.length) { alert('Pilih aksi dan varian'); return; }
        alert('Bulk ' + action + ' untuk ' + ids.length + ' varian');
    };

    var checkAll = document.getElementById('check-all-variants');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.variant-check').forEach(function (c) { c.checked = checkAll.checked; });
        });
    }
})();
</script>
@endsection
