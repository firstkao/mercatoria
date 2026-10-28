@extends('admin.layouts.admin')

@section('title', 'Game')

@section('content')
    <h1 class="text-xl font-semibold">Game</h1>

    <form method="POST" action="{{ route('office.games.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="name" placeholder="Nama game" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <select name="default_store_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <option value="">Default store</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($games as $game)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('office.games.update', $game) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $game->name }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <span class="py-2 text-xs text-gray-400">{{ $game->slug }}</span>
                    <select name="default_store_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">Default store</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" @selected($game->default_store_id == $store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1 py-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($game->is_active)> Aktif</label>
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Simpan</button>
                </form>
                <form method="POST" action="{{ route('office.games.destroy', $game) }}" class="mt-2" onsubmit="return confirm('Hapus game beserta katalognya?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Hapus game</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
