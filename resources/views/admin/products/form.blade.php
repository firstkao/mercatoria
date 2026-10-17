@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Produk' : 'Tambah Produk')

@php
    $selectedGameIds = array_map('strval', (array) old(
        'game_ids',
        $product->relationLoaded('games') ? $product->games->modelKeys() : [],
    ));
    $gameDefaultStores = $games->mapWithKeys(fn ($game) => [$game->id => [
        'id' => $game->default_store_id,
        'name' => $game->defaultStore?->name,
    ]])->all();
    $previewStores = $stores->mapWithKeys(fn ($store) => [$store->id => [
        'minimum' => (float) $store->min_belanja_rmb,
        'shipping' => (float) $store->ongkir_rmb,
    ]])->all();
    $variantData = $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'name' => $variant->name ?: $variant->sku,
        'sku' => $variant->sku,
        'weight' => $variant->weight_gram,
        'effectiveWeight' => $variant->effectiveWeight(),
    ])->values();
@endphp

@section('content')
<div>
    <a href="{{ route('office.products.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Produk</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $isEditing ? 'Edit Produk: '.$product->name : 'Tambah Produk' }}</h1>
</div>

<form method="POST"
      action="{{ $isEditing ? route('office.products.update', $product) : route('office.products.store') }}"
      class="mt-5 space-y-5"
      x-data="{
          gameDefaults: @js($gameDefaultStores),
          storesById: @js($previewStores),
          selectedGameIds: @js($selectedGameIds),
          storeId: @js(old('store_id', $product->store_id)),
          priceRmb: @js((float) old('price_rmb', $product->price_rmb ?? 0)),
          weightGram: @js((int) old('weight_gram', $product->weight_gram ?? 0)),
          rate: @js((float) setting('rate_rmb_idr', 2200)),
          profitPercent: @js((float) setting('profit_percent', 11)),
          cnBtmPerKg: @js((float) setting('ongkir_cn_btm_per_kg', 25000)),
          btmJktPerKg: @js((float) setting('ongkir_btm_jkt_per_kg', 60000)),
          get defaultGameStore() { return this.selectedGameIds.map(id => this.gameDefaults[id]?.name).find(Boolean) ?? null },
          get effectiveStoreId() { return this.storeId || this.selectedGameIds.map(id => this.gameDefaults[id]?.id).find(Boolean) || null },
          get preview() {
              const store = this.storesById[this.effectiveStoreId] ?? null;
              const local = store && store.minimum > 0 && store.shipping > 0 && this.priceRmb < store.minimum
                  ? (this.priceRmb * store.shipping / store.minimum) * this.rate
                  : 0;
              const cnBtm = this.weightGram / 1000 * this.cnBtmPerKg;
              const btmJkt = this.weightGram / 1000 * this.btmJktPerKg;
              const price = this.priceRmb * this.rate;
              const subtotal = price + local + cnBtm + btmJkt;
              const profit = subtotal * this.profitPercent / 100;
              return { local, cnBtm, btmJkt, price, profit, total: subtotal + profit };
          },
          rupiah(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value) }
      }">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <section class="grid gap-5 rounded-xl border border-border bg-white p-5 md:grid-cols-2 md:p-6">
        <div class="md:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium">Nama Produk *</label>
            <input id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('name')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slug" class="mb-1 block text-sm font-medium">Slug *</label>
            <input id="slug" name="slug" required maxlength="255" value="{{ old('slug', $product->slug) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('slug')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sku" class="mb-1 block text-sm font-medium">SKU *</label>
            <input id="sku" name="sku" required maxlength="64" value="{{ old('sku', $product->sku) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('sku')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="price_rmb" class="mb-1 block text-sm font-medium">Harga (RMB) *</label>
            <input id="price_rmb" name="price_rmb" type="number" min="0" step="0.01" required x-model.number="priceRmb" value="{{ old('price_rmb', $product->price_rmb ?? 0) }}"
                   class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
            @error('price_rmb')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="weight_gram" class="mb-1 block text-sm font-medium">Berat (gram) *</label>
            <div class="flex items-center gap-2">
                <input id="weight_gram" name="weight_gram" type="number" min="1" max="1000000" step="1" required
                       x-model.number="weightGram" value="{{ old('weight_gram', $product->weight_gram) }}"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <span class="text-sm text-text-muted">gram</span>
            </div>
            <p class="mt-1 text-xs text-text-muted">Berat produk default. Varian bisa override.</p>
            @error('weight_gram')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="type" class="mb-1 block text-sm font-medium">Tipe *</label>
            <select id="type" name="type" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="single" @selected(old('type', $product->type ?? 'single') === 'single')>Produk tunggal</option>
                <option value="variant" @selected(old('type', $product->type) === 'variant')>Produk dengan varian</option>
            </select>
            @error('type')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="availability" class="mb-1 block text-sm font-medium">Ketersediaan *</label>
            <select id="availability" name="availability" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="tersedia" @selected(old('availability', $product->availability ?? 'tersedia') === 'tersedia')>Tersedia</option>
                <option value="kosong" @selected(old('availability', $product->availability) === 'kosong')>Kosong</option>
            </select>
            @error('availability')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="game_ids" class="mb-1 block text-sm font-medium">Game *</label>
            <select id="game_ids" name="game_ids[]" multiple required size="5"
                    x-on:change="selectedGameIds = Array.from($event.target.selectedOptions, option => option.value)"
                    class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" @selected(in_array((string) $game->id, $selectedGameIds))>
                        {{ $game->name }}{{ $game->is_active ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">Pilih satu atau lebih game yang menampilkan produk ini.</p>
            @error('game_ids')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            @error('game_ids.*')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="store_id" class="mb-1 block text-sm font-medium">Store</label>
            <select id="store_id" name="store_id" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <option value="">— Pakai default game —</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((string) old('store_id', $product->store_id) === (string) $store->id)>
                        {{ $store->name }} ({{ $store->hasMinBelanja() ? number_format((float) $store->min_belanja_rmb, 2, ',', '.').' RMB' : 'tanpa minimum' }} · {{ $store->isFreeShipping() ? 'Gratis' : number_format((float) $store->ongkir_rmb, 2, ',', '.').' RMB' }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-text-muted">
                <span x-show="defaultGameStore" x-text="'Default game: ' + defaultGameStore"></span>
                <span x-show="!defaultGameStore">Game tidak memiliki default store. Tanpa override, ongkir lokal CN menjadi Rp 0.</span>
            </p>
            @error('store_id')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            <a href="{{ route('office.stores.create') }}" class="mt-2 inline-block text-sm text-navy-500 hover:underline">+ Store Baru</a>
        </div>
        <div class="md:col-span-2">
            <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
            <textarea id="description" name="description" rows="5" class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">{{ old('description', $product->description) }}</textarea>
            @error('description')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
        </div>
        <section class="md:col-span-2 rounded-lg border border-info-200 bg-info-50 p-4" aria-live="polite">
            <h2 class="font-semibold text-navy-500">Preview Perhitungan (khusus admin)</h2>
            <p class="mt-1 text-xs text-text-muted">Preview diperbarui saat harga, berat, atau Store berubah. Customer hanya melihat harga jual.</p>
            <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                <div class="flex justify-between gap-3"><dt>Harga produk</dt><dd x-text="rupiah(preview.price)"></dd></div>
                <div class="flex justify-between gap-3"><dt>Ongkir lokal CN</dt><dd x-text="rupiah(preview.local)"></dd></div>
                <div class="flex justify-between gap-3"><dt>Ongkir CN–BTM</dt><dd x-text="rupiah(preview.cnBtm)"></dd></div>
                <div class="flex justify-between gap-3"><dt>Ongkir BTM–JKT</dt><dd x-text="rupiah(preview.btmJkt)"></dd></div>
                <div class="flex justify-between gap-3"><dt>Profit ({{ rtrim(rtrim(number_format((float) setting('profit_percent', 11), 2, '.', ''), '0'), '.') }}%)</dt><dd x-text="rupiah(preview.profit)"></dd></div>
                <div class="flex justify-between gap-3 border-t border-info-200 pt-2 font-bold"><dt>Total estimasi</dt><dd x-text="rupiah(preview.total)"></dd></div>
            </dl>
        </section>
        <div class="md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->exists ? $product->is_active : true)) class="rounded border-border text-primary-500 focus:ring-primary-400">
                Terbitkan produk di katalog
            </label>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.products.index') }}">Batal</x-button>
        <x-button type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Produk' }}</x-button>
    </div>
</form>

@if ($isEditing)
    <section class="mt-8 rounded-xl border border-border bg-white p-5 md:p-6"
             x-data="{ variants: @js($variantData), selected: [], newWeight: '' }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-500">Varian Produk</h2>
                <p class="mt-1 text-sm text-text-muted">Berat kosong mengikuti berat default produk: {{ $product->weight_gram }}g.</p>
            </div>
        </div>

        @if (session('variant_weight_updated'))
            <div x-data="{ visible: true }" x-show="visible" x-init="setTimeout(() => visible = false, 2000)"
                 class="mt-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700" role="status">
                Berat {{ session('variant_weight_updated') }} varian berhasil diperbarui.
            </div>
        @endif

        <div x-show="selected.length > 0" x-cloak
             class="sticky top-2 z-20 mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-primary-200 bg-white p-3 shadow-md">
            <span class="text-sm font-medium"><span x-text="selected.length"></span> varian dipilih</span>
            <button type="button" x-on:click="$dispatch('open-modal', 'bulk-variant-weight')"
                    class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-semibold text-white hover:bg-navy-600">
                Bulk Edit Berat
            </button>
        </div>

        @if ($product->variants->isNotEmpty())
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-surface-2 text-text-muted">
                        <tr>
                            <th class="w-12 px-3 py-2"><span class="sr-only">Pilih</span></th>
                            <th class="px-3 py-2">Varian</th>
                            <th class="px-3 py-2">SKU</th>
                            <th class="px-3 py-2">Harga</th>
                            <th class="px-3 py-2">Berat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($product->variants as $variant)
                            <tr>
                                <td class="px-3 py-3">
                                    <input type="checkbox" value="{{ $variant->id }}" x-model.number="selected"
                                           aria-label="Pilih {{ $variant->name ?: $variant->sku }}"
                                           class="rounded border-border text-primary-500 focus:ring-primary-400">
                                </td>
                                <td class="px-3 py-3 font-medium">{{ $variant->name ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $variant->sku }}</td>
                                <td class="px-3 py-3">{{ number_format((float) $variant->price, 2, ',', '.') }}</td>
                                <td class="px-3 py-3" x-data="{ editing: false, weight: @js($variant->weight_gram) }">
                                    <button type="button" x-show="!editing" x-on:click="editing = true; $nextTick(() => $refs.weight.focus())"
                                            class="font-medium hover:text-navy-500">
                                        @if ($variant->weight_gram)
                                            {{ $variant->weight_gram }}g
                                        @else
                                            <span class="italic text-text-light">—</span>
                                        @endif
                                    </button>
                                    <form method="POST" action="{{ route('office.products.variants.weight.update', [$product, $variant]) }}"
                                          x-show="editing" x-cloak x-on:submit="$el.querySelector('button[type=submit]').disabled = true">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex items-center gap-2">
                                            <input x-ref="weight" x-model="weight" name="weight_gram" type="number" min="1" max="1000000"
                                                   placeholder="Default produk" x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                                                   class="w-36 rounded border-border text-sm focus:border-primary-400 focus:ring-primary-400">
                                            <button type="submit" class="text-sm font-semibold text-navy-500 hover:underline">Simpan</button>
                                        </div>
                                    </form>
                                    @if ($variant->weight_gram === null)
                                        <span class="ml-1 text-xs text-text-light">(pakai {{ $product->weight_gram }}g)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-4 rounded-lg border border-dashed border-border p-5 text-center text-sm text-text-muted">Belum ada varian untuk produk ini.</p>
        @endif

        <form method="POST" action="{{ route('office.products.variants.store', $product) }}"
              class="mt-6 grid gap-3 border-t border-border pt-5 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <label class="text-sm">SKU
                <input name="sku" required maxlength="64" class="mt-1 w-full rounded-lg border-border text-sm" value="{{ old('sku') }}">
            </label>
            <label class="text-sm">Nama varian
                <input name="name" maxlength="255" class="mt-1 w-full rounded-lg border-border text-sm" value="{{ old('name') }}">
            </label>
            <label class="text-sm">Harga
                <input name="price" type="number" min="0" step="0.01" required class="mt-1 w-full rounded-lg border-border text-sm" value="{{ old('price', 0) }}">
            </label>
            <label class="text-sm">Berat (gram)
                <input name="weight_gram" type="number" min="1" max="1000000" class="mt-1 w-full rounded-lg border-border text-sm" value="{{ old('weight_gram') }}">
            </label>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-surface-2">Tambah Varian</button>
            </div>
        </form>

        <div x-show="false" x-cloak></div>
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail === 'bulk-variant-weight') open = true"
             x-on:close-modal.window="if ($event.detail === 'bulk-variant-weight') open = false"
             x-on:keydown.escape.window="open = false"
             x-show="open" x-cloak x-on:click.self="open = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6" role="dialog" aria-modal="true" aria-labelledby="bulk-weight-title">
                <h3 id="bulk-weight-title" class="text-lg font-bold">Bulk Edit Berat — Set Same</h3>
                <p class="mt-1 text-sm text-text-muted">Berat baru akan diterapkan ke semua varian terpilih.</p>
                <ul class="mt-4 max-h-40 space-y-2 overflow-y-auto text-sm">
                    <template x-for="variant in variants.filter(item => selected.includes(item.id))" :key="variant.id">
                        <li class="flex justify-between gap-4">
                            <span x-text="variant.name + ' (' + variant.sku + ')'"></span>
                            <span><span x-text="(variant.weight ?? '—') + (variant.weight ? 'g' : '')"></span> → <strong x-text="(newWeight || '—') + (newWeight ? 'g' : '')"></strong></span>
                        </li>
                    </template>
                </ul>
                <form method="POST" action="{{ route('office.products.variants.weight.bulk', $product) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="selected_variant_ids[]" x-bind:value="id">
                    </template>
                    <label for="bulk-weight" class="block text-sm font-medium">Berat baru (gram)</label>
                    <input id="bulk-weight" name="weight_gram" type="number" min="1" max="1000000" required x-model="newWeight"
                           class="mt-1 w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                    @error('weight_gram')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" x-on:click="open = false" class="rounded-lg px-4 py-2 text-sm text-text-muted hover:bg-surface-2">Batal</button>
                        <button type="submit" class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-semibold text-white hover:bg-navy-600">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endif
@endsection
