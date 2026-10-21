@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Store' : 'Tambah Store')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border focus:border-primary-400 focus:ring-primary-400';
@endphp
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('office.stores.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Stores</a>
        <h1 class="mt-2 text-2xl font-bold text-navy-500">
            {{ $isEditing ? 'Edit Store: '.$store->name : 'Tambah Store Baru' }}
        </h1>
    </div>
    @if ($isEditing)
        <x-button type="button" variant="ghost" class="border-danger-500 text-danger-500 hover:bg-danger-50" x-data
                  x-on:click="$dispatch('open-modal', 'delete-store')">Hapus</x-button>
    @endif
</div>

<form method="POST"
      action="{{ $isEditing ? route('office.stores.update', $store) : route('office.stores.store') }}"
      class="mt-5"
      x-data="{
          min: Number(@js((float) old('min_belanja_rmb', $store->min_belanja_rmb ?? 0))),
          shipping: Number(@js((float) old('ongkir_rmb', $store->ongkir_rmb ?? 0))),
          rate: Number(@js($rateRmbIdr)),
          num(value) { return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value) },
          free(price) { return this.min <= 0 || this.shipping <= 0 || price >= this.min },
          cost(price) { return this.free(price) ? 0 : price * this.shipping / this.min },
          rupiah(price) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(this.cost(price) * this.rate)) },
          reason(price) {
              if (this.min <= 0) return 'Karena min belanja 0 → GRATIS ongkir lokal (Rp 0)';
              if (this.shipping <= 0) return 'Karena ongkir 0 → GRATIS ongkir lokal (Rp 0)';
              if (this.free(price)) return 'Karena ' + price + ' ≥ ' + this.num(this.min) + ' → GRATIS ongkir lokal (Rp 0)';
              return 'Karena ' + price + ' < ' + this.num(this.min) + ' → customer bayar ongkir lokal:';
          },
      }">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="space-y-5 lg:col-span-3">
            <section class="rounded-xl border border-border bg-white p-5 md:p-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Informasi Store</h2>
                <hr class="mt-2 border-border">
                <div class="mt-4 space-y-5">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium">Nama Store <span class="text-danger-500">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $store->name) }}" required maxlength="100"
                               class="{{ $inputClass }} {{ $errors->has('name') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                               @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                        @unless ($isEditing)
                            <p class="mt-1 text-xs text-text-muted">ℹ️ Nama toko/tier. Bebas, bisa sama dengan yang lain.</p>
                        @endunless
                        @error('name')<p id="name-error" class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="min_belanja_rmb" class="mb-1 block text-sm font-medium">Minimum Belanja (RMB) <span class="text-danger-500">*</span></label>
                        <input id="min_belanja_rmb" name="min_belanja_rmb" type="number" step="0.01" min="0" required
                               value="{{ old('min_belanja_rmb', $store->min_belanja_rmb ?? 0) }}"
                               x-on:input="min = Number($event.target.value)"
                               class="{{ $inputClass }} {{ $errors->has('min_belanja_rmb') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                               @if ($errors->has('min_belanja_rmb')) aria-invalid="true" aria-describedby="min-belanja-error" @endif>
                        @unless ($isEditing)
                            <p class="mt-1 text-xs text-text-muted">ℹ️ Minimum belanja untuk gratis ongkir lokal CN.<br>Set 0 kalau gak ada minimum (langsung gratis).</p>
                        @endunless
                        @error('min_belanja_rmb')<p id="min-belanja-error" class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="ongkir_rmb" class="mb-1 block text-sm font-medium">Ongkir Lokal CN (RMB) <span class="text-danger-500">*</span></label>
                        <input id="ongkir_rmb" name="ongkir_rmb" type="number" step="0.01" min="0" required
                               value="{{ old('ongkir_rmb', $store->ongkir_rmb ?? 0) }}"
                               x-on:input="shipping = Number($event.target.value)"
                               class="{{ $inputClass }} {{ $errors->has('ongkir_rmb') ? 'border-danger-500 bg-danger-50' : 'border-border' }}"
                               @if ($errors->has('ongkir_rmb')) aria-invalid="true" aria-describedby="ongkir-error" @endif>
                        @unless ($isEditing)
                            <p class="mt-1 text-xs text-text-muted">ℹ️ Biaya ongkir lokal CN dalam RMB.<br>Set 0 kalau gratis selalu.</p>
                        @endunless
                        @error('ongkir_rmb')<p id="ongkir-error" class="mt-1 text-xs text-danger-500" role="alert">⚠️ {{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            @if ($isEditing)
                <section class="rounded-xl border border-border bg-white p-5 md:p-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Penggunaan Store</h2>
                    <hr class="mt-2 border-border">
                    <p class="mt-4 text-sm">Store ini dipakai oleh:</p>
                    <p class="mt-3 text-sm font-semibold">Game: {{ $usage['games'] }}</p>
                    @if ($games->isNotEmpty())
                        <ul class="mt-1 list-inside list-disc text-sm text-text-muted">
                            @foreach ($games as $game)
                                <li>{{ $game->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="mt-3 text-sm font-semibold">Produk: {{ $usage['products'] }}</p>
                    @if ($usage['products'] > 0)
                        <a href="{{ route('office.products.index', ['store_id' => $store->id]) }}"
                           class="mt-2 inline-block rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-navy-500 hover:bg-surface-2">
                            Lihat Semua Produk →
                        </a>
                    @endif
                </section>
            @endif
        </div>

        <section class="h-fit rounded-lg border border-border bg-surface-2 p-4 lg:col-span-2" aria-live="polite">
            <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Preview Perhitungan{{ $isEditing ? ' (Updated)' : '' }}</h2>
            <hr class="mt-2 border-border">
            <div class="mt-3 space-y-4 text-[13px] leading-relaxed text-[#333]">
                @foreach ([58, 150] as $price)
                    <div>
                        <p>Contoh: produk harga {{ $price }} RMB{{ $loop->first ? ' dengan store ini' : '' }}</p>
                        <p class="font-bold text-navy-500" x-text="reason({{ $price }})"></p>
                        <template x-if="! free({{ $price }})">
                            <div>
                                <p>({{ $price }} × <span class="font-bold text-navy-500" x-text="num(shipping)"></span>) / <span class="font-bold text-navy-500" x-text="num(min)"></span>
                                    = <span class="font-bold text-navy-500" x-text="num(cost({{ $price }})) + ' RMB'"></span></p>
                                <p>Rate <span x-text="num(rate)"></span> → <span class="font-bold text-navy-500" x-text="rupiah({{ $price }})"></span></p>
                            </div>
                        </template>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="mt-5 flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.stores.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Store' }}</x-button>
    </div>
</form>

@if ($isEditing)
    @include('admin.stores._delete-modal', ['modalName' => 'delete-store'])
@endif
@endsection
