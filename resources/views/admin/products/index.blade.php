@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Produk</h1>
        <p class="mt-1 text-sm text-text-muted">Kelola produk, game, dan store ongkir.</p>
    </div>
    <x-button href="{{ route('office.products.create') }}">+ Tambah Produk</x-button>
</div>

<form method="GET" action="{{ route('office.products.index') }}" class="mt-5 grid gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-[1fr_1fr_auto]">
    <label class="sr-only" for="product-search">Cari nama atau SKU</label>
    <input id="product-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama atau SKU..."
           class="rounded-lg border-border text-sm focus:border-primary-400 focus:ring-primary-400">
    <label class="sr-only" for="store-filter">Filter store</label>
    <select id="store-filter" name="store_id" class="rounded-lg border-border text-sm">
        <option value="">Semua store</option>
        @foreach ($stores as $store)
            <option value="{{ $store->id }}" @selected((string) ($filters['store_id'] ?? '') === (string) $store->id)>{{ $store->name }}</option>
        @endforeach
    </select>
    <x-button type="submit" variant="ghost">Cari / Filter</x-button>
</form>

@if ($products->isEmpty())
    <div class="mt-5 rounded-xl border border-dashed border-border bg-white px-6 py-12 text-center">
        <h2 class="text-lg font-semibold">Belum ada produk</h2>
        <x-button href="{{ route('office.products.create') }}" class="mt-4">Tambah Produk</x-button>
    </div>
@else
    <div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr>
                    <th scope="col" class="px-4 py-3">Produk</th>
                    <th scope="col" class="px-4 py-3">Game</th>
                    <th scope="col" class="px-4 py-3">Store</th>
                    <th scope="col" class="px-4 py-3">Harga RMB</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($products as $product)
                    <tr class="hover:bg-surface-2">
                        <th scope="row" class="px-4 py-4">
                            <span class="block font-semibold">{{ $product->name }}</span>
                            <span class="text-xs text-text-muted">{{ $product->sku }}</span>
                        </th>
                        <td class="px-4 py-4">{{ $product->games->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-4">{{ $product->store?->name ?? 'Default dari game' }}</td>
                        <td class="px-4 py-4">{{ number_format((float) $product->price_rmb, 2, ',', '.') }}</td>
                        <td class="px-4 py-4">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-4"><a href="{{ route('office.products.edit', $product) }}" class="font-medium text-navy-500 hover:underline">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $products->links() }}</div>
@endif
@endsection
