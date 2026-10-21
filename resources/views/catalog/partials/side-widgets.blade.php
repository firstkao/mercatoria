@php($prices = app(\App\Services\Product\ProductPriceFormatter::class))
<div class="space-y-4">
    @if ($bestOffers->isNotEmpty())
        <section class="border border-border bg-white p-4" aria-labelledby="best-offer-title-{{ $suffix }}">
            <h2 id="best-offer-title-{{ $suffix }}" class="border-b border-border pb-2 text-sm font-bold text-[#111]">Penawaran Terbaik</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-1">
                @foreach ($bestOffers as $offer)
                    <x-product-card :product="$offer" :discount="true" />
                @endforeach
            </div>
        </section>
    @endif

    @auth
        <section class="border border-border bg-white p-4" aria-labelledby="recent-title-{{ $suffix }}">
            <h2 id="recent-title-{{ $suffix }}" class="border-b border-border pb-2 text-sm font-bold text-[#111]">Terakhir Dilihat</h2>
            @forelse ($recentViews as $viewed)
                @php($viewedGame = $viewed->games->first())
                <a href="{{ $viewedGame ? route('games.products.show', [$viewedGame->slug, $viewed->slug]) : route('products.direct', $viewed->slug) }}"
                    class="flex gap-3 border-b border-border py-2 last:border-b-0">
                    @if ($viewed->main_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($viewed->main_image_path) }}" alt="" loading="lazy" draggable="false" class="h-12 w-12 shrink-0 object-cover">
                    @else
                        <span class="h-12 w-12 shrink-0 bg-surface-2"></span>
                    @endif
                    <span class="min-w-0 text-[11.5px] leading-snug">
                        <span class="line-clamp-2 font-bold text-[#212121]">@if ($viewedGame)[{{ mb_strtoupper($viewedGame->name) }}] @endif{{ $viewed->name }}</span>
                        <span class="block font-bold text-primary-400">{{ $prices->label($viewed) }}</span>
                    </span>
                </a>
            @empty
                <p class="mt-3 text-xs text-text-muted">Belum ada produk yang kamu lihat.</p>
            @endforelse
        </section>
    @endauth
</div>
