@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Game' : 'Tambah Game')

@php
    $selectedDeveloperIds = old('developer_ids', $game->relationLoaded('developers') ? $game->developers->modelKeys() : []);
    $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    $storeOptions = $stores->map(fn ($store) => [
        'id' => (string) $store->id,
        'label' => $store->name.' ('.($store->hasMinBelanja() ? $fmt($store->min_belanja_rmb).' RMB' : 'tanpa minimum').' · '.($store->isFreeShipping() ? 'Gratis' : $fmt($store->ongkir_rmb).' RMB').')',
    ])->values();
    $fieldClass = 'w-full rounded-lg border focus:border-primary-400 focus:ring-primary-400';
@endphp

@section('content')
<a href="{{ route('office.games.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Game</a>

<form method="POST" enctype="multipart/form-data"
      action="{{ $isEditing ? route('office.games.update', $game) : route('office.games.store') }}" class="mt-2">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-navy-500">{{ $isEditing ? 'Edit Game: '.$game->name : 'Tambah Game' }}</h1>
        <div class="flex gap-3">
            @if ($isEditing)
                <x-button variant="ghost" href="{{ route('games.show', $game) }}" target="_blank" rel="noopener">Preview</x-button>
            @endif
            <x-button type="submit">{{ $isEditing ? 'Simpan' : 'Simpan Game' }}</x-button>
        </div>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <section class="space-y-5 rounded-xl border border-border bg-white p-5 md:p-6 lg:col-span-2">
            <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Informasi Game</h2>
            <hr class="border-border">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Nama Game <span class="text-danger-500">*</span></label>
                <input id="name" name="name" required maxlength="255" value="{{ old('name', $game->name) }}"
                       class="{{ $fieldClass }} {{ $errors->has('name') ? 'border-danger-500 bg-danger-50' : 'border-border' }}">
                @error('name')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="mb-1 block text-sm font-medium">Slug <span class="text-danger-500">*</span></label>
                <input id="slug" name="slug" required maxlength="255" value="{{ old('slug', $game->slug) }}"
                       class="{{ $fieldClass }} {{ $errors->has('slug') ? 'border-danger-500 bg-danger-50' : 'border-border' }}">
                @error('slug')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="{{ $fieldClass }} border-border">{{ old('description', $game->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
            </div>
            <div>
                <label for="developer_ids" class="mb-1 block text-sm font-medium">Developer <span class="text-danger-500">*</span></label>
                <select id="developer_ids" name="developer_ids[]" multiple size="5" required
                        class="{{ $fieldClass }} {{ $errors->has('developer_ids') ? 'border-danger-500 bg-danger-50' : 'border-border' }}">
                    @foreach ($developers as $developer)
                        <option value="{{ $developer->id }}" @selected(in_array($developer->id, $selectedDeveloperIds))>{{ $developer->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-text-muted">ℹ️ Multi-select: 1 game bisa punya lebih dari satu developer (Ctrl/Cmd + klik).</p>
                @error('developer_ids')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
                @error('developer_ids.*')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
            </div>

            <hr class="border-border">

            <div x-data="{
                    search: '',
                    open: false,
                    selectedStore: @js((string) old('default_store_id', $game->default_store_id ?? '')),
                    stores: @js($storeOptions),
                    get filtered() { return this.stores.filter(s => s.label.toLocaleLowerCase('id').includes(this.search.toLocaleLowerCase('id'))) },
                    get selectedLabel() { return this.stores.find(s => s.id === this.selectedStore)?.label ?? '' },
                    choose(id) { this.selectedStore = id; this.open = false; this.search = '' },
                 }"
                 x-on:keydown.escape="open = false" x-on:click.outside="open = false">
                <label id="default-store-label" for="default-store-search" class="mb-1 block text-sm font-medium">
                    Default Store @if ($stores->isNotEmpty())<span class="text-danger-500">*</span>@endif
                </label>
                <input type="hidden" name="default_store_id" :value="selectedStore">
                <div class="relative">
                    <input id="default-store-search" type="text" role="combobox" autocomplete="off"
                           aria-controls="default-store-list" aria-haspopup="listbox" :aria-expanded="open.toString()"
                           x-model="search" x-on:focus="open = true" x-on:input="open = true"
                           x-on:keydown.enter.prevent="if (filtered.length) choose(filtered[0].id)"
                           :placeholder="selectedLabel || 'Cari / pilih store...'"
                           class="{{ $fieldClass }} {{ $errors->has('default_store_id') ? 'border-danger-500 bg-danger-50' : 'border-border' }}">
                    <ul id="default-store-list" role="listbox" x-show="open" x-cloak
                        class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-border bg-white shadow">
                        <li role="option" tabindex="-1" class="cursor-pointer px-3 py-2 text-sm italic text-text-light hover:bg-surface-2"
                            x-on:click="choose('')">— Tidak ada default store —</li>
                        <template x-for="store in filtered" :key="store.id">
                            <li role="option" tabindex="-1" :aria-selected="(store.id === selectedStore).toString()"
                                class="cursor-pointer px-3 py-2 text-sm hover:bg-surface-2"
                                :class="store.id === selectedStore ? 'font-semibold text-navy-500' : ''"
                                x-on:click="choose(store.id)" x-text="store.label"></li>
                        </template>
                    </ul>
                </div>
                <p class="mt-1 text-xs text-text-muted">ℹ️ Store default untuk semua produk game ini.<br>Produk bisa override kalau berbeda (misal collab).</p>
                @error('default_store_id')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
                <a href="{{ route('office.stores.create') }}" class="mt-3 inline-block rounded-lg border border-primary-300 px-3 py-1.5 text-sm text-navy-500 hover:bg-primary-50">+ Tambah Store Baru</a>
            </div>
        </section>

        <aside class="h-fit space-y-5">
            <section class="rounded-xl border border-border bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Logo</h2>
                @if ($game->logo_path)
                    <img src="{{ Storage::disk('public')->url($game->logo_path) }}" alt="Logo {{ $game->name }}" class="mt-3 h-20 w-20 rounded-lg border border-border object-contain">
                @endif
                <input type="file" name="logo" accept="image/png,image/webp" aria-label="Upload logo"
                       class="mt-3 block w-full text-sm">
                <p class="mt-1 text-xs text-text-muted">PNG/WebP, maksimal 500 KB. Logo lama otomatis diganti.</p>
                @error('logo')<p class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
            </section>

            <section class="rounded-xl border border-border bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Status</h2>
                <input type="hidden" name="is_active" value="0">
                <label class="mt-3 inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $game->exists ? $game->is_active : true)) class="rounded border-border text-primary-500 focus:ring-primary-400">
                    Aktif
                </label>
            </section>
        </aside>
    </div>

    <div class="mt-5 flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.games.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Game' }}</x-button>
    </div>
</form>
@endsection
