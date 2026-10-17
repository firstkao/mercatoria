@extends('layouts.admin')

@section('title', 'Import Order Lama')

@section('content')
<div>
    <a href="{{ route('office.users.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Customer</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">Import Order Lama</h1>
    <p class="mt-1 text-sm text-text-muted">Catat harga dan tanggal dari WooCommerce. Order disimpan berstatus selesai dengan snapshot historis.</p>
    <div class="mt-3 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('office.users.create') }}" class="rounded-lg border border-border px-3 py-2 font-medium text-navy-500 hover:bg-surface-2">+ User Baru</a>
        <a href="{{ route('office.products.create') }}" class="rounded-lg border border-border px-3 py-2 font-medium text-navy-500 hover:bg-surface-2">+ Produk Baru</a>
    </div>
</div>

<form method="POST" action="{{ route('office.orders.store') }}" class="mt-5 space-y-5">
    @csrf
    <section class="grid gap-5 rounded-xl border border-border bg-white p-5 md:grid-cols-2 md:p-6">
        <div class="md:col-span-2">
            <label for="user_search" class="mb-1 block text-sm font-medium">Cari customer migrasi</label>
            <div class="flex gap-2">
                <input id="user_search" name="q" type="search" form="legacy-user-search" value="{{ $search }}" placeholder="Nama, email, atau nomor HP" class="w-full rounded-lg border-border">
                <button form="legacy-user-search" type="submit" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold">Cari</button>
            </div>
            <p class="mt-1 text-xs text-text-muted">Pilih customer dari hasil pencarian atau daftar di bawah.</p>
        </div>
        <div class="md:col-span-2">
            <label for="user_id" class="mb-1 block text-sm font-medium">Customer Migrasi *</label>
            <select id="user_id" name="user_id" required class="w-full rounded-lg border-border">
                <option value="">Pilih customer</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $selectedUserId) === (string) $user->id)>
                        {{ $user->name }} — {{ $user->email }}
                    </option>
                @endforeach
            </select>
            @error('user_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="product_id" class="mb-1 block text-sm font-medium">Produk *</label>
            <select id="product_id" name="product_id" required class="w-full rounded-lg border-border">
                <option value="">Pilih produk lama</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                        {{ $product->name }} — {{ $product->sku }}
                    </option>
                @endforeach
            </select>
            @error('product_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="variant_id" class="mb-1 block text-sm font-medium">Varian (opsional)</label>
            <select id="variant_id" name="variant_id" class="w-full rounded-lg border-border">
                <option value="">Tanpa varian</option>
                @foreach ($products as $product)
                    @foreach ($product->variants as $variant)
                        <option value="{{ $variant->id }}" @selected((string) old('variant_id') === (string) $variant->id)>
                            {{ $product->name }} — {{ $variant->name ?: $variant->sku }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">Pilih varian bila item lama memang memiliki varian.</p>
            @error('variant_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="qty" class="mb-1 block text-sm font-medium">Jumlah *</label>
            <input id="qty" name="qty" type="number" min="1" max="10000" required value="{{ old('qty', 1) }}"
                   class="w-full rounded-lg border-border">
            @error('qty')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="unit_price" class="mb-1 block text-sm font-medium">Harga per item (Rp) *</label>
            <input id="unit_price" name="unit_price" type="number" min="0" max="99999999.99" step="0.01" required value="{{ old('unit_price') }}"
                   class="w-full rounded-lg border-border">
            @error('unit_price')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="ordered_at" class="mb-1 block text-sm font-medium">Tanggal Order *</label>
            <input id="ordered_at" name="ordered_at" type="datetime-local" required value="{{ old('ordered_at', now()->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-lg border-border">
            @error('ordered_at')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
    </section>
    <p class="rounded-lg border border-info-200 bg-info-50 p-4 text-sm text-text-muted">
        Snapshot menyimpan data produk, harga historis, jumlah, berat, serta tanggal order. Ongkir historis tidak ditambahkan karena detailnya tidak tersedia.
    </p>
    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.users.index') }}">Batal</x-button>
        <x-button type="submit">Simpan Snapshot Order</x-button>
    </div>
</form>
<form id="legacy-user-search" method="GET" action="{{ route('office.orders.create') }}" class="hidden">
    @if ($selectedUserId)<input type="hidden" name="user_id" value="{{ $selectedUserId }}">@endif
</form>
@endsection
