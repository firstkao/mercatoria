@extends('layouts.app')

@section('title', $product->name.' — Mercatoria')

@section('content')
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[48%_52%]">
        <div>
            <div class="overflow-hidden rounded-xl border border-rule bg-white">
                @if (($variant?->image_path) ?? $product->image_path)
                    <img src="{{ Storage::url($variant?->image_path ?? $product->image_path) }}"
                         alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div class="flex aspect-square w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">
                        Tanpa Foto
                    </div>
                @endif
            </div>

            @if ($product->description_image_path)
                <div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
                    <img src="{{ Storage::url($product->description_image_path) }}" alt="Deskripsi {{ $product->name }}" class="w-full">
                </div>
            @endif
        </div>

        <div>
            <nav class="flex flex-wrap items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">
                <a href="/" class="transition-colors hover:text-ink">Beranda</a>
                <span class="text-ink/35">/</span>
                <a href="/{{ $game->slug }}" class="transition-colors hover:text-ink">{{ $game->name }}</a>
                <span class="text-ink/35">/</span>
                <span class="text-ink">{{ $product->name }}</span>
            </nav>

            <p class="mt-3 font-mono text-[10px] uppercase tracking-[0.16em] text-ink/50">[{{ $game->name }}]</p>
            <h1 class="mt-1 font-display text-2xl font-semibold text-ink">{{ $product->name }}</h1>

            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($product->tags as $tag)
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase text-white {{ $tag->slug === 'presale' ? 'bg-[#FFC107] text-[#000000]' : ($tag->slug === 'limited' ? 'bg-[#E53935]' : ($tag->slug === 'new' ? 'bg-[#4FC3F7]' : 'bg-[#E53935]')) }}">{{ $tag->name }}</span>
                @endforeach
                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $product->availability === 'tersedia' ? 'bg-[#43A047] text-white' : 'bg-[#9E9E9E] text-white' }}">
                    {{ $product->availability === 'tersedia' ? 'Tersedia' : 'Kosong' }}
                </span>
            </div>

            <div class="mt-4 rounded-xl border border-rule bg-white p-4">
                @if ($product->hasActiveDiscount())
                    <p class="text-sm text-ink/45 line-through">{{ format_rp($pricing['base_price_bersih']) }}</p>
                @endif
                <p class="text-3xl font-semibold text-[#29B6F6]">{{ format_rp($pricing['final_price_bersih']) }}</p>
                <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
            </div>

            @if ($product->is_split_box && $product->splitBox)
                <livewire:split-box-picker :product="$product" />
            @elseif ($product->variants->isNotEmpty())
                <div class="mt-6">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pilih Varian</h2>
                    <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                        @foreach ($product->variants as $v)
                            <a href="/{{ $game->slug }}/{{ $product->slug }}?variant={{ $v->id }}"
                               class="rounded-lg border px-3 py-2 text-center text-sm transition-colors
                                      {{ ($variant?->id ?? null) === $v->id
                                          ? 'border-ink bg-ink text-white'
                                          : 'border-rule bg-white text-ink/80 hover:border-ink' }}">
                                {{ $v->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <dl class="mt-6 space-y-1 text-sm text-ink/70">
                <div class="flex justify-between border-b border-rule/50 py-1">
                    <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">SKU</dt>
                    <dd class="text-ink">{{ $variant->sku ?? $product->sku }}</dd>
                </div>
                @if ($pricing['store_name'])
                    <div class="flex justify-between border-b border-rule/50 py-1">
                        <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Store</dt>
                        <dd class="text-ink">{{ $pricing['store_name'] }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-b border-rule/50 py-1">
                    <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Berat</dt>
                    <dd class="text-ink">{{ number_format($pricing['weight_gram']) }} gram</dd>
                </div>
            </dl>

            {{-- Quantity + CTA (Design Spec 6.1) --}}
            @unless ($product->is_split_box && $product->splitBox)
                <form method="POST" action="{{ route('cart.store') }}" class="mt-6 flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($variant)
                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                    @endif
                    <div>
                        <label for="qty" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Jumlah</label>
                        <input type="number" name="qty" id="qty" value="1" min="1" max="999"
                               class="mt-2 w-24 rounded-lg border border-rule bg-white px-3 py-2.5 text-center text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    </div>
                    <button type="submit"
                            class="rounded-lg px-6 py-2.5 text-sm font-semibold tracking-[0.08em] text-white transition-colors hover:opacity-90"
                            style="background:#4FC3F7">
                        TAMBAH KE KERANJANG
                    </button>
                </form>
            @endunless
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="font-display text-lg font-semibold text-ink">Produk Terkait</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                @foreach ($related as $relatedProduct)
                    <x-product-card :product="$relatedProduct" :shipping="$shipping" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
