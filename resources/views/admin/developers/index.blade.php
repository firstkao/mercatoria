@extends('admin.layouts.admin')

@section('title', 'Developer')

@section('content')
    <h1 class="text-xl font-semibold">Developer</h1>

    <form method="POST" action="{{ route('office.developers.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="name" placeholder="Nama developer" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($developers as $developer)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('office.developers.update', $developer) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $developer->name }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <span class="py-2 text-xs text-gray-400">{{ $developer->slug }}</span>
                    <label class="flex items-center gap-1 py-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($developer->is_active)> Aktif</label>
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Simpan</button>
                </form>
                <form method="POST" action="{{ route('office.developers.destroy', $developer) }}" class="mt-2" onsubmit="return confirm('Hapus developer?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Hapus developer</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
