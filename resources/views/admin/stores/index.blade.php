@extends('admin.layouts.admin')

@section('title', 'Stores')

@section('content')
    <h1 class="text-xl font-semibold">Stores (Tier Ongkir)</h1>

    <form method="POST" action="{{ route('office.stores.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="name" placeholder="Nama store" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="number" step="0.01" name="min_belanja_rmb" placeholder="Min belanja (RMB)" required class="w-40 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <input type="number" step="0.01" name="ongkir_rmb" placeholder="Ongkir (RMB)" required class="w-36 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($stores as $store)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <form method="POST" action="{{ route('office.stores.update', $store) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $store->name }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input type="number" step="0.01" name="min_belanja_rmb" value="{{ $store->min_belanja_rmb }}" class="w-40 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input type="number" step="0.01" name="ongkir_rmb" value="{{ $store->ongkir_rmb }}" class="w-36 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <span class="py-2 text-xs text-gray-400">{{ $store->products_count }} produk · {{ $store->games_count }} game</span>
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Simpan</button>
                </form>
                <form method="POST" action="{{ route('office.stores.destroy', $store) }}" class="mt-2" onsubmit="return confirm('Hapus store? Referensi produk/game jadi NULL.')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Hapus store</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
