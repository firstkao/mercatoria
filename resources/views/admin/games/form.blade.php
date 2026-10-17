@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Game' : 'Tambah Game')

@php
    $selectedDeveloperIds = old('developer_ids', $game->relationLoaded('developers') ? $game->developers->modelKeys() : []);
@endphp

@section('content')
<div>
    <a href="{{ route('office.games.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Game</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $isEditing ? 'Edit Game: '.$game->name : 'Tambah Game' }}</h1>
</div>

<form method="POST" action="{{ $isEditing ? route('office.games.update', $game) : route('office.games.store') }}" class="mt-5 space-y-5">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <section class="space-y-4 rounded-xl border border-border bg-white p-5 md:p-6">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nama game *</label>
            <input id="name" name="name" required maxlength="255" value="{{ old('name', $game->name) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('name')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slug" class="mb-1 block text-sm font-medium">Slug *</label>
            <input id="slug" name="slug" required maxlength="255" value="{{ old('slug', $game->slug) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('slug')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">{{ old('description', $game->description) }}</textarea>
            @error('description')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="developer_ids" class="mb-1 block text-sm font-medium">Developer</label>
            <select id="developer_ids" name="developer_ids[]" multiple size="5" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @foreach ($developers as $developer)
                    <option value="{{ $developer->id }}" @selected(in_array($developer->id, $selectedDeveloperIds))>{{ $developer->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">Pilih satu atau lebih developer. Gunakan Ctrl/Cmd untuk memilih beberapa.</p>
            @error('developer_ids')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            @error('developer_ids.*')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="default_store_id" class="mb-1 block text-sm font-medium">Default Store</label>
            <select id="default_store_id" name="default_store_id" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="">— Tidak ada default store —</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((string) old('default_store_id', $game->default_store_id) === (string) $store->id)>
                        {{ $store->name }} ({{ $store->hasMinBelanja() ? number_format((float) $store->min_belanja_rmb, 2, ',', '.').' RMB' : 'tanpa minimum' }} · {{ $store->isFreeShipping() ? 'Gratis' : number_format((float) $store->ongkir_rmb, 2, ',', '.').' RMB' }})
                    </option>
                @endforeach
            </select>
            @error('default_store_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            <a href="{{ route('office.stores.create') }}" class="mt-2 inline-block text-sm text-navy-500 hover:underline">+ Store Baru</a>
        </div>
        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $game->exists ? $game->is_active : true)) class="rounded border-border text-primary-500 focus:ring-primary-400">
                Game aktif di katalog
            </label>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.games.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Game' }}</x-button>
    </div>
</form>
@endsection
