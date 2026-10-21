@props(['product', 'matchedVariant' => null, 'discount' => false])

@php
    $prices = app(\App\Services\Product\ProductPriceFormatter::class);
    $productGame = $product->games->first();
    $productUrl = $productGame
        ? route('games.products.show', [$productGame->slug, $product->slug])
        : route('products.direct', $product->slug);
    if ($matchedVariant) {
        $productUrl .= '?variant='.$matchedVariant->id;
    }
    $image = $matchedVariant?->image_path ?: $product->main_image_path;
    $tagNames = $product->tags->pluck('name')->map(fn ($name) => mb_strtoupper($name));
    $ribbon = $discount ? 'DISKON' : ($tagNames->contains('PRESALE') ? 'PRESALE' : ($tagNames->contains('LIMITED') ? 'LIMITED' : ($product->availability === 'kosong' ? 'KOSONG' : null)));
    $ribbonClasses = match ($ribbon) {
        'PRESALE' => 'bg-[#FFC107] text-black',
        'LIMITED', 'DISKON' => 'bg-[#E53935] text-white',
        'KOSONG' => 'bg-[#9E9E9E] text-white',
        default => '',
    };
    $hasDiscount = $product->hasActiveDiscount();
@endphp

<article {{ $attributes->class('group relative') }} data-product-card>
    <a href="{{ $productUrl }}" class="block">
        <div class="relative aspect-square overflow-hidden bg-surface-2">
            @if ($image)
                <x-protected-image :src="\Illuminate\Support\Facades\Storage::url($image)" :alt="$product->name" loading="lazy"
                    class="h-full w-full object-cover transition duration-200 group-hover:scale-[1.03]" />
            @else
                <div class="flex h-full w-full items-center justify-center text-xs text-text-light">Gambar belum tersedia</div>
            @endif
            @if ($ribbon)
                <span data-product-badge class="absolute left-0 top-2 z-10 px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wide {{ $ribbonClasses }}">{{ $ribbon }}</span>
            @endif
        </div>
        <div class="pt-2">
            <h3 class="line-clamp-3 text-[11.5px] font-bold leading-[1.35] text-[#212121]">
                @if ($productGame)<span>[{{ mb_strtoupper($productGame->name) }}]</span>@endif
                {{ $product->name }}
            </h3>
            @if ($matchedVariant)
                <span class="mt-1 inline-block rounded-full bg-[#E3F2FD] px-2 py-0.5 text-[10.5px] font-semibold text-[#1a4d8f]">🎯 Varian: {{ $matchedVariant->name }}</span>
            @endif
            @if ($hasDiscount)
                <p class="mt-1 text-[11px] text-text-muted line-through">{{ $prices->originalLabel($product) }}</p>
            @endif
            <p class="{{ $hasDiscount ? '' : 'mt-1 ' }}text-[11.5px] font-bold text-primary-400">{{ $prices->label($product) }}</p>
        </div>
    </a>
</article>
