@php($isNew = ! $developer->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Developer' : 'Edit Developer',
    'back' => route('admin.developers.index'),
])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.developers.store') : route('admin.developers.update', $developer) }}"
      class="panel stack">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <label class="field">
        <span>Nama developer *</span>
        <input type="text" name="name" value="{{ old('name', $developer->name) }}" maxlength="120" required>
        @include('admin.partials.error', ['name' => 'name'])
    </label>

    <label class="field">
        <span>Slug (opsional)</span>
        <input type="text" name="slug" value="{{ old('slug', $developer->slug) }}" maxlength="120" placeholder="kosongkan = otomatis dari nama">
        <small class="hint">Huruf kecil, angka, dan tanda hubung saja (a-z, 0-9, -).</small>
        @include('admin.partials.error', ['name' => 'slug'])
    </label>

    <div class="actions">
        <a href="{{ route('admin.developers.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>

@unless($isNew)
    <form method="POST" action="{{ route('admin.developers.destroy', $developer) }}" class="danger-zone"
          onsubmit="return confirm('Hapus developer ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus developer</button>
    </form>
@endunless
@endsection
