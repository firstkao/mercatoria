@extends('layouts.admin')

@section('title', $slide->exists ? 'Edit Hero Slide' : 'Tambah Hero Slide')

@section('content')
<h1 class="mb-5 text-2xl font-bold">{{ $slide->exists ? 'Edit Hero Slide' : 'Tambah Hero Slide' }}</h1>
<form method="POST" enctype="multipart/form-data" action="{{ $slide->exists ? route('office.hero-slides.update', $slide) : route('office.hero-slides.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-border bg-white p-6">
    @csrf
    @if ($slide->exists) @method('PUT') @endif
    @if ($slide->exists)
        <img src="{{ Storage::disk('public')->url($slide->image_path) }}" alt="" class="h-32 rounded-lg object-cover">
    @endif
    <label class="block text-sm">Gambar {{ $slide->exists ? '(opsional untuk mempertahankan gambar sekarang)' : '' }}
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(! $slide->exists) class="mt-1 block w-full text-sm">
        @error('image')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm">Judul
        <input name="title" value="{{ old('title', $slide->title) }}" maxlength="255" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        @error('title')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm">URL tujuan (URL https:// atau path internal /...)
        <input name="link_url" value="{{ old('link_url', $slide->link_url) }}" maxlength="500" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        @error('link_url')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm">Urutan
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        @error('sort_order')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $slide->is_active ?? true))> Aktif</label>
    @error('is_active')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    <div class="flex gap-3"><button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('office.hero-slides.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm">Batal</a></div>
</form>
@endsection
