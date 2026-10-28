@extends('admin.layouts.admin')

@section('title', 'Kategori Merch')

@section('content')
    <h1 class="text-xl font-semibold">Kategori Merch (Auto-Group)</h1>

    <form method="POST" action="{{ route('office.merch-categories.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="name" placeholder="Nama kategori" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="number" name="priority" placeholder="Prioritas" required class="w-28 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="text" name="keywords" placeholder="keyword1, keyword2" class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($categories as $category)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('office.merch-categories.update', $category) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $category->name }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input type="number" name="priority" value="{{ $category->priority }}" class="w-28 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input type="text" name="keywords" value="{{ implode(', ', $category->keywords ?? []) }}" class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Simpan</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
