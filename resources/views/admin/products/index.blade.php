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
<div x-data="{
        single: { url: '', name: '', orders: 0 },
        bulk: { count: 0, ordered: 0 },
        openBulk(event) {
            event.preventDefault();
            const selected = [...document.querySelectorAll('[form=bulk-delete-products][name=\'product_ids[]\']:checked')];
            if (!selected.length) { alert('Pilih minimal satu produk.'); return; }
            this.bulk = { count: selected.length, ordered: selected.filter(i => Number(i.dataset.orderCount) > 0).length };
            this.$dispatch('open-modal', 'hard-delete-bulk');
        }
    }">
    <form id="bulk-delete-products" method="POST" x-ref="bulkForm" action="{{ route('office.products.bulk-destroy') }}" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
        @csrf
        @method('DELETE')
        <p class="text-sm text-red-900">Penghapusan bersifat permanen. Snapshot order terdahulu akan tetap tersimpan.</p>
        <button type="submit" x-on:click="openBulk($event)" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Hapus produk terpilih</button>
    </form>
    <div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Pilih</span></th>
                    <th scope="col" class="px-4 py-3">Produk</th>
                    <th scope="col" class="px-4 py-3">Game</th>
                    <th scope="col" class="px-4 py-3">Store</th>
                    <th scope="col" class="px-4 py-3">Harga RMB</th>
                    <th scope="col" class="px-4 py-3">Berat</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Order lama</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($products as $product)
                    <tr class="hover:bg-surface-2">
                        <td class="px-4 py-4">
                            <input form="bulk-delete-products" type="checkbox" name="product_ids[]" value="{{ $product->id }}" data-product-name="{{ $product->name }}" data-order-count="{{ $product->order_items_count }}" aria-label="Pilih {{ $product->name }}" class="rounded border-border text-primary-600">
                        </td>
                        <th scope="row" class="px-4 py-4">
                            <span class="block font-semibold">{{ $product->name }}</span>
                            <span class="text-xs text-text-muted">{{ $product->sku }}</span>
                        </th>
                        <td class="px-4 py-4">{{ $product->games->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-4">{{ $product->store?->name ?? 'Default dari game' }}</td>
                        <td class="px-4 py-4">{{ number_format((float) $product->price_rmb, 2, ',', '.') }}</td>
                        <td class="px-4 py-4">{{ $product->weight_gram ? $product->weight_gram.'g' : '—' }}</td>
                        <td class="px-4 py-4">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-4">{{ $product->order_items_count }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('office.products.edit', $product) }}" class="font-medium text-navy-500 hover:underline">Edit</a>
                                <button type="button" class="font-medium text-red-700 hover:underline"
                                        x-on:click="single = { url: @js(route('office.products.destroy', $product)), name: @js(($product->games->first() ? '['.$product->games->first()->name.'] ' : '').$product->name), orders: {{ $product->order_items_count }} }; $dispatch('open-modal', 'hard-delete-single')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $products->links() }}</div>

    <x-modal name="hard-delete-single" maxWidth="max-w-md">
        <form method="POST" :action="single.url">
            @csrf
            @method('DELETE')
            <h2 class="text-lg font-bold text-danger-500">⚠️ Hapus Produk Permanen? (HARD DELETE)</h2>
            <p class="mt-3 text-sm">Produk: <strong x-text="single.name"></strong></p>
            <div class="mt-3 rounded-lg bg-warning-50 p-3 text-sm text-warning-700">
                <p class="font-semibold">Produk ini pernah di-order <span x-text="single.orders"></span> kali.</p>
                <ul class="mt-2 list-disc pl-5">
                    <li>Produk, varian, dan foto dihapus permanen dan tidak bisa dikembalikan.</li>
                    <li>Order lama tetap aman karena tersimpan sebagai snapshot.</li>
                    <li>Produk Asli di detail order ditandai "Produk Asli Telah Dihapus".</li>
                </ul>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <x-button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'hard-delete-single')">Batal</x-button>
                <x-button variant="danger" type="submit">Hapus Permanen</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="hard-delete-bulk" maxWidth="max-w-md">
        <h2 class="text-lg font-bold text-danger-500">Hapus <span x-text="bulk.count"></span> Produk Terpilih Secara Permanen?</h2>
        <p class="mt-3 text-sm"><span x-text="bulk.ordered"></span> di antaranya pernah di-order. Order lama tetap aman sebagai snapshot; produk, varian, dan foto tidak bisa dikembalikan.</p>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'hard-delete-bulk')">Batal</x-button>
            <x-button variant="danger" type="button" x-on:click="$refs.bulkForm.submit()">Hapus Semua Permanen</x-button>
        </div>
    </x-modal>
</div>
@endif
@endsection
