@php($isNew = ! $slide->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Slide' : 'Edit Slide',
    'back' => route('admin.hero-slides.index'),
])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.hero-slides.store') : route('admin.hero-slides.update', $slide) }}"
      enctype="multipart/form-data" class="panel stack">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <label class="field">
        <span>Judul (opsional)</span>
        <input type="text" name="title" value="{{ old('title', $slide->title) }}" maxlength="150">
        @include('admin.partials.error', ['name' => 'title'])
    </label>

    <label class="field">
        <span>Subjudul (opsional)</span>
        <input type="text" name="subtitle" value="{{ old('subtitle', $slide->subtitle) }}" maxlength="255">
        @include('admin.partials.error', ['name' => 'subtitle'])
    </label>

    <label class="field">
        <span>Link URL (opsional)</span>
        <input type="url" name="link_url" value="{{ old('link_url', $slide->link_url) }}" maxlength="500" placeholder="https://...">
        @include('admin.partials.error', ['name' => 'link_url'])
    </label>

    <label class="field">
        <span>Alt text (untuk SEO, opsional)</span>
        <input type="text" name="alt_text" value="{{ old('alt_text', $slide->alt_text) }}" maxlength="255">
        @include('admin.partials.error', ['name' => 'alt_text'])
    </label>

    <div class="field">
        <span>Gambar (rasio 16:9, maks 4 MB)</span>
        @if ($slide->image_path)
            <div style="margin-bottom:10px;">
                <img src="{{ $slide->imageUrl() }}" alt="" style="max-width:320px;border-radius:8px;">
            </div>
        @endif
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <small class="hint">{{ $slide->image_path ? 'Pilih file baru untuk mengganti.' : 'JPG, PNG, atau WebP.' }}</small>
        @include('admin.partials.error', ['name' => 'image'])
    </div>

    <div class="field-row">
        <label class="field field--short">
            <span>Urutan</span>
            <input type="text"
                   name="sort_order"
                   value="{{ old('sort_order', $slide->sort_order) }}"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   autocomplete="off"
                   placeholder="0">
        </label>
        <label class="switch" style="margin-top:25px;">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->is_active))>
            <span>Aktif</span>
        </label>
    </div>

    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.hero-slides.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>

@unless($isNew)
    <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" class="danger-zone"
          onsubmit="return confirm('Hapus slide ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus slide</button>
    </form>
@endunless
@endsection