@extends('layouts.app')

@section('title', $product->name.' — Mercatoria')

@section('content')
    <nav class="mb-4 text-sm text-gray-500">
        <a href="/" class="hover:text-black">Beranda</a>
        <span class="mx-1">/</span>
        <a href="/{{ $game->slug }}" class="hover:text-black">{{ $game->name }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                @if (($variant?->image_path) ?? $product->image_path)
                    <img src="{{ Storage::url($variant?->image_path ?? $product->image_path) }}"
                         alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div class="flex aspect-square w-full items-center justify-center text-sm text-gray-400">
                        Tanpa Foto
                    </div>
                @endif
            </div>

            @if ($product->description_image_path)
                <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <img src="{{ Storage::url($product->description_image_path) }}" alt="Deskripsi {{ $product->name }}" class="w-full">
                </div>
            @endif
        </div>

        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">[{{ $game->name }}]</p>
            <h1 class="mt-1 text-2xl font-semibold">{{ $product->name }}</h1>

            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($product->tags as $tag)
                    <span class="rounded bg-red-600 px-2 py-0.5 text-[11px] font-semibold text-white">{{ $tag->name }}</span>
                @endforeach
                <span class="rounded px-2 py-0.5 text-[11px] font-semibold {{ $product->availability === 'tersedia' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                    {{ $product->availability === 'tersedia' ? 'Tersedia' : 'Kosong' }}
                </span>
            </div>

            <div class="mt-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                @if ($product->hasActiveDiscount())
                    <p class="text-sm text-gray-400 line-through">{{ format_rp($pricing['base_price_bersih']) }}</p>
                @endif
                <p class="text-3xl font-semibold text-sky-600">{{ format_rp($pricing['final_price_bersih']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
            </div>

            @if ($product->variants->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-semibold">Pilih Varian</h2>
                    <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                        @foreach ($product->variants as $v)
                            <a href="/{{ $game->slug }}/{{ $product->slug }}?variant={{ $v->id }}"
                               class="rounded-lg border px-3 py-2 text-center text-sm transition
                                      {{ ($variant?->id ?? null) === $v->id
                                          ? 'border-black bg-black text-white'
                                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400' }}">
                                {{ $v->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <dl class="mt-6 space-y-1 text-sm text-gray-600">
                <div class="flex justify-between border-b border-gray-100 py-1">
                    <dt>SKU</dt>
                    <dd>{{ $variant->sku ?? $product->sku }}</dd>
                </div>
                @if ($pricing['store_name'])
                    <div class="flex justify-between border-b border-gray-100 py-1">
                        <dt>Store</dt>
                        <dd>{{ $pricing['store_name'] }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-b border-gray-100 py-1">
                    <dt>Berat</dt>
                    <dd>{{ number_format($pricing['weight_gram']) }} gram</dd>
                </div>
            </dl>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-lg font-semibold">Produk Terkait</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                @foreach ($related as $relatedProduct)
                    <x-product-card :product="$relatedProduct" :shipping="$shipping" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
