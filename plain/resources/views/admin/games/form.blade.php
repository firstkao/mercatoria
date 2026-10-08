@php($isNew = ! $game->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Game' : 'Edit Game',
    'back' => route('admin.games.index'),
])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.games.store') : route('admin.games.update', $game) }}"
      enctype="multipart/form-data" class="panel stack">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <label class="field">
        <span>Nama game *</span>
        <input type="text" name="name" value="{{ old('name', $game->name) }}" maxlength="120" required>
        @include('admin.partials.error', ['name' => 'name'])
    </label>

    <label class="field">
        <span>Slug (opsional)</span>
        <input type="text" name="slug" value="{{ old('slug', $game->slug) }}" maxlength="120" placeholder="kosongkan = otomatis dari nama">
        <small class="hint">Huruf kecil, angka, dan tanda hubung saja (a-z, 0-9, -).</small>
        @include('admin.partials.error', ['name' => 'slug'])
    </label>

    <label class="field field--short">
        <span>Developer</span>
        <select name="developer_id">
            <option value="">— Tanpa developer —</option>
            @foreach ($developers as $developer)
                <option value="{{ $developer->id }}" @selected((int) old('developer_id', $game->developer_id) === $developer->id)>
                    {{ $developer->name }}
                </option>
            @endforeach
        </select>
        @include('admin.partials.error', ['name' => 'developer_id'])
    </label>

    <div class="field">
        <span>Gambar/Logo (maks 2 MB)</span>
        @if ($game->image_path)
            <div class="mb-3">
                <img src="{{ $game->imageUrl() }}" alt="" class="thumb-preview--sm">
            </div>
        @endif
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <small class="hint">{{ $game->image_path ? 'Pilih file baru untuk mengganti.' : 'JPG, PNG, atau WebP.' }}</small>
        @include('admin.partials.error', ['name' => 'image'])
    </div>

    <label class="field field--short">
        <span>Urutan</span>
        <input type="text"
               name="sort_order"
               value="{{ old('sort_order', $game->sort_order ?? 0) }}"
               inputmode="numeric"
               pattern="[0-9]*"
               autocomplete="off"
               placeholder="0">
        <small class="hint">Urutan terkecil tampil duluan.</small>
        @include('admin.partials.error', ['name' => 'sort_order'])
    </label>

    <div class="actions">
        <a href="{{ route('admin.games.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>

@unless($isNew)
    <form method="POST" action="{{ route('admin.games.destroy', $game) }}" class="danger-zone"
          onsubmit="return confirm('Hapus game ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus game</button>
    </form>
@endunless
@endsection
