@extends('admin.layouts.admin')

@section('title',admin.layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
@php
    $cfg = [
        'rate_rmb'       => (float) setting('rate_rmb_idr', 3000),
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
    #pform-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
    }
    #pform-layout .pform-main {
        border-right: 1px solid #e5e7eb;
        min-width: 0;
    }
    #pform-layout .pform-sidebar {
        min-width: 0;
    }
    @media (max-width: 1023px) {
        #pform-layout { grid-template-columns: 1fr; }
        #pform-layout .pform-main { border-right: none; border-bottom: 1px solid #e5e7eb; }
    }

    .upload-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #e5e7eb;
        border-radius: 0.75rem;
        background: rgba(248,249,250,0.4);
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .upload-zone:hover { border-color: rgba(0,0,0,0.25); background: rgba(248,249,250,0.8); }
    .upload-zone.drag-over { border-color: rgba(0,0,0,0.4); background: rgba(248,249,250,1); }

    .desc-preview-card {
        display: flex;
        gap: 1.25rem;
        align-items: flex-start;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: rgba(248,249,250,0.3);
        padding: 1.25rem;
    }
    .desc-preview-card img {
        width: 12rem;
        height: 12rem;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        flex-shrink: 0;
    }

    .sidebar-photo-zone {
        border: 2px dashed #e5e7eb;
        border-radius: 0.75rem;
        background: rgba(248,249,250,0.3);
        overflow: hidden;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .sidebar-photo-zone:hover { border-color: rgba(0,0,0,0.25); }
    .sidebar-photo-zone.drag-over { border-color: rgba(0,0,0,0.4); }
    .sidebar-photo-zone img { width: 100%; height: 8rem; object-fit: cover; }

    .variant-row { border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1rem; background: rgba(248,249,250,0.25); }
    .variant-row .row-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
    .variant-row .row-head .idx { font-family: monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.14em; color: rgba(0,0,0,0.45); }
    .variant-row .remove-btn { color: #E53935; cursor: pointer; background: none; border: none; font-size: 1.1rem; line-height: 1; padding: 0.25rem; }
    .variant-row .remove-btn:hover { color: #C62828; }
</style>

{{-- ═══ FLASH MESSAGES ═══ --}}
@if (session('status'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

{{-- ═══ GENERAL VALIDATION ERRORS ═══ --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Periksa kembali input berikut:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ═══ FORM UTAMA ═══ --}}
<form method="POST"
      action="{{ $product->exists ? route('office.products.update', $product) : route('office.products.store') }}"
      enctype="multipart/form-data"
      id="product-form">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <input type="hidden" name="is_active" id="input-is-active"
           value="{{ old('is_active', $product->exists ? ($product->is_active ? 1 : 0) : 1) }}">
    <input type="hidden" name="remove_description_image" id="remove-desc-flag" value="0">
    <input type="hidden" name="is_split_box" id="input-is-split-box"
           value="{{ old('is_split_box', $product->exists ? ($product->is_split_box ? 1 : 0) : 0) }}">

    <div class="overflow-hidden rounded-xl border border-rule bg-white">

        {{-- FORM HEADER --}}
        <div class="flex items-center justify-between border-b border-rule px-6 py-3">
            <h1 class="font-display text-base font-semibold text-ink">
                {{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}
            </h1>
            <div class="flex items-center gap-2">
                @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                    <a href="{{ route('office.products.preview', $product) }}" target="_blank"
                       class="rounded-lg border border-rule px-4 py-2 text-sm font-medium text-ink transition-colors hover:border-ink">
                        Preview
                    </a>
                @endif
                <button type="submit"
                        onclick="document.getElementById('input-is-active').value='1'"
                        class="rounded-lg bg-black px-5 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    Simpan Produk
                </button>
            </div>
        </div>

        {{-- 2 KOLOM --}}
        <div id="pform-layout">

            {{-- ═══════════ KONTEN UTAMA (kiri) ═══════════ --}}
            <div class="pform-main">

                {{-- Nama + Slug --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-ink">Nama Produk: <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="input-name" value="{{ old('name', $product->name) }}" required
                                   class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink"
                                   placeholder="Millennium T-Shirt Vol.2">
                            @error('name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink">
                                Slug: <span class="ml-1 text-xs font-normal text-ink/45">otomatis dari nama</span>
                            </label>
                            <input type="text" name="slug" id="input-slug" value="{{ old('slug', $product->slug) }}"
                                   class="mt-1.5 w-full rounded-lg border border-rule bg-paper/30 px-3 py-2.5 text-sm text-ink"
                                   placeholder="millennium-tshirt-vol-2">
                            @error('slug')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- TABS --}}
                <div class="border-t border-rule">
                    <div class="flex border-b border-rule px-6" role="tablist">
                        <button type="button" data-prod-tab="kalkulasi"
                                class="prod-tab border-b-2 px-4 py-3 text-sm border-ink font-semibold text-ink">Kalkulasi Harga</button>
                        <button type="button" data-prod-tab="atribut"
                                class="prod-tab border-b-2 px-4 py-3 text-sm border-transparent text-ink/55 hover:text-ink">Atribut</button>
                        <button type="button" data-prod-tab="varian"
                                class="prod-tab border-b-2 px-4 py-3 text-sm border-transparent text-ink/55 hover:text-ink">Varian</button>
                    </div>

                    {{-- ──── PANEL: Kalkulasi Harga ──── --}}
                    <div class="prod-panel p-6" data-prod-panel="kalkulasi">
                        <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Kalkulasi Harga</p>
                        <div class="mt-4 h-px bg-rule"></div>

                        {{-- Row 1: SKU + Harga RMB + Berat --}}
                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-ink">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                @error('sku')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Harga RMB <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="price_rmb" id="input-price-rmb"
                                       value="{{ old('price_rmb', $product->price_rmb) }}" required
                                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                @error('price_rmb')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Berat (gram) <span class="text-danger">*</span></label>
                                <input type="number" name="weight_gram" id="input-weight-gram"
                                       value="{{ old('weight_gram', $product->weight_gram) }}"
                                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                @error('weight_gram')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-ink/55">
                            <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                            Berat dipakai untuk hitung ongkir internasional per kg. Varian bisa override.
                        </p>

                        <div class="mt-5 h-px bg-rule"></div>

                        {{-- Row 2: 3 Ongkir --}}
                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-ink">Ongkir Lokal CN (RMB)</label>
                                <div class="mt-1.5 rounded-lg border border-rule bg-paper/40 px-3 py-2.5 text-sm text-ink/60" id="disp-ongkir-lokal">Otomatis dari store</div>
                                <p class="mt-1 text-[10px] text-ink/45">
                                    <span id="info-ongkir-lokal">Store: —</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Ongkir CN-BTM (IDR)</label>
                                <div class="mt-1.5 rounded-lg border border-rule bg-paper/40 px-3 py-2.5 text-sm text-ink/60" id="disp-ongkir-cnbtm">Otomatis dari berat</div>
                                <p class="mt-1 text-[10px] text-ink/45">
                                    <span id="info-ongkir-cnbtm">—</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Ongkir BTM-JKT (IDR)</label>
                                <div class="mt-1.5 rounded-lg border border-rule bg-paper/40 px-3 py-2.5 text-sm text-ink/60" id="disp-ongkir-btmjkt">Otomatis dari berat</div>
                                <p class="mt-1 text-[10px] text-ink/45">
                                    <span id="info-ongkir-btmjkt">—</span>
                                </p>
                            </div>
                        </div>
                        <p class="mt-2 flex items-start gap-1.5 text-xs text-ink/55">
                            <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                            Semua ongkir dihitung otomatis (read-only). Rute: China &rarr; Batam &rarr; Jakarta.
                        </p>

                        {{-- PREVIEW PERHITUNGAN --}}
                        <div class="mt-6 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-5">
                            <h3 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Preview Perhitungan Harga</h3>
                            <div class="mt-3 font-mono text-xs leading-relaxed text-ink/70">
                                <p>Input:</p>
                                <p class="ml-4">&bull; Harga RMB:&nbsp;&nbsp;&nbsp; <span id="pv-rmb">—</span></p>
                                <p class="ml-4">&bull; Berat:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span id="pv-berat">—</span></p>
                                <p class="ml-4">&bull; Store:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span id="pv-store">—</span></p>
                                <p class="ml-4">&bull; Rate:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span id="pv-rate">—</span></p>
                            </div>
                            <div class="mt-3 h-px bg-rule"></div>
                            <div class="mt-3 rounded-lg border border-rule bg-white p-4">
                                <p class="mb-2 font-mono text-xs font-medium text-ink">Perhitungan:</p>
                                <table class="w-full font-mono text-xs">
                                    <tbody>
                                        <tr>
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap">Harga Produk:</td>
                                            <td class="py-1 pr-4 text-ink/70" id="calc-harga-formula">—</td>
                                            <td class="py-1 text-right text-ink" id="calc-harga">—</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap">Ongkir Lokal CN:</td>
                                            <td class="py-1 pr-4 text-ink/70" id="calc-lokal-formula">—</td>
                                            <td class="py-1 text-right text-ink" id="calc-lokal">—</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap">Ongkir CN-BTM:</td>
                                            <td class="py-1 pr-4 text-ink/70" id="calc-cnbtm-formula">—</td>
                                            <td class="py-1 text-right text-ink" id="calc-cnbtm">—</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap">Ongkir BTM-JKT:</td>
                                            <td class="py-1 pr-4 text-ink/70" id="calc-btmjkt-formula">—</td>
                                            <td class="py-1 text-right text-ink" id="calc-btmjkt">—</td>
                                        </tr>
                                        <tr id="calc-diskon-row" class="hidden">
                                            <td class="py-1 pr-4 text-green-700 whitespace-nowrap">Diskon:</td>
                                            <td class="py-1 pr-4 text-green-700" id="calc-diskon-formula">—</td>
                                            <td class="py-1 text-right text-green-600" id="calc-diskon">—</td>
                                        </tr>
                                        <tr class="border-t border-ink/20">
                                            <td class="py-1.5 pr-4 font-semibold text-ink whitespace-nowrap" colspan="2">Subtotal:</td>
                                            <td class="py-1.5 text-right font-semibold text-ink" id="calc-subtotal">—</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap" colspan="2" id="calc-profit-label">Profit:</td>
                                            <td class="py-1 text-right text-ink" id="calc-profit">—</td>
                                        </tr>
                                        <tr class="border-t border-ink/20">
                                            <td class="py-1 pr-4 text-ink/70 whitespace-nowrap" colspan="2">Total (sebelum bulat):</td>
                                            <td class="py-1 text-right text-ink" id="calc-total">—</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1.5 pr-4 font-semibold text-ink whitespace-nowrap" colspan="2">Total (setelah bulat):</td>
                                            <td class="py-1.5 text-right font-mono text-sm font-bold text-[#1A4D8F]" id="calc-rounded">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-3 flex items-start gap-1.5 text-xs text-ink/55">
                                <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                                Preview ini cuma untuk admin. Customer cuma lihat "Rp <span id="calc-customer" class="font-mono">—</span>".
                            </p>
                        </div>
                    </div>

                    {{-- ──── PANEL: Atribut ──── --}}
                    <div class="prod-panel hidden p-6" data-prod-panel="atribut">
                        <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Atribut Produk</p>
                        <div class="mt-4 h-px bg-rule"></div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-ink">Tipe <span class="text-danger">*</span></label>
                                <select name="type" required class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    <option value="single" @selected(old('type', $product->type ?: 'single') === 'single')>Single</option>
                                    <option value="variant" @selected(old('type') === 'variant' || $product->type === 'variant')>Varian</option>
                                </select>
                                @error('type')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Game <span class="text-danger">*</span></label>
                                <select name="game_id" id="input-game-id" required class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    @foreach ($games as $game)
                                        <option value="{{ $game->id }}" @selected(old('game_id', $product->game_id) == $game->id)>{{ $game->name }}</option>
                                    @endforeach
                                </select>
                                @error('game_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Developer <span class="text-ink/45">(opsional)</span></label>
                                <select name="developer_id" id="input-developer-id" class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    <option value="">— tanpa developer —</option>
                                    @foreach ($developerList as $dev)
                                        <option value="{{ $dev->id }}" @selected(old('developer_id', $product->developer_id) == $dev->id)>{{ $dev->name }}</option>
                                    @endforeach
                                </select>
                                @error('developer_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink">Kategori Merch</label>
                                <select name="merch_category_id" class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                                    <option value="">— auto —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('merch_category_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 rounded-lg border-l-4 border-l-[#1E88E5] bg-[#F8F9FA] p-4">
                            <p class="flex items-start gap-2 text-xs text-ink/70">
                                <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.85rem;"></i>
                                Ongkir dihitung otomatis dari berat. Rute: China &rarr; Batam &rarr; Jakarta.
                            </p>
                        </div>
                    </div>

                    {{-- ──── PANEL: Varian ──── --}}
                    <div class="prod-panel hidden p-6" data-prod-panel="varian">
                        <div class="flex items-center justify-between">
                            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Varian Produk</p>
                            @if ($product->exists)
                                <button type="button" onclick="openAddVariantModal()"
                                        class="flex items-center gap-1.5 rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink">
                                    <i class="bi bi-plus-lg" style="font-size:0.8rem;"></i>
                                    Tambah Varian
                                </button>
                            @endif
                        </div>
                        <div class="mt-4 h-px bg-rule"></div>

                        @if ($product->exists)
                            @if ($product->variants->count())
                                <div class="mt-4 flex items-center gap-3">
                                    <label class="flex items-center gap-2 text-sm text-ink/70">
                                        <input type="checkbox" id="check-all-variants" class="h-4 w-4 rounded border-rule">
                                        Pilih semua
                                    </label>
                                    <button type="button" onclick="openBulkWeight()"
                                            class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                                        <i class="bi bi-pencil-square me-1" style="font-size:0.8rem;"></i> Bulk Edit Berat
                                    </button>
                                    <span class="text-xs text-ink/50" id="variant-selected-count">0 varian terpilih</span>
                                </div>

                                <div class="mt-3 overflow-x-auto rounded-lg border border-rule">
                                    <table class="w-full text-sm">
                                        <thead class="border-b border-rule bg-[#F8F9FA]">
                                            <tr>
                                                <th class="w-10 px-3 py-2.5"></th>
                                                <th class="px-3 py-2.5 text-left font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">SKU</th>
                                                <th class="px-3 py-2.5 text-left font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">Nama</th>
                                                <th class="px-3 py-2.5 text-left font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">Harga</th>
                                                <th class="px-3 py-2.5 text-left font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">Berat</th>
                                                <th class="px-3 py-2.5 text-left font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">Status</th>
                                                <th class="px-3 py-2.5 text-center font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($product->variants as $variant)
                                                <tr class="border-b border-rule/50 transition-colors hover:bg-paper/30" id="variant-row-{{ $variant->id }}">
                                                    <td class="px-3 py-2.5">
                                                        <input type="checkbox" class="variant-check h-4 w-4 rounded border-rule"
                                                               value="{{ $variant->id }}"
                                                               data-name="{{ $variant->name }}"
                                                               data-sku="{{ $variant->sku }}"
                                                               data-price="{{ $variant->price_rmb }}"
                                                               data-weight="{{ $variant->weight_gram }}"
                                                               data-availability="{{ $variant->availability }}"
                                                               data-image="{{ $variant->image_path ? asset('storage/' . $variant->image_path) : '' }}">
                                                    </td>
                                                    <td class="px-3 py-2.5 font-mono text-xs text-ink/70">{{ $variant->sku }}</td>
                                                    <td class="px-3 py-2.5 text-ink">{{ $variant->name }}</td>
                                                    <td class="px-3 py-2.5 font-mono text-ink">{{ $variant->price_rmb ? '¥'.number_format($variant->price_rmb, 2) : '—' }}</td>
                                                    <td class="px-3 py-2.5 font-mono text-ink">{{ $variant->weight_gram ? $variant->weight_gram.'g' : '—' }}</td>
                                                    <td class="px-3 py-2.5">
                                                        @if ($variant->availability === 'tersedia')
                                                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700">Tersedia</span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">Kosong</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2.5 text-center">
                                                        <button type="button" onclick="editVariant({{ $variant->id }})"
                                                                class="rounded-lg border border-rule px-2.5 py-1 text-xs text-ink/70 hover:border-ink hover:text-ink">Edit</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-2 text-xs text-ink/50">Menampilkan {{ $product->variants->count() }} varian</p>
                            @else
                                <div class="mt-6 flex flex-col items-center gap-3 py-8">
                                    <i class="bi bi-box-seam text-ink/20" style="font-size:2.5rem;"></i>
                                    <p class="text-sm text-ink/50">Belum ada varian.</p>
                                    <button type="button" onclick="openAddVariantModal()"
                                            class="rounded-lg border border-ink px-5 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-ink hover:text-white">
                                        <i class="bi bi-plus-lg me-1.5" style="font-size:0.8rem;"></i>
                                        Tambah Varian Pertama
                                    </button>
                                </div>
                            @endif

                        @else
                            <p class="mt-4 text-xs text-ink/55">
                                Tambahkan varian sekarang — produk + varian langsung tersimpan bersamaan.
                            </p>

                            <div id="variant-builder" class="mt-4 space-y-3">
                                <div class="variant-row" data-vindex="0">
                                    <div class="row-head">
                                        <span class="idx">Varian #1</span>
                                        <button type="button" class="remove-btn" onclick="removeVariantRow(this)" title="Hapus baris">&times;</button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">Nama Varian</label>
                                            <input type="text" name="variants[0][name]" placeholder="cth: Size L"
                                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">SKU</label>
                                            <input type="text" name="variants[0][sku]" placeholder="SKU-001"
                                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">Harga RMB <span class="text-ink/40">(opsional)</span></label>
                                            <input type="number" step="0.01" name="variants[0][price_rmb]" placeholder="ikut produk"
                                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">Berat (gram) <span class="text-ink/40">(opsional)</span></label>
                                            <input type="number" name="variants[0][weight_gram]" placeholder="ikut produk"
                                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">Ketersediaan</label>
                                            <select name="variants[0][availability]" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                                <option value="tersedia">Tersedia</option>
                                                <option value="kosong">Kosong</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink/70">Foto <span class="text-ink/40">(opsional)</span></label>
                                            <input type="file" name="variants[0][image]" accept=".jpg,.jpeg,.png,.webp"
                                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-1.5 text-xs">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" onclick="addVariantRow()"
                                    class="mt-3 flex items-center gap-1.5 rounded-lg border border-ink px-4 py-2 text-xs font-semibold text-ink transition-colors hover:bg-ink hover:text-white">
                                <i class="bi bi-plus-lg" style="font-size:0.75rem;"></i>
                                Tambah Varian Lagi
                            </button>
                        @endif
                    </div>
                    {{-- AKHIR PANEL VARIAN --}}
                </div>

                {{-- DESKRIPSI GAMBAR PANJANG --}}
                <div class="border-t border-rule p-6">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                        Deskripsi Produk (Gambar Panjang)
                        <span class="ml-1 font-normal text-ink/45">· Maks 6 MB · JPG/PNG/WebP</span>
                    </p>

                    <input type="file" id="desc-image-input" name="description_image"
                           accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewDescImage(this)">

                    <div id="desc-upload-area"
                         class="upload-zone mt-4 px-8 py-14 {{ $product->description_image_path ? 'hidden' : '' }}"
                         onclick="document.getElementById('desc-image-input').click()"
                         ondragover="event.preventDefault(); this.classList.add('drag-over');"
                         ondragleave="this.classList.remove('drag-over');"
                         ondrop="event.preventDefault(); this.classList.remove('drag-over'); if(event.dataTransfer.files.length){ document.getElementById('desc-image-input').files = event.dataTransfer.files; previewDescImage(document.getElementById('desc-image-input')); }">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm">
                            <i class="bi bi-cloud-arrow-up text-ink/40" style="font-size:1.8rem;"></i>
                        </div>
                        <p class="mt-4 text-sm font-medium text-ink">Klik atau drag &amp; drop gambar ke sini</p>
                        <p class="mt-1 text-xs text-ink/50">JPG, PNG, atau WebP — Maks 6 MB</p>
                    </div>

                    <div id="desc-preview-area"
                         class="mt-4 {{ $product->description_image_path ? '' : 'hidden' }}">
                        <div class="desc-preview-card">
                            <img id="desc-preview-img"
                                 src="{{ $product->description_image_path ? asset('storage/' . $product->description_image_path) : '' }}"
                                 alt="Deskripsi">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink truncate" id="desc-preview-name">
                                    {{ $product->description_image_path ? basename($product->description_image_path) : '—' }}
                                </p>
                                <p class="mt-0.5 text-xs text-ink/50" id="desc-preview-size">
                                    {{ $product->description_image_path ? 'Gambar saat ini' : '—' }}
                                </p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($product->description_image_path)
                                        <button type="button" id="desc-btn-full"
                                                onclick="window.open('{{ asset('storage/' . $product->description_image_path) }}', '_blank')"
                                                class="flex items-center gap-1.5 rounded-lg border border-rule bg-white px-3.5 py-2 text-xs font-medium text-ink transition-colors hover:border-ink">
                                            <i class="bi bi-magnifying-glass" style="font-size:0.85rem;"></i>
                                            Lihat Full
                                        </button>
                                    @endif
                                    <button type="button" onclick="document.getElementById('desc-image-input').click()"
                                            class="flex items-center gap-1.5 rounded-lg border border-rule bg-white px-3.5 py-2 text-xs font-medium text-ink transition-colors hover:border-ink">
                                        <i class="bi bi-arrow-repeat" style="font-size:0.85rem;"></i>
                                        Ganti
                                    </button>
                                    <button type="button" onclick="removeDescImage()"
                                            class="flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3.5 py-2 text-xs font-medium text-red-600 transition-colors hover:border-red-400 hover:bg-red-50">
                                        <i class="bi bi-x-lg" style="font-size:0.85rem;"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        Gambar lama otomatis dihapus saat di-override.
                    </p>
                    @error('description_image')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

            </div>
            {{-- AKHIR KONTEN UTAMA --}}

            {{-- ═══════════ SIDEBAR (kanan) ═══════════ --}}
            <div class="pform-sidebar">

                {{-- TERBITKAN --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Terbitkan</p>
                    <p class="mt-2 text-sm text-ink/70">
                        Status:
                        <strong class="{{ ($product->exists && $product->is_active) ? 'text-green-600' : 'text-amber-600' }}">
                            {{ ($product->exists && $product->is_active) ? 'Published' : 'Draft' }}
                        </strong>
                    </p>
                    <button type="submit"
                            onclick="document.getElementById('input-is-active').value='0'"
                            class="mt-3 w-full rounded-lg border border-rule px-3 py-2.5 text-sm font-medium text-ink transition-colors hover:border-ink hover:bg-paper">
                        <i class="bi bi-file-earmark me-1.5" style="font-size:0.9rem;"></i>
                        Simpan sebagai Draft
                    </button>
                    @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                        <a href="{{ route('office.products.preview', $product) }}" target="_blank"
                           class="mt-2 block w-full rounded-lg border border-rule px-3 py-2 text-center text-xs font-medium text-ink/70 transition-colors hover:border-ink hover:text-ink">
                            <i class="bi bi-eye me-1" style="font-size:0.85rem;"></i> Preview
                        </a>
                    @endif
                </div>

                {{-- KETERSEDIAAN --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ketersediaan</p>
                    <select name="availability" class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        <option value="tersedia" @selected(old('availability', $product->availability ?: 'tersedia') === 'tersedia')>Tersedia</option>
                        <option value="kosong" @selected(old('availability') === 'kosong' || $product->availability === 'kosong')>Kosong</option>
                    </select>
                    @error('availability')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- STORE --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Store Override <span class="text-ink/45">(opsional)</span></p>
                    <div class="mt-2">
                        <select name="store_id" id="sidebar-store-id" class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                            <option value="">— gunakan default game —</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('store_id', $product->store_id) == $store->id)
                                        data-min="{{ $store->min_belanja_rmb ?? 0 }}"
                                        data-ongkir="{{ $store->ongkir_rmb ?? 0 }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-2 flex items-start gap-1.5 text-xs text-ink/55">
                        <i class="bi bi-info-circle mt-0.5" style="font-size:0.75rem;"></i>
                        <span id="store-info">Store digunakan untuk hitung ongkir lokal CN.</span>
                    </p>
                </div>

                {{-- ORDER COUNT --}}
                @if ($product->exists)
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Order Count</p>
                    <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $orderCount }}x</p>
                </div>
                @endif

                {{-- TAG PRODUK --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Tag Produk</p>
                    <div class="mt-2 space-y-1.5">
                        @foreach ($tags as $tag)
                            <label class="flex items-center gap-2 text-sm text-ink/80">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                       @checked(in_array($tag->id, $productTags))
                                       class="h-4 w-4 rounded border-rule">
                                {{ strtoupper($tag->name) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- GAME --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Game</p>
                    <div class="mt-2 space-y-1.5">
                        @foreach ($games as $game)
                            <label class="flex items-center gap-2 text-sm text-ink/80">
                                <input type="radio" name="game_id_sidebar" value="{{ $game->id }}"
                                       @checked(old('game_id', $product->game_id) == $game->id)
                                       onchange="document.getElementById('input-game-id').value=this.value"
                                       class="h-4 w-4 border-rule">
                                {{ $game->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- FOTO PRODUK --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Foto Produk</p>

                    <input type="file" id="photo-input" name="image"
                           accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewPhoto(this)">

                    <div id="photo-upload-zone"
                         class="sidebar-photo-zone mt-2"
                         onclick="document.getElementById('photo-input').click()"
                         ondragover="event.preventDefault(); this.classList.add('drag-over');"
                         ondragleave="this.classList.remove('drag-over');"
                         ondrop="event.preventDefault(); this.classList.remove('drag-over'); if(event.dataTransfer.files.length){ document.getElementById('photo-input').files = event.dataTransfer.files; previewPhoto(document.getElementById('photo-input')); }">
                        <div id="photo-preview" class="flex h-28 w-full items-center justify-center overflow-hidden bg-paper/30">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" class="h-full w-full object-cover" alt="Foto">
                            @else
                                <div class="flex flex-col items-center gap-1.5">
                                    <i class="bi bi-image text-ink/30" style="font-size:1.6rem;"></i>
                                    <span class="text-[10px] text-ink/40">Klik atau drag</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-1.5 text-center text-[10px] text-ink/45">
                        {{ $product->image_path ? 'Klik / drag untuk ganti foto' : 'JPG, PNG, WebP — Maks 6 MB' }}
                    </p>
                    @error('image')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- DISKON --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Diskon Produk</p>
                    <div class="mt-2">
                        <label class="block text-xs text-ink/70">Nominal (Rp)</label>
                        <input type="number" step="0.01" min="0" name="discount_amount"
                               value="{{ old('discount_amount', $product->discount_amount) }}"
                               class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        @error('discount_amount')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-ink/70">Mulai</label>
                            <input type="date" name="discount_start"
                                   value="{{ old('discount_start', $product->discount_start?->toDateString()) }}"
                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-xs text-ink">
                        </div>
                        <div>
                            <label class="block text-xs text-ink/70">Berakhir</label>
                            <input type="date" name="discount_end"
                                   value="{{ old('discount_end', $product->discount_end?->toDateString()) }}"
                                   class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-xs text-ink">
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-ink/45">Diskon wajib &le; harga jual dalam Rupiah.</p>
                </div>

                {{-- SPLIT BOX --}}
                <div class="border-b border-rule p-5">
                    <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Split Box</p>

                    @if ($product->exists && ! $product->is_split_box)
                        {{-- Produk asli: tombol buat salinan split --}}
                        <p class="mt-2 text-xs text-ink/55">
                            Buat salinan produk ini untuk jual per slot. Produk asli tetap jual utuh.
                        </p>
                        <button type="button" onclick="openSplitCloneModal()"
                                class="mt-3 w-full rounded-lg border border-ink px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-ink hover:text-white">
                            <i class="bi bi-grid-3x3-gap me-1.5" style="font-size:0.85rem;"></i>
                            Buat Split Box
                        </button>

                    @elseif ($product->exists && $product->is_split_box)
                        {{-- Produk split: info + link ke parent --}}
                        <div class="mt-2 rounded-lg border border-rule bg-paper/30 p-3">
                            <p class="text-xs text-ink/70">
                                <i class="bi bi-diagram-3 me-1" style="font-size:0.8rem;"></i>
                                Salinan split dari
                                @if ($product->parent_id)
                                    <a href="{{ route('office.products.edit', $product->parent_id) }}"
                                       class="font-semibold text-link underline underline-offset-2">{{ $product->parent->name ?? 'produk asli' }}</a>
                                @else
                                    produk lain
                                @endif
                            </p>
                        </div>
                        <p class="mt-2 text-xs text-ink/55">
                            Buat varian slot manual di tab Varian. Contoh: "1a", "1b", "2a", "2b".
                        </p>
                        @if ($product->parent_id)
                            <a href="{{ route('office.products.edit', $product->parent_id) }}"
                               class="mt-2 block w-full rounded-lg border border-rule px-3 py-2 text-center text-xs font-medium text-ink/70 transition-colors hover:border-ink hover:text-ink">
                                <i class="bi bi-arrow-left me-1" style="font-size:0.8rem;"></i> Lihat Produk Asli
                            </a>
                        @endif

                    @else
                        {{-- Produk baru (create) --}}
                        <p class="mt-2 text-xs text-ink/55">
                            Simpan produk dulu, lalu buat salinan split box.
                        </p>
                    @endif
                </div>

                {{-- HAPUS PRODUK --}}
                @if ($product->exists)
                <div class="p-5">
                    <button type="button" onclick="openDeleteModal()"
                            class="flex w-full items-center gap-2 rounded-lg border border-danger/40 px-4 py-2.5 text-sm font-medium text-danger transition-colors hover:border-red-600 hover:bg-red-50">
                        <i class="bi bi-exclamation-triangle" style="font-size:0.95rem;"></i>
                        Hapus Produk
                    </button>
                    <p class="mt-1.5 text-center text-[10px] text-ink/45">(Hapus Permanen)</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</form>
{{-- AKHIR FORM UTAMA --}}

{{-- ═══ SPLIT BOX MANAGER ═══ --}}
<div class="mt-5">
    @if ($product->exists)
        <livewire:split-box-manager :product="$product" />
    @else
        <div class="rounded-xl border border-rule bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#F8F9FA]">
                    <i class="bi bi-grid-3x3-gap text-ink/40" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">Split Box — Slot &amp; Kloter</p>
                    <p class="mt-0.5 text-xs text-ink/55">Simpan produk dulu, lalu buat salinan split box.</p>
                </div>
            </div>
            <div class="mt-4 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
                <p class="flex items-start gap-2 text-xs text-ink/65">
                    <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.85rem;"></i>
                    Setelah produk tersimpan, halaman akan berubah ke mode Edit dan kamu bisa langsung kelola slot &amp; kloter di sini.
                </p>
            </div>
        </div>
    @endif
</div>


{{-- ═══ MODAL: Tambah Varian ═══ --}}
@if ($product->exists)
<div id="modal-add-variant" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-rule px-8 py-5">
            <h3 class="font-display text-lg font-semibold text-ink">Tambah Varian</h3>
            <button type="button" onclick="closeAddVariantModal()" class="text-ink/40 hover:text-ink">
                <i class="bi bi-x-lg" style="font-size:1.1rem;"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('office.products.variants.store', $product) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="space-y-4 px-8 py-5">
                <div>
                    <label class="block text-sm font-medium text-ink">Nama Varian <span class="text-danger">*</span></label>
                    <input type="text" name="name" required
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink"
                           placeholder="cth: Size L / Warna Merah">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink">SKU <span class="text-danger">*</span></label>
                        <input type="text" name="sku" required
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink">Harga RMB</label>
                        <input type="number" step="0.01" name="price_rmb"
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink">Berat (gram)</label>
                        <input type="number" name="weight_gram"
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink">Ketersediaan</label>
                        <select name="availability"
                                class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                            <option value="tersedia">Tersedia</option>
                            <option value="kosong">Kosong</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink">Foto Varian <span class="text-xs font-normal text-ink/50">(opsional)</span></label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
                <button type="button" onclick="closeAddVariantModal()"
                        class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                    <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i>
                    Simpan Varian
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══ MODAL: Hard Delete ═══ --}}
@if ($product->exists)
<div id="modal-delete" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                <i class="bi bi-exclamation-triangle text-[#E53935]" style="font-size:1.35rem;"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-semibold text-ink">Hapus Produk Permanen?</h3>
                <p class="mt-0.5 text-xs text-ink/55">HARD DELETE — Tidak bisa di-restore.</p>
            </div>
        </div>
        <div class="px-8 py-6">
            <p class="text-sm text-ink/80">Produk: <strong class="text-ink">{{ $product->name }}</strong></p>
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50/50 p-4">
                <p class="flex items-start gap-2 text-xs font-semibold text-[#E53935]">
                    <i class="bi bi-exclamation-triangle mt-0.5" style="font-size:0.9rem;"></i>
                    Produk ini pernah di-order {{ $orderCount }} kali.
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-ink/70">
                    <li class="flex items-start gap-2"><i class="bi bi-x text-[#E53935] mt-0.5"></i> Data produk &amp; foto asli dihapus PERMANEN dari DB.</li>
                    <li class="flex items-start gap-2"><i class="bi bi-x text-[#E53935] mt-0.5"></i> TIDAK BISA DI-RESTORE.</li>
                    <li class="flex items-start gap-2"><i class="bi bi-check text-green-600 mt-0.5"></i> Order lama TETAP UTUH 100% (baca dari snapshot).</li>
                </ul>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
            <button type="button" onclick="closeDeleteModal()"
                    class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">Batal</button>
            <form method="POST" action="{{ route('office.products.destroy', $product) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit"
                        class="rounded-lg bg-[#E53935] px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#C62828]">
                    <i class="bi bi-trash me-1.5" style="font-size:0.85rem;"></i> Hapus Permanen
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ═══ MODAL: Edit Varian ═══ --}}
@if ($product->exists)
<div id="modal-edit-variant" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-rule px-8 py-5">
            <h3 class="font-display text-lg font-semibold text-ink">Edit Varian</h3>
            <button type="button" onclick="closeEditVariant()" class="text-ink/40 hover:text-ink">
                <i class="bi bi-x-lg" style="font-size:1.1rem;"></i>
            </button>
        </div>
        <form id="form-edit-variant" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4 px-8 py-5">
                <div>
                    <label class="block text-sm font-medium text-ink">Nama Varian</label>
                    <input type="text" name="name" id="ev-name" required
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink">SKU</label>
                        <input type="text" name="sku" id="ev-sku" required
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink">Harga RMB</label>
                        <input type="number" step="0.01" name="price_rmb" id="ev-price"
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink">Berat (gram)</label>
                        <input type="number" name="weight_gram" id="ev-weight"
                               class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink">Ketersediaan</label>
                        <select name="availability" id="ev-availability"
                                class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                            <option value="tersedia">Tersedia</option>
                            <option value="kosong">Kosong</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink">Ganti Foto <span class="text-xs font-normal text-ink/50">(opsional)</span></label>
                    <div id="ev-image-preview" class="mt-2 hidden">
                        <img id="ev-image-thumb" src="" class="h-20 w-20 rounded-lg border border-rule object-cover">
                    </div>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-rule px-8 py-5">
                <button type="button" onclick="deleteVariant()"
                        class="flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition-colors hover:border-red-400 hover:bg-red-50">
                    <i class="bi bi-trash" style="font-size:0.85rem;"></i> Hapus Varian
                </button>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditVariant()"
                            class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">Batal</button>
                    <button type="submit"
                            class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">Simpan Varian</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══ MODAL: Bulk Edit Berat ═══ --}}
<div id="modal-bulk-weight" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-6">
    <div class="w-full max-w-[560px] rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="border-b border-rule px-8 py-5">
            <h3 class="font-display text-lg font-semibold text-ink">Bulk Edit Berat Varian</h3>
        </div>
        <div class="px-8 py-5">
            <p class="text-sm text-ink/70"><span id="bulk-count" class="font-semibold text-ink">0</span> varian terpilih:</p>
            <ul class="mt-2 max-h-32 space-y-1 overflow-y-auto text-xs text-ink/60" id="bulk-list"></ul>

            <div class="mt-4">
                <label class="block text-sm font-medium text-ink">Berat Baru (gram):</label>
                <input type="number" id="bulk-weight-input" placeholder="250"
                       class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink"
                       oninput="updateBulkPreview()">
            </div>

            <div class="mt-4 rounded-lg border border-rule bg-[#F8F9FA] p-4">
                <p class="text-xs font-medium text-ink">Preview:</p>
                <p class="mt-1 font-mono text-xs text-ink/70" id="bulk-preview-text">Masukkan berat untuk preview...</p>
            </div>

            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/50 p-3">
                <p class="flex items-start gap-2 text-xs text-ink/70">
                    <i class="bi bi-exclamation-triangle mt-0.5 text-amber-500" style="font-size:0.85rem;"></i>
                    Perubahan akan langsung tersimpan ke database.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
            <button type="button" onclick="closeBulkWeight()"
                    class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">Batal</button>
            <button type="button" onclick="applyBulkWeight()" id="btn-apply-bulk"
                    class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                <i class="bi bi-check-lg me-1.5" style="font-size:0.85rem;"></i>
                Apply ke <span id="bulk-apply-count">0</span> Varian
            </button>
        </div>
    </div>
</div>

{{-- MODAL: Bulk Success --}}
<div id="modal-bulk-success" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-md rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100">
                <i class="bi bi-check-circle text-green-600" style="font-size:1.3rem;"></i>
            </div>
            <h3 class="font-display text-lg font-semibold text-ink">Berhasil!</h3>
        </div>
        <div class="px-8 py-5">
            <p class="text-sm text-ink/80" id="bulk-success-msg">Varian telah diubah.</p>
        </div>
        <div class="flex items-center justify-end border-t border-rule px-8 py-4">
            <button type="button" onclick="closeBulkSuccess()"
                    class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">Tutup</button>
        </div>
    </div>
</div>

{{-- ═══ MODAL: Buat Split Box ═══ --}}
@if ($product->exists && ! $product->is_split_box)
<div id="modal-split-clone" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                <i class="bi bi-grid-3x3-gap text-blue-600" style="font-size:1.35rem;"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-semibold text-ink">Buat Split Box</h3>
                <p class="mt-0.5 text-xs text-ink/55">Salinan produk untuk jual per slot.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('office.products.split-clone', $product) }}">
            @csrf
            <div class="px-8 py-6">
                <div class="rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-4">
                    <ul class="space-y-1.5 text-xs text-ink/70">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-600 mt-0.5"></i>
                            <span>Nama, foto, deskripsi, game, dll di-copy</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-600 mt-0.5"></i>
                            <span>Flag <code>is_split_box</code> diaktifkan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-arrow-right text-blue-500 mt-0.5"></i>
                            <span><strong>Varian dipecah manual</strong> oleh admin (misal: 1 &rarr; 1a, 1b)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-info text-blue-500 mt-0.5"></i>
                            <span>Produk asli tetap ada &amp; tidak berubah</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-ink">Nama Produk Split <span class="text-danger">*</span></label>
                    <input type="text" name="split_name" required
                           value="{{ ($product->name ?? '') . ' (Split Box)' }}"
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-ink">Harga Per Slot (RMB) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="split_price_rmb" required
                           class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-rule px-8 py-5">
                <button type="button" onclick="closeSplitCloneModal()"
                        class="rounded-lg border border-rule bg-white px-6 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-paper">Batal</button>
                <button type="submit"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                    <i class="bi bi-plus-lg me-1.5" style="font-size:0.85rem;"></i>
                    Buat Produk Split
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══ VARIANT BUILDER JS ═══ --}}
<script>
(function () {
    var vIndex = 0;

    window.addVariantRow = function () {
        vIndex++;
        var builder = document.getElementById('variant-builder');
        if (!builder) return;

        var html = ''
            + '<div class="variant-row" data-vindex="' + vIndex + '">'
            +   '<div class="row-head">'
            +     '<span class="idx">Varian #' + (vIndex + 1) + '</span>'
            +     '<button type="button" class="remove-btn" onclick="removeVariantRow(this)" title="Hapus baris">&times;</button>'
            +   '</div>'
            +   '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">'
            +     '<div><label class="block text-xs font-medium text-ink/70">Nama Varian</label>'
            +       '<input type="text" name="variants[' + vIndex + '][name]" placeholder="cth: Size L" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></div>'
            +     '<div><label class="block text-xs font-medium text-ink/70">SKU</label>'
            +       '<input type="text" name="variants[' + vIndex + '][sku]" placeholder="SKU-001" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></div>'
            +     '<div><label class="block text-xs font-medium text-ink/70">Harga RMB <span class="text-ink/40">(opsional)</span></label>'
            +       '<input type="number" step="0.01" name="variants[' + vIndex + '][price_rmb]" placeholder="ikut produk" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></div>'
            +     '<div><label class="block text-xs font-medium text-ink/70">Berat (gram) <span class="text-ink/40">(opsional)</span></label>'
            +       '<input type="number" name="variants[' + vIndex + '][weight_gram]" placeholder="ikut produk" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></div>'
            +     '<div><label class="block text-xs font-medium text-ink/70">Ketersediaan</label>'
            +       '<select name="variants[' + vIndex + '][availability]" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">'
            +         '<option value="tersedia">Tersedia</option><option value="kosong">Kosong</option></select></div>'
            +     '<div><label class="block text-xs font-medium text-ink/70">Foto <span class="text-ink/40">(opsional)</span></label>'
            +       '<input type="file" name="variants[' + vIndex + '][image]" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full rounded-lg border border-rule px-3 py-1.5 text-xs"></div>'
            +   '</div>'
            + '</div>';

        builder.insertAdjacentHTML('beforeend', html);
    };

    window.removeVariantRow = function (btn) {
        var row = btn.closest('.variant-row');
        var builder = document.getElementById('variant-builder');
        if (!row || !builder) return;

        if (builder.querySelectorAll('.variant-row').length <= 1) {
            row.querySelectorAll('input, select').forEach(function (el) {
                if (el.type === 'file') { el.value = ''; }
                else if (el.tagName === 'SELECT') { el.selectedIndex = 0; }
                else { el.value = ''; }
            });
            return;
        }
        row.remove();
    };
})();
</script>

{{-- ═══ CONFIG UNTUK JS EKSTERNAL ═══ --}}
@php
    $jsConfig = [
        'cfg'            => $cfg,
        'productExists'  => $product->exists,
        'variantBaseUrl' => $product->exists ? url('office/products/' . $product->id . '/variants') : '',
        'csrfToken'      => csrf_token(),
    ];
@endphp
<script type="application/json" id="product-form-config">{!! json_encode($jsConfig) !!}</script>
<script src="{{ asset('js/admin/product-form.js') }}"></script>
@endsection
