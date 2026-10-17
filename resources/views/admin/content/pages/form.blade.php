@extends('layouts.admin')

@section('title', $page->exists ? 'Edit Halaman' : 'Buat Halaman')

@section('content')
    <div>
        <a href="{{ route('office.pages.index') }}" class="text-sm text-navy-500 hover:underline">← Halaman Konten</a>
        <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $page->exists ? 'Edit Halaman' : 'Halaman Baru' }}</h1>
    </div>
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ $page->exists ? route('office.pages.update', $page) : route('office.pages.store') }}"
          class="mt-5 space-y-4 rounded-xl border border-border bg-white p-5">
        @csrf
        @if ($page->exists) @method('PUT') @endif
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium">Judul
                <input name="title" value="{{ old('title', $page->title) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Slug
                <input name="slug" value="{{ old('slug', $page->slug) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
        </div>
        <label class="block text-sm font-medium">Konten HTML
            <textarea name="content" rows="16" required maxlength="100000"
                      class="mt-1 w-full rounded-lg border border-border px-3 py-2 font-mono text-sm">{{ old('content', $page->content) }}</textarea>
            <span class="mt-1 block text-xs text-text-light">Tag aman seperti heading, paragraf, daftar, tautan, tabel, dan gambar didukung. Script dan atribut event dihapus.</span>
        </label>
        <label class="block text-sm font-medium">Path gambar unggulan (opsional)
            <input name="featured_image_path" value="{{ old('featured_image_path', $page->featured_image_path) }}" maxlength="2048"
                   class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        </label>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium">SEO title
                <input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Status
                <select name="status" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    <option value="draft" @selected(old('status', $page->status ?: 'draft') === 'draft')>Draft</option>
                    <option value="publish" @selected(old('status', $page->status) === 'publish')>Publish</option>
                </select>
            </label>
        </div>
        <label class="block text-sm font-medium">SEO description
            <textarea name="meta_description" rows="3" maxlength="500"
                      class="mt-1 w-full rounded-lg border border-border px-3 py-2">{{ old('meta_description', $page->meta_description) }}</textarea>
        </label>
        <div class="flex gap-3">
            <x-button type="submit">Simpan</x-button>
            <x-button variant="ghost" href="{{ route('office.pages.index') }}">Batal</x-button>
        </div>
    </form>
@endsection
