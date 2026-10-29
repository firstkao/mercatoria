@extends('admin.layouts.admin')

@section('title', 'Halaman CMS')

@section('content')
    <h1 class="text-xl font-semibold">Halaman CMS</h1>

    <form method="POST" action="{{ route('office.pages.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="title" placeholder="Judul" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="text" name="slug" placeholder="slug-halaman" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="text" name="content" placeholder="Isi halaman" class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <label class="flex items-center gap-1 py-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($pages as $page)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('office.pages.update', $page) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $page->title }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <span class="py-2 text-xs text-gray-400">/{{ $page->slug }}</span>
                    <input type="text" name="content" value="{{ $page->content }}" class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <label class="flex items-center gap-1 py-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($page->is_active)> Aktif</label>
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Simpan</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
