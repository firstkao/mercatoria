@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Produk' : 'Tambah Produk')

@php
    $selectedGameIds = array_map('strval', (array) old(
        'game_ids',
        $product->relationLoaded('games') ? $product->games->modelKeys() : [],
    ));
    $gameDefaultStores = $games->mapWithKeys(fn ($game) => [$game->id => $game->defaultStore?->name])->all();
@endphp

@section('content')
<div>
    <a href="{{ route('office.products.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Produk</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $isEditing ? 'Edit Produk: '.$product->name : 'Tambah Produk' }}</h1>
</div>

<form method="POST"
      action="{{ $isEditing ? route('office.products.update', $product) : route('office.products.store') }}"
      class="mt-5 space-y-5"
      x-data="{ defaultStores: @js($gameDefaultStores), selectedGameIds: @js($selectedGameIds), get defaultGameStore() { return this.selectedGameIds.map(id => this.defaultStores[id]).find(Boolean) ?? null } }">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <section class="grid gap-5 rounded-xl border border-border bg-white p-5 md:grid-cols-2 md:p-6">
        <div class="md:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium">Nama Produk *</label>
            <input id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('name')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slug" class="mb-1 block text-sm font-medium">Slug *</label>
            <input id="slug" name="slug" required maxlength="255" value="{{ old('slug', $product->slug) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('slug')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sku" class="mb-1 block text-sm font-medium">SKU *</label>
            <input id="sku" name="sku" required maxlength="64" value="{{ old('sku', $product->sku) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('sku')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="price_rmb" class="mb-1 block text-sm font-medium">Harga (RMB) *</label>
            <input id="price_rmb" name="price_rmb" type="number" min="0" step="0.01" required value="{{ old('price_rmb', $product->price_rmb ?? 0) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('price_rmb')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="type" class="mb-1 block text-sm font-medium">Tipe *</label>
            <select id="type" name="type" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="single" @selected(old('type', $product->type ?? 'single') === 'single')>Produk tunggal</option>
                <option value="variant" @selected(old('type', $product->type) === 'variant')>Produk dengan varian</option>
            </select>
            @error('type')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="availability" class="mb-1 block text-sm font-medium">Ketersediaan *</label>
            <select id="availability" name="availability" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="tersedia" @selected(old('availability', $product->availability ?? 'tersedia') === 'tersedia')>Tersedia</option>
                <option value="kosong" @selected(old('availability', $product->availability) === 'kosong')>Kosong</option>
            </select>
            @error('availability')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="game_ids" class="mb-1 block text-sm font-medium">Game *</label>
            <select id="game_ids" name="game_ids[]" multiple required size="5"
                    x-on:change="selectedGameIds = Array.from($event.target.selectedOptions, option => option.value)"
                    class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" @selected(in_array((string) $game->id, $selectedGameIds))>
                        {{ $game->name }}{{ $game->is_active ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">Pilih satu atau lebih game yang menampilkan produk ini.</p>
            @error('game_ids')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            @error('game_ids.*')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="store_id" class="mb-1 block text-sm font-medium">Store</label>
            <select id="store_id" name="store_id" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="">— Pakai default game —</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((string) old('store_id', $product->store_id) === (string) $store->id)>
                        {{ $store->name }} ({{ $store->hasMinBelanja() ? number_format((float) $store->min_belanja_rmb, 2, ',', '.').' RMB' : 'tanpa minimum' }} · {{ $store->isFreeShipping() ? 'Gratis' : number_format((float) $store->ongkir_rmb, 2, ',', '.').' RMB' }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">
                <span x-show="defaultGameStore" x-text="'Default game: ' + defaultGameStore"></span>
                <span x-show="!defaultGameStore">Game tidak memiliki default store. Tanpa override, ongkir lokal CN menjadi Rp 0.</span>
            </p>
            @error('store_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            <a href="{{ route('office.stores.create') }}" class="mt-2 inline-block text-sm text-navy-500 hover:underline">+ Store Baru</a>
        </div>
        <div class="md:col-span-2">
            <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
            <textarea id="description" name="description" rows="5" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">{{ old('description', $product->description) }}</textarea>
            @error('description')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->exists ? $product->is_active : true)) class="rounded border-border text-primary-500 focus:ring-primary-400">
                Terbitkan produk di katalog
            </label>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.products.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Produk' }}</x-button>
    </div>
</form>
@endsection
