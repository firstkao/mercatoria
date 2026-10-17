@extends('layouts.admin')

@section('title', 'Stores / Tier Ongkir')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Stores / Tier Ongkir</h1>
        <p class="mt-1 text-sm text-text-muted">Atur minimum belanja dan ongkir lokal CN per toko.</p>
    </div>
    <x-button href="{{ route('office.stores.create') }}">+ Tambah Store</x-button>
</div>

<div class="mt-5 border-l-4 border-info-500 bg-info-50 p-4 text-sm text-info-700">
    Store menentukan perhitungan ongkir lokal CN dan dapat dipakai sebagai default game atau override di produk.
</div>

<form method="GET" action="{{ route('office.stores.index') }}" class="mt-5 flex max-w-xl gap-2">
    <label for="store-search" class="sr-only">Cari store</label>
    <input id="store-search" name="q" value="{{ $search }}" type="search" placeholder="Cari store..."
           class="min-w-0 flex-1 rounded-lg border-border text-sm focus:border-primary-400 focus:ring-primary-400">
    <x-button type="submit" variant="ghost">Cari</x-button>
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
                    <th scope="col" class="px-4 py-3">Nama</th>
                    <th scope="col" class="px-4 py-3">Min Belanja</th>
                    <th scope="col" class="px-4 py-3">Ongkir</th>
                    <th scope="col" class="px-4 py-3">Jumlah Produk</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($stores as $store)
                    <tr class="hover:bg-surface-2">
                        <th scope="row" class="px-4 py-4 font-semibold">{{ $store->name }}</th>
                        <td class="px-4 py-4">
                            @if ($store->hasMinBelanja())
                                {{ number_format((float) $store->min_belanja_rmb, 2, ',', '.') }} RMB
                            @else
                                <span class="text-text-light">— (gak ada min)</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if ($store->isFreeShipping())
                                <span class="rounded-full bg-success-100 px-2 py-1 text-xs font-semibold text-success-700">Gratis</span>
                            @else
                                {{ number_format((float) $store->ongkir_rmb, 2, ',', '.') }} RMB
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}" class="font-semibold text-navy-500 hover:underline">
                                {{ $store->products_count }}
                            </a>
                            <span class="text-text-light">produk · {{ $store->games_count }} game</span>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('office.stores.edit', $store) }}" class="font-medium text-navy-500 hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 grid gap-3 md:hidden">
        @foreach ($stores as $store)
            <article class="rounded-xl border border-border bg-white p-4">
                <h2 class="font-semibold">{{ $store->name }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-text-muted">Min Belanja</dt>
                        <dd class="{{ $store->hasMinBelanja() ? '' : 'text-text-light' }}">
                            {{ $store->hasMinBelanja() ? number_format((float) $store->min_belanja_rmb, 2, ',', '.').' RMB' : '— (gak ada min)' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Ongkir</dt>
                        <dd>
                            @if ($store->isFreeShipping())
                                <span class="rounded-full bg-success-100 px-2 py-1 text-xs font-semibold text-success-700">Gratis</span>
                            @else
                                {{ number_format((float) $store->ongkir_rmb, 2, ',', '.') }} RMB
                            @endif
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-text-muted">Produk / game</dt>
                        <dd>{{ $store->products_count }} produk · {{ $store->games_count }} game</dd>
                    </div>
                </dl>
                <a href="{{ route('office.stores.edit', $store) }}" class="mt-4 inline-block font-medium text-navy-500 hover:underline">Edit store</a>
            </article>
        @endforeach
    </div>

    <div class="mt-5">{{ $stores->links() }}</div>
@endif
@endsection
