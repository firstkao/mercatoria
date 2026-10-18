@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Store' : 'Tambah Store')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('office.stores.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Stores</a>
        <h1 class="mt-2 text-2xl font-bold text-navy-500">
            {{ $isEditing ? 'Edit Store: '.$store->name : 'Tambah Store Baru' }}
        </h1>
    </div>
    @if ($isEditing)
        <x-button type="button" variant="danger" x-data x-on:click="$dispatch('open-modal', 'delete-store')">Hapus Store</x-button>
    @endif
</div>

<form method="POST"
      action="{{ $isEditing ? route('office.stores.update', $store) : route('office.stores.store') }}"
      class="mt-5 space-y-5"
      x-data="{ min: Number(@js((float) old('min_belanja_rmb', $store->min_belanja_rmb ?? 0))), shipping: Number(@js((float) old('ongkir_rmb', $store->ongkir_rmb ?? 0))), rate: Number(@js($rateRmbIdr)), cost(price) { return this.min <= 0 || this.shipping <= 0 || price >= this.min ? 0 : price * this.shipping / this.min }, rupiah(price) { return new Intl.NumberFormat('id-ID').format(Math.round(price * this.rate)) } }">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Informasi Store</h2>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="mb-1 block text-sm font-medium">Nama Store <span class="text-danger-500">*</span></label>
                <input id="name" name="name" value="{{ old('name', $store->name) }}" required maxlength="100"
                       class="w-full rounded-lg border focus:border-primary-400 focus:ring-primary-400 {{ $errors->has('name') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                       @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                <p class="mt-1 text-xs text-text-muted">Nama toko atau tier harus unik.</p>
                @error('name')<p id="name-error" class="mt-1 text-sm text-danger-500" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="min_belanja_rmb" class="mb-1 block text-sm font-medium">Minimum Belanja (RMB) <span class="text-danger-500">*</span></label>
                <input id="min_belanja_rmb" name="min_belanja_rmb" type="number" step="0.01" min="0" required
                       value="{{ old('min_belanja_rmb', $store->min_belanja_rmb ?? 0) }}"
                       x-on:input="min = Number($event.target.value)"
                       class="w-full rounded-lg border focus:border-primary-400 focus:ring-primary-400 {{ $errors->has('min_belanja_rmb') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                       @if ($errors->has('min_belanja_rmb')) aria-invalid="true" aria-describedby="min-belanja-error" @endif>
                <p class="mt-1 text-xs text-text-muted">Minimum untuk gratis ongkir lokal CN. Isi 0 bila tidak ada minimum.</p>
                @error('min_belanja_rmb')<p id="min-belanja-error" class="mt-1 text-sm text-danger-500" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="ongkir_rmb" class="mb-1 block text-sm font-medium">Ongkir Lokal CN (RMB) <span class="text-danger-500">*</span></label>
                <input id="ongkir_rmb" name="ongkir_rmb" type="number" step="0.01" min="0" required
                       value="{{ old('ongkir_rmb', $store->ongkir_rmb ?? 0) }}"
                       x-on:input="shipping = Number($event.target.value)"
                       class="w-full rounded-lg border focus:border-primary-400 focus:ring-primary-400 {{ $errors->has('ongkir_rmb') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                       @if ($errors->has('ongkir_rmb')) aria-invalid="true" aria-describedby="ongkir-error" @endif>
                <p class="mt-1 text-xs text-text-muted">Isi 0 bila ongkir lokal selalu gratis.</p>
                @error('ongkir_rmb')<p id="ongkir-error" class="mt-1 text-sm text-danger-500" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    @if ($isEditing)
        <section class="rounded-xl border border-border bg-white p-5 md:p-6">
            <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Penggunaan Store</h2>
            <p class="mt-1 text-sm text-text-muted">Store ini dipakai oleh {{ $usage['games'] }} game dan {{ $usage['products'] }} produk.</p>
            @if ($games->isNotEmpty())
                <ul class="mt-3 list-inside list-disc text-sm text-text-muted">
                    @foreach ($games as $game)
                        <li>{{ $game->name }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($usage['products'] > 0)
                <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}" class="mt-3 inline-block text-sm font-medium text-navy-500 hover:underline">
                    Lihat {{ $usage['products'] }} produk yang menggunakan store →
                </a>
            @endif
            <p class="mt-3 rounded-lg bg-warning-50 p-3 text-sm text-warning-700">
                Jika store dihapus, relasi default store pada game dan store override pada produk akan menjadi kosong.
            </p>
        </section>
    @endif

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Preview Perhitungan</h2>
        <p class="mt-1 text-sm text-text-muted">Contoh ongkir lokal untuk harga produk 58 RMB dan 150 RMB.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ([58, 150] as $price)
                <div class="rounded-lg bg-surface-2 p-4">
                    <p class="text-sm font-medium">Produk {{ $price }} RMB</p>
                    <p class="mt-2 text-sm text-text-muted">
                        Ongkir:
                        <span x-text="cost({{ $price }}) === 0 ? 'Gratis' : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(cost({{ $price }})) + ' RMB'"></span>
                    </p>
                    <p class="text-sm text-text-muted">
                        <span x-text="cost({{ $price }}) === 0 ? 'Rp 0' : 'Rp ' + rupiah(cost({{ $price }}))"></span>
                    </p>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-text-muted">Rumus jika harga di bawah minimum: (harga × ongkir) / minimum × rate RMB/IDR.</p>
    </section>

    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.stores.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Store' }}</x-button>
    </div>
</form>

@if ($isEditing)
    <x-modal name="delete-store" max-width="max-w-lg">
        <h2 class="text-xl font-bold text-danger-500">Hapus Store?</h2>
        <p class="mt-2 text-sm text-text-muted">Store <strong>{{ $store->name }}</strong> akan dihapus permanen.</p>
        @if ($usage['games'] > 0 || $usage['products'] > 0)
            <div class="mt-4 rounded-lg border border-warning-100 bg-warning-50 p-4 text-sm text-warning-700">
                Store ini sedang dipakai oleh:
                <ul class="mt-2 list-inside list-disc">
                    <li>{{ $usage['games'] }} game</li>
                    <li>{{ $usage['products'] }} produk</li>
                </ul>
                <p class="mt-2">Jika dihapus, foreign key store pada game dan produk akan menjadi NULL. Data game dan produk tetap ada, dan order lama tetap utuh dari snapshot.</p>
            </div>
        @else
            <p class="mt-4 rounded-lg bg-surface-2 p-4 text-sm text-text-muted">Store ini belum dipakai oleh game atau produk.</p>
        @endif
        <div class="mt-6 flex justify-end gap-3">
            <x-button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'delete-store')">Batal</x-button>
            <form method="POST" action="{{ route('office.stores.destroy', $store) }}">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Hapus Permanen</x-button>
            </form>
        </div>
    </x-modal>
@endif
@endsection
