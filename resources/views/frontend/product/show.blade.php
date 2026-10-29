@extends('layouts.app')

@section('title', $product->name.' — Mercatoria')

@section('content')
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[48%_52%]">
        <div>
            <div class="overflow-hidden rounded-xl border border-rule bg-white">
                @if (($variant?->image_path) ?? $product->image_path)
                    <img id="product-main-img" src="{{ Storage::url($variant?->image_path ?? $product->image_path) }}"
                         alt="{{ $product->name }}" class="aspect-square w-full object-cover cursor-zoom-in"
                         onclick="document.getElementById('product-lightbox').showModal()">
                @else
                    <div class="flex aspect-square w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">
                        Tanpa Foto
                    </div>
                @endif
            </div>

            {{-- Lightbox (DS 6.3) --}}
            <dialog id="product-lightbox" class="m-0 h-full w-full max-h-none max-w-none bg-transparent p-0">
                <div class="fixed inset-0 flex items-center justify-center bg-black/85 p-4" onclick="if(event.target===this)this.closest('dialog').close()">
                    <button type="button" class="absolute right-4 top-4 text-white/80 transition-colors hover:text-white"
                            onclick="document.getElementById('product-lightbox').close()" aria-label="Tutup">
                        <x-icons.chevron-up class="rotate-45" width="24" height="24" />
                    </button>
                    <img id="lightbox-img" src="{{ Storage::url($variant?->image_path ?? $product->image_path) }}"
                         alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                </div>
            </dialog>

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
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $product->name }}</h1>

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
                        @if ($variant)
                            <a href="/{{ $game->slug }}/{{ $product->slug }}"
                               class="inline-flex items-center justify-center gap-1 rounded-lg border border-rule px-3 py-2 text-center text-sm text-ink/60 transition-colors hover:border-ink hover:text-ink">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                                Clear
                            </a>
                        @endif
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
                            class="rounded-lg bg-accent px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:opacity-90">
                        TAMBAH KE KERANJANG
                    </button>
                </form>
            @endunless

            {{-- Share link reward (PRD 9.5 / Design Spec 6.1) --}}
            @php($shareUrl = request()->getSchemeAndHttpHost().'/'.$game->slug.'/'.$product->slug.(auth()->user() ? '?ref='.auth()->user()->referral_code : ''))
            <div class="mt-5 rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Bagikan produk ini</p>
                @auth
                    <p class="mt-1.5 text-sm text-ink/75">+5 koin per IP unik (khusus IP Indonesia) saat link dibuka.</p>
                    <p class="mt-2 break-all font-mono text-xs text-link">{{ $shareUrl }}</p>
                @else
                    <p class="mt-1.5 text-sm text-ink/70">
                        <a href="{{ route('login') }}" class="font-medium text-link underline decoration-link/35 underline-offset-4">Masuk</a>
                        untuk membagikan link referral dan mendapat koin.
                    </p>
                @endauth
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" data-copy="{{ $shareUrl }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <x-icons.clipboard width="14" height="14" /> Copy Link
                    </button>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <x-icons.facebook width="14" height="14" /> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($product->name) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <x-icons.twitter-x width="14" height="14" /> X
                    </a>
                    <a href="mailto:?subject={{ urlencode($product->name) }}&body={{ urlencode($shareUrl) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg>
                        Email
                    </a>
                    <button type="button" data-bookmark="{{ $shareUrl }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5z"/></svg>
                        Bookmark
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = btn.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value);
                    }
                });
            });
            document.querySelectorAll('[data-bookmark]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = btn.getAttribute('data-bookmark');
                    try {
                        var list = JSON.parse(localStorage.getItem('bookmarked_products') || '[]');
                        if (list.indexOf(url) === -1) list.push(url);
                        localStorage.setItem('bookmarked_products', JSON.stringify(list));
                        btn.textContent = 'Tersimpan';
                    } catch (e) {}
                });
            });
        })();
    </script>

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
