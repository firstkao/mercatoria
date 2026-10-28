@extends('admin.layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <h1 class="text-xl font-semibold">{{ $product->exists ? 'Edit: '.$product->name : 'Tambah Produk' }}</h1>

    <form method="POST"
          action="{{ $product->exists ? route('office.products.update', $product) : route('office.products.store') }}"
          class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">SKU <span class="text-red-500">*</span></label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('sku')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Game <span class="text-red-500">*</span></label>
                <select name="game_id" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach ($games as $game)
                        <option value="{{ $game->id }}" @selected(old('game_id', $product->game_id) == $game->id)>{{ $game->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Store (override)</label>
                <select name="store_id" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">— ikut game —</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" @selected(old('store_id', $product->store_id) == $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Kategori Merch</label>
                <select name="merch_category_id" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">— auto —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('merch_category_id', $product->merch_category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Harga RMB <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price_rmb" value="{{ old('price_rmb', $product->price_rmb) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('price_rmb')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Berat (gram)</label>
                <input type="number" name="weight_gram" value="{{ old('weight_gram', $product->weight_gram) }}"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Tipe</label>
                <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="single" @selected(old('type', $product->type ?: 'single') === 'single')>Single</option>
                    <option value="variant" @selected(old('type') === 'variant' || $product->type === 'variant')>Varian</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Ketersediaan</label>
                <select name="availability" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="tersedia" @selected(old('availability', $product->availability ?: 'tersedia') === 'tersedia')>Tersedia</option>
                    <option value="kosong" @selected(old('availability') === 'kosong' || $product->availability === 'kosong')>Kosong</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Diskon (Rp)</label>
                <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', $product->discount_amount) }}"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Periode Diskon</label>
                <div class="mt-1 flex gap-2">
                    <input type="date" name="discount_start" value="{{ old('discount_start', $product->discount_start?->toDateString()) }}"
                           class="w-1/2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input type="date" name="discount_end" value="{{ old('discount_end', $product->discount_end?->toDateString()) }}"
                           class="w-1/2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium">Tag</label>
            <div class="mt-1 flex flex-wrap gap-3">
                @foreach ($tags as $tag)
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                               @checked(in_array($tag->id, old('tags', $product->exists ? $product->tags->pluck('id')->all() : [])))>
                        {{ $tag->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
            Produk aktif
        </label>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">
            {{ $product->exists ? 'SIMPAN PERUBAHAN' : 'SIMPAN PRODUK' }}
        </button>
    </form>

    @if ($product->exists)
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold">Varian</h2>

            <table class="mt-3 w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="py-2">Nama</th>
                        <th class="py-2">SKU</th>
                        <th class="py-2">Harga RMB</th>
                        <th class="py-2">Berat (g)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->variants as $variant)
                        <tr class="border-b border-gray-50">
                            <td class="py-2">{{ $variant->name }}</td>
                            <td class="py-2">{{ $variant->sku }}</td>
                            <td class="py-2">{{ $variant->price_rmb ?? '-' }}</td>
                            <td class="py-2">{{ $variant->weight_gram ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('office.products.variants.bulk-weight', $product) }}" class="mt-4 flex items-end gap-2">
                @csrf
                <div>
                    <label class="block text-xs font-medium">Bulk Edit Berat (Set Same)</label>
                    <input type="number" name="weight_gram" required placeholder="250"
                           class="mt-1 w-28 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <select name="ids[]" multiple size="3" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach ($product->variants as $variant)
                        <option value="{{ $variant->id }}">{{ $variant->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Apply</button>
            </form>

            <form method="POST" action="{{ route('office.products.variants.store', $product) }}" class="mt-4 grid grid-cols-2 gap-2">
                @csrf
                <input type="text" name="name" placeholder="Nama varian" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="text" name="sku" placeholder="SKU" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="number" step="0.01" name="price_rmb" placeholder="Harga RMB" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="number" name="weight_gram" placeholder="Berat (g)" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select name="availability" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="tersedia">Tersedia</option>
                    <option value="kosong">Kosong</option>
                </select>
                <button type="submit" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Varian</button>
            </form>
        </div>

        <form method="POST" action="{{ route('office.products.destroy', $product) }}" class="mt-6"
              onsubmit="return confirm('Hapus permanen produk ini? Order lama tetap utuh dari snapshot.')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-600 hover:border-red-600">
                Hapus Permanen
            </button>
        </form>
    @endif
@endsection
