@extends('admin.layouts.admin')

@section('title', 'Semua Produk')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Semua Produk</h1>
        <a href="{{ route('office.products.create') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah Produk</a>
    </div>

    <form method="POST" action="{{ route('office.products.bulk') }}" class="mt-4">
        @csrf
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="w-10 px-3 py-2"></th>
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2">SKU</th>
                        <th class="px-3 py-2">Game</th>
                        <th class="px-3 py-2">Berat</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-b border-gray-50">
                            <td class="px-3 py-2"><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="rounded border-gray-300"></td>
                            <td class="px-3 py-2 font-medium">{{ $product->name }}</td>
                            <td class="px-3 py-2">{{ $product->sku }}</td>
                            <td class="px-3 py-2">{{ $product->game->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $product->weight_gram !== null ? number_format($product->weight_gram).' g' : '-' }}</td>
                            <td class="px-3 py-2">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('office.products.edit', $product) }}" class="text-sky-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <select name="action" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <option value="aktifkan">Aktifkan</option>
                <option value="nonaktifkan">Nonaktifkan</option>
                <option value="hapus">Hapus Permanen</option>
            </select>
            <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Terapkan</button>
        </div>
    </form>

    <div class="mt-4">{{ $products->links() }}</div>
@endsection
