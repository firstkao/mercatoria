@extends('admin.layouts.admin')

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
    /* ─── Layout ─── */
    #pform-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
    }
    #pform-layout .pform-main {
        border-right: 1px solid var(--color-rule, #e5e7eb);
        min-width: 0;
    }
    #pform-layout .pform-sidebar {
        min-width: 0;
        background: #fafaf8;
    }
    @media (max-width: 1023px) {
        #pform-layout { grid-template-columns: 1fr; }
        #pform-layout .pform-main { border-right: none; border-bottom: 1px solid var(--color-rule, #e5e7eb); }
    }

    /* ─── Upload Zones ─── */
    .upload-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #d4d4d0;
        border-radius: 1rem;
        background: linear-gradient(135deg, rgba(248,249,250,0.5) 0%, rgba(240,241,243,0.3) 100%);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .upload-zone:hover {
        border-color: #999;
        background: linear-gradient(135deg, rgba(248,249,250,0.9) 0%, rgba(240,241,243,0.6) 100%);
        transform: translateY(-1px);
    }
    .upload-zone.drag-over {
        border-color: #1A2B4A;
        background: linear-gradient(135deg, rgba(26,43,74,0.04) 0%, rgba(26,43,74,0.02) 100%);
    }
    .upload-zone .upload-icon {
        width: 4rem; height: 4rem;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: transform 0.2s ease;
    }
    .upload-zone:hover .upload-icon { transform: scale(1.05); }

    /* ─── Desc Preview Card ─── */
    .desc-preview-card {
        display: flex; gap: 1.5rem; align-items: flex-start;
        border: 1px solid var(--color-rule, #e5e7eb);
        border-radius: 1rem;
        background: white;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .desc-preview-card img {
        width: 13rem; height: 13rem;
        object-fit: cover;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        flex-shrink: 0;
    }

    /* ─── Sidebar Photo Zone ─── */
    .sidebar-photo-zone {
        border: 2px dashed #d4d4d0;
        border-radius: 0.75rem;
        background: white;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar-photo-zone:hover {
        border-color: #999;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .sidebar-photo-zone.drag-over { border-color: #1A2B4A; }
    .sidebar-photo-zone img { width: 100%; height: 8rem; object-fit: cover; }

    /* ─── Variant Row (create mode) ─── */
    .variant-row {
        border: 1px solid var(--color-rule, #e5e7eb);
        border-radius: 0.75rem;
        padding: 1.25rem;
        background: white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .variant-row .row-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem;
    }
    .variant-row .row-head .idx {
        font-family: monospace; font-size: 10px; text-transform: uppercase;
        letter-spacing: 0.16em; color: rgba(0,0,0,0.35);
    }
    .variant-row .remove-btn {
        color: #E53935; cursor: pointer; background: none; border: none;
        font-size: 1.25rem; line-height: 1; padding: 0.25rem;
        border-radius: 0.375rem;
        transition: all 0.15s;
    }
    .variant-row .remove-btn:hover { color: #C62828; background: rgba(229,57,53,0.08); }

    /* ─── Sidebar Section Cards ─── */
    .sidebar-section {
        padding: 1.25rem 1.25rem;
        border-bottom: 1px solid var(--color-rule, #e5e7eb);
    }
    .sidebar-section:last-child { border-bottom: none; }
    .sidebar-label {
        font-family: monospace;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .sidebar-label i { font-size: 0.85rem; opacity: 0.6; }

    /* ─── Tabs (pill style) ─── */
    .prod-tab {
        position: relative;
        border: none;
        background: transparent;
        border-radius: 0.5rem 0.5rem 0 0;
        transition: all 0.2s ease;
    }
    .prod-tab:hover { background: rgba(0,0,0,0.03); }
    .prod-tab.active {
        background: rgba(26,43,74,0.06);
    }

    /* ─── Pricing Preview Card ─── */
    .pricing-preview {
        background: linear-gradient(145deg, #f8f9fa 0%, #f0f2f5 100%);
        border-left: 4px solid #1A2B4A;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .pricing-preview .calc-table td {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .pricing-preview .calc-table tr:last-child td { border-bottom: none; }

    /* ─── Info Callout ─── */
    .info-callout {
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        padding: 0.75rem 1rem;
        border-radius: 0.625rem;
        background: rgba(26,43,74,0.04);
        border: 1px solid rgba(26,43,74,0.08);
    }

    /* ─── Flash Messages ─── */
    .flash-msg {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        animation: slideDown 0.3s ease;
    }
    .flash-msg.success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .flash-msg.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .flash-msg i { margin-top: 0.125rem; flex-shrink: 0; }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ─── Sticky Form Header ─── */
    .form-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* ─── Primary Button ─── */
    .btn-primary {
        background: #1a1a1a;
        color: white;
        border: none;
        border-radius: 0.625rem;
        padding: 0.625rem 1.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .btn-primary:hover {
        background: #000;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-primary:active { transform: translateY(0); }

    .btn-secondary {
        background: white;
        color: var(--color-ink, #1a1a1a);
        border: 1px solid var(--color-rule, #e5e7eb);
        border-radius: 0.625rem;
        padding: 0.5rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-secondary:hover {
        border-color: var(--color-ink, #1a1a1a);
        background: var(--color-paper, #f8f9fa);
    }

    /* ─── Variant Table ─── */
    .variant-table thead {
        background: linear-gradient(180deg, #f8f9fa 0%, #f3f4f6 100%);
    }
    .variant-table tbody tr {
        transition: background 0.15s;
    }
    .variant-table tbody tr:hover {
        background: rgba(26,43,74,0.02);
    }

    /* ─── Status Badge ─── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .badge-green { background: #ecfdf5; color: #065f46; }
    .badge-amber { background: #fffbeb; color: #92400e; }
    .badge-red   { background: #fef2f2; color: #991b1b; }
    .badge-blue  { background: #eff6ff; color: #1e40af; }
    .badge-gray  { background: #f3f4f6; color: #374151; }
</style>

{{-- ═══ FLASH MESSAGES ═══ --}}
@if (session('status'))
    <div class="flash-msg success mb-4">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('status') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="flash-msg error mb-4">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ═══ GENERAL VALIDATION ERRORS ═══ --}}
@if ($errors->any())
    <div class="flash-msg error mb-4">
        <i class="bi bi-x-circle-fill"></i>
        <div>
            <p class="font-semibold">Periksa kembali input berikut:</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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

    <div class="overflow-hidden rounded-2xl border border-rule bg-white shadow-sm">

        {{-- FORM HEADER (sticky) --}}
        <div class="form-header flex items-center justify-between border-b border-rule px-8 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $product->exists ? 'bg-amber-50' : 'bg-emerald-50' }}">
                    <i class="bi {{ $product->exists ? 'bi-pencil-square text-amber-600' : 'bi-plus-lg text-emerald-600' }}" style="font-size:0.95rem;"></i>
                </div>
                <div>
                    <h1 class="font-display text-base font-semibold text-ink leading-tight">
                        {{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}
                    </h1>
                    @if ($product->exists)
                        <p class="text-[11px] text-ink/45">{{ $product->sku }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                    <a href="{{ route('office.products.preview', $product) }}" target="_blank"
                       class="btn-secondary flex items-center gap-1.5">
                        <i class="bi bi-eye" style="font-size:0.85rem;"></i>
                        Preview
                    </a>
                @endif
                <button type="submit"
                        onclick="document.getElementById('input-is-active').value='1'"
                        class="btn-primary">
                    Simpan Produk
                </button>
            </div>
        </div>

        {{-- 2 KOLOM --}}
        <div id="pform-layout">

            {{-- ═══════════ KONTEN UTAMA (kiri) ═══════════ --}}
            <div class="pform-main">

                {{-- Nama + Slug --}}
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-ink">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="input-name" value="{{ old('name', $product->name) }}" required
                                   class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10"
                                   placeholder="Millennium T-Shirt Vol.2">
                            @error('name')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-ink">
                                Slug <span class="ml-1 text-xs font-normal text-ink/40">otomatis dari nama</span>
                            </label>
                            <input type="text" name="slug" id="input-slug" value="{{ old('slug', $product->slug) }}"
                                   class="mt-2 w-full rounded-lg border border-rule bg-paper/40 px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10"
                                   placeholder="millennium-tshirt-vol-2">
                            @error('slug')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- TABS --}}
                <div class="border-t border-rule">
                    <div class="flex gap-1 border-b border-rule px-8 pt-3" role="tablist">
                        <button type="button" data-prod-tab="kalkulasi"
                                class="prod-tab border-b-2 border-ink px-5 py-3 text-sm font-semibold text-ink">
                            <i class="bi bi-calculator me-1.5" style="font-size:0.85rem;"></i>
                            Kalkulasi Harga
                        </button>
                        <button type="button" data-prod-tab="atribut"
                                class="prod-tab border-b-2 border-transparent px-5 py-3 text-sm text-ink/50 hover:text-ink">
                            <i class="bi bi-tags me-1.5" style="font-size:0.85rem;"></i>
                            Atribut
                        </button>
                        <button type="button" data-prod-tab="varian"
                                class="prod-tab border-b-2 border-transparent px-5 py-3 text-sm text-ink/50 hover:text-ink">
                            <i class="bi bi-layers me-1.5" style="font-size:0.85rem;"></i>
                            Varian
                        </button>
                    </div>

                    {{-- ──── PANEL: Kalkulasi Harga ──── --}}
                    <div class="prod-panel p-8" data-prod-panel="kalkulasi">

                        {{-- Row 1: SKU + Harga RMB + Berat --}}
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-semibold text-ink">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                                       class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                @error('sku')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-ink">Harga RMB <span class="text-danger">*</span></label>
                                <div class="relative mt-2">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink/40">¥</span>
                                    <input type="number" step="0.01" name="price_rmb" id="input-price-rmb"
                                           value="{{ old('price_rmb', $product->price_rmb) }}" required
                                           class="w-full rounded-lg border border-rule py-2.5 pl-8 pr-4 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                </div>
                                @error('price_rmb')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            {{-- [FIX #4] Berat — tambah required + min --}}
                            <div>
                                <label class="block text-sm font-semibold text-ink">Berat (gram) <span class="text-danger">*</span></label>
                                <div class="relative mt-2">
                                    <input type="number" name="weight_gram" id="input-weight-gram" required min="1"
                                           value="{{ old('weight_gram', $product->weight_gram) }}"
                                           class="w-full rounded-lg border border-rule py-2.5 pl-4 pr-10 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-ink/40">gram</span>
                                </div>
                                @error('weight_gram')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="info-callout mt-4">
                            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.85rem;"></i>
                            <p class="text-xs text-ink/60">Berat dipakai untuk hitung ongkir internasional per kg. Varian bisa override.</p>
                        </div>

                        {{-- Row 2: 3 Ongkir (read-only) --}}
                        <div class="mt-8">
                            <p class="font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/40">Ongkir (Otomatis)</p>
                            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="rounded-lg border border-rule bg-white p-4">
                                    <p class="text-xs text-ink/50">Ongkir Lokal CN (RMB)</p>
                                    <p class="mt-1.5 font-mono text-sm font-semibold text-ink" id="disp-ongkir-lokal">—</p>
                                    <p class="mt-1 text-[10px] text-ink/40" id="info-ongkir-lokal">Store: —</p>
                                </div>
                                <div class="rounded-lg border border-rule bg-white p-4">
                                    <p class="text-xs text-ink/50">Ongkir CN → BTM (IDR)</p>
                                    <p class="mt-1.5 font-mono text-sm font-semibold text-ink" id="disp-ongkir-cnbtm">—</p>
                                    <p class="mt-1 text-[10px] text-ink/40" id="info-ongkir-cnbtm">—</p>
                                </div>
                                <div class="rounded-lg border border-rule bg-white p-4">
                                    <p class="text-xs text-ink/50">Ongkir BTM → JKT (IDR)</p>
                                    <p class="mt-1.5 font-mono text-sm font-semibold text-ink" id="disp-ongkir-btmjkt">—</p>
                                    <p class="mt-1 text-[10px] text-ink/40" id="info-ongkir-btmjkt">—</p>
                                </div>
                            </div>
                            <p class="mt-2 text-[10px] text-ink/40">Semua ongkir dihitung otomatis (read-only). Rute: China → Batam → Jakarta.</p>
                        </div>

                        {{-- PREVIEW PERHITUNGAN --}}
                        <div class="pricing-preview mt-8 p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/50">Preview Perhitungan Harga</h3>
                                <span class="badge badge-gray">Admin Only</span>
                            </div>

                            {{-- Input Summary --}}
                            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                                <div class="rounded-lg bg-white/70 p-3">
                                    <p class="text-[10px] text-ink/40">Harga RMB</p>
                                    <p class="mt-0.5 font-mono text-sm font-medium text-ink" id="pv-rmb">—</p>
                                </div>
                                <div class="rounded-lg bg-white/70 p-3">
                                    <p class="text-[10px] text-ink/40">Berat</p>
                                    <p class="mt-0.5 font-mono text-sm font-medium text-ink" id="pv-berat">—</p>
                                </div>
                                <div class="rounded-lg bg-white/70 p-3">
                                    <p class="text-[10px] text-ink/40">Store</p>
                                    <p class="mt-0.5 font-mono text-sm font-medium text-ink" id="pv-store">—</p>
                                </div>
                                <div class="rounded-lg bg-white/70 p-3">
                                    <p class="text-[10px] text-ink/40">Rate</p>
                                    <p class="mt-0.5 font-mono text-sm font-medium text-ink" id="pv-rate">—</p>
                                </div>
                            </div>

                            {{-- Calculation Table --}}
                            <div class="mt-4 rounded-lg border border-rule bg-white">
                                <table class="calc-table w-full font-mono text-xs">
                                    <tbody>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60">Harga Produk</td>
                                            <td class="px-2 py-2.5 text-ink/50" id="calc-harga-formula">—</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-harga">—</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60">Ongkir Lokal CN</td>
                                            <td class="px-2 py-2.5 text-ink/50" id="calc-lokal-formula">—</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-lokal">—</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60">Ongkir CN-BTM</td>
                                            <td class="px-2 py-2.5 text-ink/50" id="calc-cnbtm-formula">—</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-cnbtm">—</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60">Ongkir BTM-JKT</td>
                                            <td class="px-2 py-2.5 text-ink/50" id="calc-btmjkt-formula">—</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-btmjkt">—</td>
                                        </tr>
                                        <tr id="calc-diskon-row" class="hidden">
                                            <td class="px-4 py-2.5 text-emerald-700">Diskon</td>
                                            <td class="px-2 py-2.5 text-emerald-600/70" id="calc-diskon-formula">—</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-emerald-600" id="calc-diskon">—</td>
                                        </tr>
                                        <tr class="bg-paper/50">
                                            <td class="px-4 py-2.5 font-semibold text-ink" colspan="2">Subtotal</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-ink" id="calc-subtotal">—</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60" colspan="2" id="calc-profit-label">Profit</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-profit">—</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2.5 text-ink/60" colspan="2">Total (sebelum bulat)</td>
                                            <td class="px-4 py-2.5 text-right font-medium text-ink" id="calc-total">—</td>
                                        </tr>
                                        <tr class="border-t-2 border-ink/15">
                                            <td class="px-4 py-3 font-semibold text-ink" colspan="2">Total (setelah bulat)</td>
                                            <td class="px-4 py-3 text-right font-mono text-base font-bold text-[#1A2B4A]" id="calc-rounded">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-xs text-ink/50">
                                    <i class="bi bi-eye-slash me-1" style="font-size:0.75rem;"></i>
                                    Customer cuma lihat:
                                    <span class="ml-1 rounded bg-white px-2 py-0.5 font-mono text-sm font-semibold text-ink">
                                        Rp <span id="calc-customer">—</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ──── PANEL: Atribut ──── --}}
                    <div class="prod-panel hidden p-8" data-prod-panel="atribut">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-ink">Tipe <span class="text-danger">*</span></label>
                                <select name="type" required class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                    <option value="single" @selected(old('type', $product->type ?: 'single') === 'single')>Single</option>
                                    <option value="variant" @selected(old('type') === 'variant' || $product->type === 'variant')>Varian</option>
                                </select>
                                @error('type')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-ink">Game <span class="text-danger">*</span></label>
                                <select name="game_id" id="input-game-id" required class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                    @foreach ($games as $game)
                                        <option value="{{ $game->id }}" @selected(old('game_id', $product->game_id) == $game->id)>{{ $game->name }}</option>
                                    @endforeach
                                </select>
                                @error('game_id')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-ink">Developer <span class="text-xs font-normal text-ink/40">(opsional)</span></label>
                                <select name="developer_id" id="input-developer-id" class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                    <option value="">— tanpa developer —</option>
                                    @foreach ($developerList as $dev)
                                        <option value="{{ $dev->id }}" @selected(old('developer_id', $product->developer_id) == $dev->id)>{{ $dev->name }}</option>
                                    @endforeach
                                </select>
                                @error('developer_id')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-ink">Kategori Merch</label>
                                <select name="merch_category_id" class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                                    <option value="">— auto —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('merch_category_id')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="info-callout mt-6">
                            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.85rem;"></i>
                            <p class="text-xs text-ink/60">Ongkir dihitung otomatis dari berat. Rute: China → Batam → Jakarta.</p>
                        </div>
                    </div>

                    {{-- ──── PANEL: Varian ──── --}}
                    <div class="prod-panel hidden p-8" data-prod-panel="varian">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-ink">Varian Produk</p>
                                @if ($product->exists && $product->variants->count())
                                    <p class="mt-0.5 text-xs text-ink/45">{{ $product->variants->count() }} varian terdaftar</p>
                                @endif
                            </div>
                            @if ($product->exists)
                                <button type="button" onclick="openAddVariantModal()"
                                        class="btn-primary flex items-center gap-2 !px-5 !py-2.5 !text-xs">
                                    <i class="bi bi-plus-lg" style="font-size:0.8rem;"></i>
                                    Tambah Varian
                                </button>
                            @endif
                        </div>

                        @if ($product->exists)
                            @if ($product->variants->count())
                                {{-- Toolbar --}}
                                <div class="mt-5 flex items-center gap-4 rounded-lg border border-rule bg-paper/40 px-4 py-3">
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-ink/70">
                                        <input type="checkbox" id="check-all-variants" class="h-4 w-4 rounded border-rule">
                                        Pilih semua
                                    </label>
                                    <div class="h-4 w-px bg-rule"></div>
                                    <button type="button" onclick="openBulkWeight()"
                                            class="btn-secondary flex items-center gap-1.5 !py-1.5 !text-xs">
                                        <i class="bi bi-pencil-square" style="font-size:0.8rem;"></i>
                                        Bulk Edit Berat
                                    </button>
                                    <button type="button" onclick="applyBulkPrice()"
                                            class="btn-secondary flex items-center gap-1.5 !py-1.5 !text-xs">
                                        <i class="bi bi-currency-exchange" style="font-size:0.8rem;"></i>
                                        Bulk Edit Harga
                                    </button>
                                    <span class="text-xs text-ink/45" id="variant-selected-count">0 varian terpilih</span>
                                </div>

                                {{-- Table --}}
                                <div class="mt-3 overflow-hidden rounded-xl border border-rule">
                                    <table class="variant-table w-full text-sm">
                                        <thead>
                                            <tr>
                                                <th class="w-10 px-4 py-3"></th>
                                                <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">SKU</th>
                                                <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Nama</th>
                                                <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Harga</th>
                                                <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Berat</th>
                                                <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Status</th>
                                                <th class="px-4 py-3 text-center font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($product->variants as $variant)
                                                <tr class="border-b border-rule/40 last:border-b-0" id="variant-row-{{ $variant->id }}">
                                                    <td class="px-4 py-3">
                                                        <input type="checkbox" class="variant-check h-4 w-4 rounded border-rule"
                                                               value="{{ $variant->id }}"
                                                               data-name="{{ $variant->name }}"
                                                               data-sku="{{ $variant->sku }}"
                                                               data-price="{{ $variant->price_rmb }}"
                                                               data-weight="{{ $variant->weight_gram }}"
                                                               data-availability="{{ $variant->availability }}"
                                                               data-image="{{ $variant->image_path ? asset('storage/' . $variant->image_path) : '' }}">
                                                    </td>
                                                    <td class="px-4 py-3 font-mono text-xs text-ink/50">{{ $variant->sku }}</td>
                                                    <td class="px-4 py-3 font-medium text-ink">{{ $variant->name }}</td>
                                                    <td class="px-4 py-3 font-mono text-ink">{{ $variant->price_rmb ? '¥'.number_format($variant->price_rmb, 2) : '—' }}</td>
                                                    <td class="px-4 py-3 font-mono text-ink">{{ $variant->weight_gram ? $variant->weight_gram.'g' : '—' }}</td>
                                                    <td class="px-4 py-3">
                                                        @if ($variant->availability === 'tersedia')
                                                            <span class="badge badge-green">
                                                                <i class="bi bi-check-circle" style="font-size:0.65rem;"></i> Tersedia
                                                            </span>
                                                        @else
                                                            <span class="badge badge-amber">
                                                                <i class="bi bi-dash-circle" style="font-size:0.65rem;"></i> Kosong
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <button type="button" onclick="editVariant({{ $variant->id }})"
                                                                class="btn-secondary !py-1 !px-3 !text-xs">
                                                            <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                                                            Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="mt-8 flex flex-col items-center gap-4 rounded-xl border-2 border-dashed border-rule py-12">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                                        <i class="bi bi-box-seam text-ink/20" style="font-size:2rem;"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-ink/60">Belum ada varian</p>
                                        <p class="mt-1 text-xs text-ink/40">Tambahkan varian pertama untuk mulai mengelola stok.</p>
                                    </div>
                                    <button type="button" onclick="openAddVariantModal()"
                                            class="btn-primary flex items-center gap-2">
                                        <i class="bi bi-plus-lg" style="font-size:0.85rem;"></i>
                                        Tambah Varian Pertama
                                    </button>
                                </div>
                            @endif

                        @else
                            <div class="info-callout mt-5">
                                <i class="bi bi-lightbulb mt-0.5 text-amber-500" style="font-size:0.85rem;"></i>
                                <p class="text-xs text-ink/60">Tambahkan varian sekarang — produk + varian langsung tersimpan bersamaan.</p>
                            </div>

                            <div id="variant-builder" class="mt-5 space-y-4">
                                <div class="variant-row" data-vindex="0">
                                    <div class="row-head">
                                        <span class="idx">Varian #1</span>
                                        <button type="button" class="remove-btn" onclick="removeVariantRow(this)" title="Hapus baris">&times;</button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">Nama Varian</label>
                                            <input type="text" name="variants[0][name]" placeholder="cth: Size L"
                                                   class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">SKU</label>
                                            <input type="text" name="variants[0][sku]" placeholder="SKU-001"
                                                   class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">Harga RMB <span class="font-normal text-ink/40">(opsional)</span></label>
                                            <input type="number" step="0.01" name="variants[0][price_rmb]" placeholder="ikut produk"
                                                   class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">Berat (gram) <span class="font-normal text-ink/40">(opsional)</span></label>
                                            <input type="number" name="variants[0][weight_gram]" placeholder="ikut produk"
                                                   class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">Ketersediaan</label>
                                            <select name="variants[0][availability]" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink">
                                                <option value="tersedia">Tersedia</option>
                                                <option value="kosong">Kosong</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-ink/70">Foto <span class="font-normal text-ink/40">(opsional)</span></label>
                                            <input type="file" name="variants[0][image]" accept=".jpg,.jpeg,.png,.webp"
                                                   class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2 text-xs text-ink/70">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" onclick="addVariantRow()"
                                    class="btn-secondary mt-4 flex items-center gap-2">
                                <i class="bi bi-plus-lg" style="font-size:0.8rem;"></i>
                                Tambah Varian Lagi
                            </button>
                        @endif
                    </div>
                </div>

                {{-- DESKRIPSI GAMBAR PANJANG --}}
                <div class="border-t border-rule p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink">Deskripsi Produk (Gambar Panjang)</p>
                            <p class="mt-0.5 text-xs text-ink/45">Upload gambar deskripsi produk</p>
                        </div>
                        <span class="badge badge-gray">JPG / PNG / WebP · Maks 6 MB</span>
                    </div>

                    <input type="file" id="desc-image-input" name="description_image"
                           accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewDescImage(this)">

                    <div id="desc-upload-area"
                         class="upload-zone mt-5 px-8 py-16 {{ $product->description_image_path ? 'hidden' : '' }}"
                         onclick="document.getElementById('desc-image-input').click()"
                         ondragover="event.preventDefault(); this.classList.add('drag-over');"
                         ondragleave="this.classList.remove('drag-over');"
                         ondrop="event.preventDefault(); this.classList.remove('drag-over'); if(event.dataTransfer.files.length){ document.getElementById('desc-image-input').files = event.dataTransfer.files; previewDescImage(document.getElementById('desc-image-input')); }">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-arrow-up text-ink/35" style="font-size:1.6rem;"></i>
                        </div>
                        <p class="mt-5 text-sm font-medium text-ink">Klik atau drag &amp; drop gambar ke sini</p>
                        <p class="mt-1 text-xs text-ink/45">JPG, PNG, atau WebP — Maks 6 MB</p>
                    </div>

                    <div id="desc-preview-area"
                         class="mt-5 {{ $product->description_image_path ? '' : 'hidden' }}">
                        <div class="desc-preview-card">
                            <img id="desc-preview-img"
                                 src="{{ $product->description_image_path ? asset('storage/' . $product->description_image_path) : '' }}"
                                 alt="Deskripsi">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink truncate" id="desc-preview-name">
                                    {{ $product->description_image_path ? basename($product->description_image_path) : '—' }}
                                </p>
                                <p class="mt-0.5 text-xs text-ink/45" id="desc-preview-size">
                                    {{ $product->description_image_path ? 'Gambar saat ini' : '—' }}
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @if ($product->description_image_path)
                                        <button type="button" id="desc-btn-full"
                                                onclick="window.open('{{ asset('storage/' . $product->description_image_path) }}', '_blank')"
                                                class="btn-secondary flex items-center gap-1.5 !text-xs">
                                            <i class="bi bi-magnifying-glass" style="font-size:0.85rem;"></i>
                                            Lihat Full
                                        </button>
                                    @endif
                                    <button type="button" onclick="document.getElementById('desc-image-input').click()"
                                            class="btn-secondary flex items-center gap-1.5 !text-xs">
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

                    <div class="info-callout mt-4">
                        <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.85rem;"></i>
                        <p class="text-xs text-ink/60">Gambar lama otomatis dihapus saat di-override.</p>
                    </div>
                    @error('description_image')<p class="mt-2 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

            </div>
            {{-- AKHIR KONTEN UTAMA --}}

            {{-- ═══════════ SIDEBAR (kanan) ═══════════ --}}
            <div class="pform-sidebar">

                {{-- TERBITKAN --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-send"></i> Terbitkan
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-sm text-ink/70">Status:</span>
                        @if ($product->exists && $product->is_active)
                            <span class="badge badge-green"><i class="bi bi-check-circle" style="font-size:0.65rem;"></i> Published</span>
                        @else
                            <span class="badge badge-amber"><i class="bi bi-pencil" style="font-size:0.65rem;"></i> Draft</span>
                        @endif
                    </div>
                    <button type="submit"
                            onclick="document.getElementById('input-is-active').value='0'"
                            class="btn-secondary mt-3 w-full flex items-center justify-center gap-2">
                        <i class="bi bi-file-earmark" style="font-size:0.9rem;"></i>
                        Simpan sebagai Draft
                    </button>
                    @if ($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview'))
                        <a href="{{ route('office.products.preview', $product) }}" target="_blank"
                           class="btn-secondary mt-2 w-full flex items-center justify-center gap-2 !text-xs">
                            <i class="bi bi-eye" style="font-size:0.85rem;"></i>
                            Preview
                        </a>
                    @endif
                </div>

                {{-- KETERSEDIAAN + STORE --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-box-seam"></i> Ketersediaan
                    </div>
                    <select name="availability" class="mt-2.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                        <option value="tersedia" @selected(old('availability', $product->availability ?: 'tersedia') === 'tersedia')>Tersedia</option>
                        <option value="kosong" @selected(old('availability') === 'kosong' || $product->availability === 'kosong')>Kosong</option>
                    </select>
                    @error('availability')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror

                    <div class="mt-5">
                        <div class="sidebar-label">
                            <i class="bi bi-shop"></i> Store Override
                            <span class="ml-auto text-[9px] font-normal normal-case tracking-normal text-ink/35">opsional</span>
                        </div>
                        <select name="store_id" id="sidebar-store-id" class="mt-2.5 w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                            <option value="">— gunakan default game —</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('store_id', $product->store_id) == $store->id)
                                        data-min="{{ $store->min_belanja_rmb ?? 0 }}"
                                        data-ongkir="{{ $store->ongkir_rmb ?? 0 }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[10px] text-ink/40" id="store-info">Store digunakan untuk hitung ongkir lokal CN.</p>
                    </div>
                </div>

                {{-- ORDER COUNT --}}
                @if ($product->exists)
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-graph-up"></i> Order Count
                    </div>
                    <div class="mt-2 flex items-end gap-2">
                        <p class="font-display text-3xl font-bold leading-none text-ink">{{ $orderCount }}</p>
                        <p class="mb-0.5 text-sm text-ink/50">order</p>
                    </div>
                </div>
                @endif

                {{-- TAG PRODUK --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-bookmark"></i> Tag Produk
                    </div>
                    <div class="mt-2.5 space-y-2">
                        @foreach ($tags as $tag)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 text-sm text-ink/80 transition-colors hover:bg-white/80">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                       @checked(in_array($tag->id, $productTags))
                                       class="h-4 w-4 rounded border-rule">
                                {{ strtoupper($tag->name) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- GAME (radio) --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-controller"></i> Game
                    </div>
                    <div class="mt-2.5 space-y-2">
                        @foreach ($games as $game)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 text-sm text-ink/80 transition-colors hover:bg-white/80">
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
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-image"></i> Foto Produk
                    </div>

                    <input type="file" id="photo-input" name="image"
                           accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewPhoto(this)">

                    <div id="photo-upload-zone"
                         class="sidebar-photo-zone mt-3"
                         onclick="document.getElementById('photo-input').click()"
                         ondragover="event.preventDefault(); this.classList.add('drag-over');"
                         ondragleave="this.classList.remove('drag-over');"
                         ondrop="event.preventDefault(); this.classList.remove('drag-over'); if(event.dataTransfer.files.length){ document.getElementById('photo-input').files = event.dataTransfer.files; previewPhoto(document.getElementById('photo-input')); }">
                        <div id="photo-preview" class="flex h-32 w-full items-center justify-center overflow-hidden bg-paper/40">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" class="h-full w-full object-cover" alt="Foto">
                            @else
                                <div class="flex flex-col items-center gap-2">
                                    <i class="bi bi-image text-ink/25" style="font-size:1.8rem;"></i>
                                    <span class="text-[11px] text-ink/35">Klik atau drag</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-2 text-center text-[10px] text-ink/40">
                        {{ $product->image_path ? 'Klik / drag untuk ganti foto' : 'JPG, PNG, WebP — Maks 6 MB' }}
                    </p>
                    @error('image')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- [FIX #3] DISKON — currency: RMB --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-percent"></i> Diskon Produk
                    </div>
                    <div class="mt-2.5">
                        <label class="block text-xs font-medium text-ink/60">Nominal (RMB)</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-ink/40">¥</span>
                            {{-- [FIX #1] id="input-discount" — sinkron dengan JS --}}
                            <input type="number" step="0.01" min="0" name="discount_amount" id="input-discount"
                                   value="{{ old('discount_amount', $product->discount_amount) }}"
                                   class="w-full rounded-lg border border-rule py-2 pl-9 pr-3 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                        </div>
                        @error('discount_amount')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-medium text-ink/60">Mulai</label>
                            <input type="date" name="discount_start"
                                   value="{{ old('discount_start', $product->discount_start?->toDateString()) }}"
                                   class="mt-1 w-full rounded-lg border border-rule px-2.5 py-2 text-xs text-ink">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink/60">Berakhir</label>
                            <input type="date" name="discount_end"
                                   value="{{ old('discount_end', $product->discount_end?->toDateString()) }}"
                                   class="mt-1 w-full rounded-lg border border-rule px-2.5 py-2 text-xs text-ink">
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-ink/35">Diskon dalam RMB. Wajib ≤ harga jual.</p>
                </div>

                {{-- SPLIT BOX --}}
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-grid-3x3-gap"></i> Split Box
                    </div>

                    @if ($product->exists && ! $product->is_split_box)
                        <p class="mt-2.5 text-xs leading-relaxed text-ink/55">
                            Buat salinan produk ini untuk jual per slot. Produk asli tetap jual utuh.
                        </p>
                        <button type="button" onclick="openSplitCloneModal()"
                                class="btn-secondary mt-3 w-full flex items-center justify-center gap-2 !font-semibold">
                            <i class="bi bi-grid-3x3-gap" style="font-size:0.9rem;"></i>
                            Buat Split Box
                        </button>

                    @elseif ($product->exists && $product->is_split_box)
                        <div class="mt-2.5 rounded-lg border border-blue-100 bg-blue-50/50 p-3">
                            <p class="text-xs leading-relaxed text-blue-700">
                                <i class="bi bi-diagram-3 me-1" style="font-size:0.8rem;"></i>
                                Salinan split dari
                                @if ($product->parent_id)
                                    <a href="{{ route('office.products.edit', $product->parent_id) }}"
                                       class="font-semibold underline underline-offset-2">{{ $product->parent->name ?? 'produk asli' }}</a>
                                @else
                                    produk lain
                                @endif
                            </p>
                        </div>
                        <p class="mt-2 text-xs text-ink/50">
                            Buat varian slot manual di tab Varian. Contoh: "1a", "1b", "2a", "2b".
                        </p>
                        @if ($product->parent_id)
                            <a href="{{ route('office.products.edit', $product->parent_id) }}"
                               class="btn-secondary mt-2.5 w-full flex items-center justify-center gap-2 !text-xs">
                                <i class="bi bi-arrow-left" style="font-size:0.8rem;"></i>
                                Lihat Produk Asli
                            </a>
                        @endif

                    @else
                        <p class="mt-2.5 text-xs text-ink/50">
                            Simpan produk dulu, lalu buat salinan split box.
                        </p>
                    @endif
                </div>

                {{-- SALIN PRODUK --}}
                @if ($product->exists)
                <div class="sidebar-section">
                    <div class="sidebar-label">
                        <i class="bi bi-copy"></i> Salin Produk
                    </div>
                    <p class="mt-2.5 text-xs leading-relaxed text-ink/55">
                        Duplikat produk ini beserta semua varian, foto, dan tag. Salinan disimpan sebagai draft.
                    </p>
                    <form method="POST" action="{{ route('office.products.duplicate', $product) }}"
                          class="mt-3"
                          onsubmit="return confirm('Salin produk ini? Salinan akan disimpan sebagai Draft.')">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50/50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition-colors hover:border-blue-400 hover:bg-blue-50">
                            <i class="bi bi-copy" style="font-size:0.9rem;"></i>
                            Salin Produk Ini
                        </button>
                    </form>
                    <p class="mt-2 text-center text-[10px] text-ink/35">Salinan otomatis jadi Draft</p>
                </div>
                @endif

                {{-- HAPUS PRODUK --}}
                @if ($product->exists)
                <div class="sidebar-section">
                    <button type="button" onclick="openDeleteModal()"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50/50 px-4 py-2.5 text-sm font-medium text-red-600 transition-colors hover:border-red-400 hover:bg-red-50">
                        <i class="bi bi-trash" style="font-size:0.95rem;"></i>
                        Hapus Produk
                    </button>
                    <p class="mt-2 text-center text-[10px] text-ink/35">Tindakan permanen — tidak bisa dibatalkan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</form>
{{-- AKHIR FORM UTAMA --}}

{{-- ═══ SPLIT BOX MANAGER ═══ --}}
<div class="mt-6">
    @if ($product->exists)
        <livewire:split-box-manager :product="$product" />
    @else
        <div class="overflow-hidden rounded-2xl border border-rule bg-white shadow-sm">
            <div class="flex items-center gap-4 px-6 py-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50">
                    <i class="bi bi-grid-3x3-gap text-blue-500" style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">Split Box — Slot &amp; Kloter</p>
                    <p class="mt-0.5 text-xs text-ink/50">Simpan produk dulu, lalu buat salinan split box.</p>
                </div>
            </div>
            <div class="border-t border-rule bg-paper/30 px-6 py-4">
                <p class="flex items-start gap-2.5 text-xs text-ink/55">
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
    <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                <i class="bi bi-plus-lg text-emerald-600" style="font-size:1.1rem;"></i>
            </div>
            <h3 class="font-display text-lg font-semibold text-ink">Tambah Varian</h3>
            <button type="button" onclick="closeAddVariantModal()" class="ml-auto text-ink/40 hover:text-ink">
                <i class="bi bi-x-lg" style="font-size:1.1rem;"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('office.products.variants.store', $product) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="space-y-5 px-8 py-6">
                <div>
                    <label class="block text-sm font-semibold text-ink">Nama Varian <span class="text-danger">*</span></label>
                    <input type="text" name="name" required
                           class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10"
                           placeholder="cth: Size L / Warna Merah">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-ink">SKU <span class="text-danger">*</span></label>
                        <input type="text" name="sku" required
                               class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-ink">Harga RMB</label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink/40">¥</span>
                            <input type="number" step="0.01" name="price_rmb"
                                   class="w-full rounded-lg border border-rule py-2.5 pl-8 pr-4 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-ink">Berat (gram)</label>
                        <input type="number" name="weight_gram"
                               class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-ink">Ketersediaan</label>
                        <select name="availability"
                                class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                            <option value="tersedia">Tersedia</option>
                            <option value="kosong">Kosong</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink">
                        Foto Varian <span class="text-xs font-normal text-ink/45">(opsional)</span>
                    </label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                           class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink/70">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-rule bg-paper/30 px-8 py-5">
                <button type="button" onclick="closeAddVariantModal()"
                        class="btn-secondary">Batal</button>
                <button type="submit"
                        class="btn-primary flex items-center gap-2">
                    <i class="bi bi-check-lg" style="font-size:0.85rem;"></i>
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
    <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-red-100 bg-red-50/50 px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                <i class="bi bi-exclamation-triangle text-red-600" style="font-size:1.35rem;"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-semibold text-ink">Hapus Produk Permanen?</h3>
                <p class="mt-0.5 text-xs text-ink/55">HARD DELETE — Tidak bisa di-restore.</p>
            </div>
        </div>
        <div class="px-8 py-6">
            <p class="text-sm text-ink/80">Produk: <strong class="text-ink">{{ $product->name }}</strong></p>
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50/50 p-5">
                <p class="flex items-start gap-2 text-xs font-semibold text-red-700">
                    <i class="bi bi-exclamation-triangle mt-0.5" style="font-size:0.9rem;"></i>
                    Produk ini pernah di-order {{ $orderCount }} kali.
                </p>
                <ul class="mt-4 space-y-2 text-xs text-ink/70">
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-x-circle-fill text-red-500 mt-0.5"></i>
                        Data produk &amp; foto asli dihapus PERMANEN dari DB.
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-x-circle-fill text-red-500 mt-0.5"></i>
                        TIDAK BISA DI-RESTORE.
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-check-circle-fill text-green-500 mt-0.5"></i>
                        Order lama TETAP UTUH 100% (baca dari snapshot).
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule bg-paper/30 px-8 py-5">
            <button type="button" onclick="closeDeleteModal()"
                    class="btn-secondary">Batal</button>
            <form method="POST" action="{{ route('office.products.destroy', $product) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-2 rounded-lg bg-[#E53935] px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#C62828]">
                    <i class="bi bi-trash" style="font-size:0.85rem;"></i>
                    Hapus Permanen
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ═══ MODAL: Edit Varian ═══ --}}
@if ($product->exists)
<div id="modal-edit-variant" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                <i class="bi bi-pencil text-blue-600" style="font-size:1.1rem;"></i>
            </div>
            <h3 class="font-display text-lg font-semibold text-ink">Edit Varian</h3>
            <button type="button" onclick="closeEditVariant()" class="ml-auto text-ink/40 hover:text-ink">
                <i class="bi bi-x-lg" style="font-size:1.1rem;"></i>
            </button>
        </div>
        <form id="form-edit-variant" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-5 px-8 py-6">
                <div>
                    <label class="block text-sm font-semibold text-ink">Nama Varian</label>
                    <input type="text" name="name" id="ev-name" required
                           class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-ink">SKU</label>
                        <input type="text" name="sku" id="ev-sku" required
                               class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-ink">Harga RMB</label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink/40">¥</span>
                            <input type="number" step="0.01" name="price_rmb" id="ev-price"
                                   class="w-full rounded-lg border border-rule py-2.5 pl-8 pr-4 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-ink">Berat (gram)</label>
                        <input type="number" name="weight_gram" id="ev-weight"
                               class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-ink">Ketersediaan</label>
                        <select name="availability" id="ev-availability"
                                class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                            <option value="tersedia">Tersedia</option>
                            <option value="kosong">Kosong</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink">
                        Ganti Foto <span class="text-xs font-normal text-ink/45">(opsional)</span>
                    </label>
                    <div id="ev-image-preview" class="mt-2 hidden">
                        <img id="ev-image-thumb" src="" class="h-20 w-20 rounded-lg border border-rule object-cover shadow-sm">
                    </div>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                           class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink/70">
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-rule bg-paper/30 px-8 py-5">
                <button type="button" onclick="deleteVariant()"
                        class="flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition-colors hover:border-red-400 hover:bg-red-50">
                    <i class="bi bi-trash" style="font-size:0.85rem;"></i>
                    Hapus Varian
                </button>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditVariant()"
                            class="btn-secondary">Batal</button>
                    <button type="submit"
                            class="btn-primary">Simpan Varian</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══ MODAL: Bulk Edit Berat ═══ --}}
<div id="modal-bulk-weight" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-[560px] overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                <i class="bi bi-pencil-square text-amber-600" style="font-size:1.1rem;"></i>
            </div>
            <h3 class="font-display text-lg font-semibold text-ink">Bulk Edit Berat Varian</h3>
        </div>
        <div class="px-8 py-6">
            <p class="text-sm text-ink/70">
                <span id="bulk-count" class="font-semibold text-ink">0</span> varian terpilih:
            </p>
            <ul class="mt-3 max-h-32 space-y-1.5 overflow-y-auto text-xs text-ink/60" id="bulk-list"></ul>

            <div class="mt-5">
                <label class="block text-sm font-semibold text-ink">Berat Baru (gram)</label>
                <input type="number" id="bulk-weight-input" placeholder="250" min="1"
                       class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10"
                       oninput="updateBulkPreview()">
            </div>

            <div class="mt-4 rounded-lg border border-rule bg-paper/40 p-4">
                <p class="text-xs font-semibold text-ink">Preview:</p>
                <p class="mt-1.5 font-mono text-xs text-ink/60" id="bulk-preview-text">Masukkan berat untuk preview...</p>
            </div>

            <div class="mt-4 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50/50 p-3.5">
                <i class="bi bi-exclamation-triangle mt-0.5 text-amber-500" style="font-size:0.85rem;"></i>
                <p class="text-xs text-ink/65">Perubahan akan langsung tersimpan ke database.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-rule bg-paper/30 px-8 py-5">
            <button type="button" onclick="closeBulkWeight()"
                    class="btn-secondary">Batal</button>
            <button type="button" onclick="applyBulkWeight()" id="btn-apply-bulk"
                    class="btn-primary flex items-center gap-2">
                <i class="bi bi-check-lg" style="font-size:0.85rem;"></i>
                Apply ke <span id="bulk-apply-count">0</span> Varian
            </button>
        </div>
    </div>
</div>

{{-- MODAL: Bulk Success --}}
<div id="modal-bulk-success" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-green-100 bg-green-50/50 px-8 py-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100">
                <i class="bi bi-check-circle text-green-600" style="font-size:1.3rem;"></i>
            </div>
            <h3 class="font-display text-lg font-semibold text-ink">Berhasil!</h3>
        </div>
        <div class="px-8 py-5">
            <p class="text-sm text-ink/80" id="bulk-success-msg">Varian telah diubah.</p>
        </div>
        <div class="flex items-center justify-end border-t border-rule bg-paper/30 px-8 py-4">
            <button type="button" onclick="closeBulkSuccess()"
                    class="btn-primary">Tutup</button>
        </div>
    </div>
</div>

{{-- ═══ MODAL: Buat Split Box ═══ --}}
@if ($product->exists && ! $product->is_split_box)
<div id="modal-split-clone" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-6">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-rule bg-white shadow-2xl">
        <div class="flex items-center gap-4 border-b border-rule px-8 py-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100">
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
                <div class="rounded-lg border border-blue-100 bg-blue-50/50 p-4">
                    <ul class="space-y-2 text-xs text-ink/70">
                        <li class="flex items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-green-500 mt-0.5" style="font-size:0.8rem;"></i>
                            <span>Nama, foto, deskripsi, game, dll di-copy</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="bi bi-check-circle-fill text-green-500 mt-0.5" style="font-size:0.8rem;"></i>
                            <span>Flag <code class="rounded bg-white px-1 py-0.5 text-[10px]">is_split_box</code> diaktifkan</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="bi bi-arrow-right-circle-fill text-blue-500 mt-0.5" style="font-size:0.8rem;"></i>
                            <span><strong>Varian dipecah manual</strong> oleh admin (misal: 1 → 1a, 1b)</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="bi bi-info-circle-fill text-blue-400 mt-0.5" style="font-size:0.8rem;"></i>
                            <span>Produk asli tetap ada &amp; tidak berubah</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold text-ink">Nama Produk Split <span class="text-danger">*</span></label>
                    <input type="text" name="split_name" required
                           value="{{ ($product->name ?? '') . ' (Split Box)' }}"
                           class="mt-2 w-full rounded-lg border border-rule px-4 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-ink">Harga Per Slot (RMB) <span class="text-danger">*</span></label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink/40">¥</span>
                        <input type="number" step="0.01" name="split_price_rmb" required
                               class="w-full rounded-lg border border-rule py-2.5 pl-8 pr-4 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-rule bg-paper/30 px-8 py-5">
                <button type="button" onclick="closeSplitCloneModal()"
                        class="btn-secondary">Batal</button>
                <button type="submit"
                        class="btn-primary flex items-center gap-2">
                    <i class="bi bi-plus-lg" style="font-size:0.85rem;"></i>
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
            +   '<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">'
            +     '<div><label class="block text-xs font-semibold text-ink/70">Nama Varian</label>'
            +       '<input type="text" name="variants[' + vIndex + '][name]" placeholder="cth: Size L" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10"></div>'
            +     '<div><label class="block text-xs font-semibold text-ink/70">SKU</label>'
            +       '<input type="text" name="variants[' + vIndex + '][sku]" placeholder="SKU-001" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10"></div>'
            +     '<div><label class="block text-xs font-semibold text-ink/70">Harga RMB <span class="font-normal text-ink/40">(opsional)</span></label>'
            +       '<input type="number" step="0.01" name="variants[' + vIndex + '][price_rmb]" placeholder="ikut produk" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10"></div>'
            +     '<div><label class="block text-xs font-semibold text-ink/70">Berat (gram) <span class="font-normal text-ink/40">(opsional)</span></label>'
            +       '<input type="number" name="variants[' + vIndex + '][weight_gram]" placeholder="ikut produk" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:ring-1 focus:ring-ink/10"></div>'
            +     '<div><label class="block text-xs font-semibold text-ink/70">Ketersediaan</label>'
            +       '<select name="variants[' + vIndex + '][availability]" class="mt-1.5 w-full rounded-lg border border-rule px-3.5 py-2.5 text-sm text-ink">'
            +         '<option value="tersedia">Tersedia</option><option value="kosong">Kosong</option></select></div>'
            +     '<div><label class="block text-xs font-semibold text-ink/70">Foto <span class="font-normal text-ink/40">(opsional)</span></label>'
            +       '<input type="file" name="variants[' + vIndex + '][image]" accept=".jpg,.jpeg,.png,.webp" class="mt-1.5 w-full rounded-lg border border-rule px-3 py-2 text-xs text-ink/70"></div>'
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
