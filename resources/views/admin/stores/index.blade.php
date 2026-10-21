@extends('layouts.admin')

@section('title', 'Stores / Tier Ongkir')

@section('content')
@php
    $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
@endphp
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-[22pt] font-bold leading-tight text-navy-500">Stores / Tier Ongkir</h1>
    </div>
    <x-button href="{{ route('office.stores.create') }}">+ Tambah Store</x-button>
</div>

<div class="mt-5 border-l-4 border-info-500 bg-info-50 p-4 text-sm text-info-700">
    <p><span aria-hidden="true">ℹ️ </span>Store menentukan kebijakan ongkir lokal CN per produk.</p>
    <p class="mt-1 pl-6">Produk default ambil store dari game, bisa override.</p>
</div>

<form method="GET" action="{{ route('office.stores.index') }}" class="mt-5 flex w-full max-w-[400px] gap-2">
    <label for="store-search" class="sr-only">Cari store</label>
    <input id="store-search" name="q" value="{{ $search }}" type="search" placeholder="Cari store..."
           class="h-10 min-w-0 flex-1 rounded-lg border-border text-sm focus:border-primary-400 focus:ring-primary-400">
    <x-button type="submit" variant="ghost" class="h-10" aria-label="Cari">🔍</x-button>
</form>

@if ($stores->isEmpty())
    <div class="mt-6 rounded-xl border border-dashed border-border bg-white px-6 py-12 text-center">
        <p class="text-4xl" aria-hidden="true">🏪</p>
        <h2 class="mt-3 text-lg font-semibold">Belum ada store</h2>
        <p class="mt-1 text-sm text-text-muted">Tambahkan store untuk mengatur tier ongkir lokal CN.</p>
        <x-button href="{{ route('office.stores.create') }}" class="mt-5">Tambah Store</x-button>
    </div>
@else
    <div class="mt-5 hidden overflow-hidden rounded-xl border border-border bg-white md:block">
        <table class="w-full text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr>
                    <th scope="col" class="w-10 px-4 py-3">
                        <input type="checkbox" form="bulk-store-form" aria-label="Pilih semua store"
                               x-on:change="document.querySelectorAll('.store-check').forEach(el => el.checked = $event.target.checked)"
                               class="rounded border-border">
                    </th>
                    <th scope="col" class="px-4 py-3">Nama Store</th>
                    <th scope="col" class="px-4 py-3">Min Belanja</th>
                    <th scope="col" class="px-4 py-3">Ongkir</th>
                    <th scope="col" class="px-4 py-3 text-center">Produk</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($stores as $store)
                    <tr class="h-[60px] hover:bg-surface-2">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $store->id }}" form="bulk-store-form"
                                   aria-label="Pilih store {{ $store->name }}" class="store-check rounded border-border">
                        </td>
                        <th scope="row" class="px-4 py-3 text-sm font-bold text-text"><span aria-hidden="true">🏪 </span>{{ $store->name }}</th>
                        <td class="px-4 py-3 text-[13px]">
                            @if ($store->hasMinBelanja())
                                {{ $fmt($store->min_belanja_rmb) }} RMB
                            @else
                                <span class="text-text-light">— (gak ada min)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[13px]">
                            @if ($store->isFreeShipping())
                                <span class="rounded-full bg-success-500 px-2 py-1 text-xs font-semibold text-white">Gratis</span>
                            @else
                                {{ $fmt($store->ongkir_rmb) }} RMB
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-[13px]">
                            @if ($store->products_count > 0)
                                <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}" class="font-bold text-navy-500 hover:underline">
                                    {{ $store->products_count }}
                                </a>
                            @else
                                <span class="text-text-light">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <a href="{{ route('office.stores.edit', $store) }}" class="font-medium text-navy-500 hover:underline">Edit</a>
                                <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}" class="font-medium text-navy-500 hover:underline">Lihat Produk</a>
                                <button type="button" x-data
                                        x-on:click="$dispatch('open-modal', 'delete-store-{{ $store->id }}')"
                                        class="font-medium text-danger-500 hover:underline">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 grid gap-3 md:hidden">
        @foreach ($stores as $store)
            <article class="rounded-xl border border-border bg-white p-4">
                <h2 class="font-bold"><span aria-hidden="true">🏪 </span>{{ $store->name }}</h2>
                <dl class="mt-3 space-y-1 text-sm">
                    <div class="flex gap-1">
                        <dt class="text-text-muted">Min:</dt>
                        <dd class="{{ $store->hasMinBelanja() ? '' : 'text-text-light' }}">
                            {{ $store->hasMinBelanja() ? $fmt($store->min_belanja_rmb).' RMB' : '— (gak ada)' }}
                        </dd>
                    </div>
                    <div class="flex gap-1">
                        <dt class="text-text-muted">Ongkir:</dt>
                        <dd>
                            @if ($store->isFreeShipping())
                                <span class="rounded-full bg-success-500 px-2 py-0.5 text-xs font-semibold text-white">Gratis</span>
                            @else
                                {{ $fmt($store->ongkir_rmb) }} RMB
                            @endif
                        </dd>
                    </div>
                    <div class="flex gap-1">
                        <dt class="text-text-muted">Produk:</dt>
                        <dd>
                            @if ($store->products_count > 0)
                                <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}"
                                   class="font-bold text-navy-500 hover:underline">{{ $store->products_count }}</a>
                            @else
                                <span class="text-text-light">0</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                    <a href="{{ route('office.stores.edit', $store) }}" class="font-medium text-navy-500 hover:underline">Edit</a>
                    <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}" class="font-medium text-navy-500 hover:underline">Lihat</a>
                    <button type="button" x-data
                            x-on:click="$dispatch('open-modal', 'delete-store-{{ $store->id }}')"
                            class="font-medium text-danger-500 hover:underline">
                        Hapus
                    </button>
                </div>
            </article>
        @endforeach
    </div>

    <form id="bulk-store-form" method="POST" action="{{ route('office.stores.bulk-destroy') }}"
          class="mt-5 hidden items-center gap-2 text-sm md:flex"
          x-data x-on:submit="if ($refs.action.value !== 'delete' || ! confirm('Hapus store terpilih? Game dan produk yang memakainya akan kembali ke default.')) { $event.preventDefault() }">
        @csrf
        @method('DELETE')
        <label for="bulk-action" class="font-medium">Bulk Action:</label>
        <select id="bulk-action" x-ref="action" class="rounded-lg border-border text-sm">
            <option value="">Pilih aksi</option>
            <option value="delete">Hapus terpilih</option>
        </select>
        <x-button type="submit" variant="ghost">Terapkan</x-button>
    </form>

    @foreach ($stores as $store)
        @include('admin.stores._delete-modal', [
            'store' => $store,
            'modalName' => 'delete-store-'.$store->id,
            'usage' => ['games' => $store->games_count, 'products' => $store->products_count],
        ])
    @endforeach

    <p class="mt-5 text-sm text-text-muted">Total: {{ number_format($stores->total()) }} store</p>
    <div class="mt-5">{{ $stores->links() }}</div>
@endif
@endsection
