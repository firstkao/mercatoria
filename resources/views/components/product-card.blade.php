@props(['product', 'shipping', 'matchedVariant' => null])

@php
    $pricing = $shipping->calculateItemShipping($product, $matchedVariant);
    $tags = $product->tags;
    $href = '/'.$product->game->slug.'/'.$product->slug
        .($matchedVariant ? '?variant='.$matchedVariant->id : '');
@endphp

<a href="{{ $href }}" class="group flex flex-col overflow-hidden rounded-lg border border-rule bg-white transition-colors hover:border-ink">
    <div class="relative aspect-square overflow-hidden bg-paper">
        @if ($matchedVariant?->image_path)
            <img src="{{ Storage::url($matchedVariant->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @elseif ($product->image_path)
            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="flex h-full w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">Tanpa Foto</div>
        @endif

        @foreach ($tags as $tag)
            <span class="absolute left-0 top-2 -rotate-45 px-3 py-0.5 text-[9.5px] font-bold uppercase text-white {{ $tag->slug === 'presale' ? 'bg-[#29B6F6]' : ($tag->slug === 'limited' ? 'bg-[#E53935]' : 'bg-[#4FC3F7]') }}">
                {{ $tag->name }}
            </span>
        @endforeach
    </div>

    <div class="flex flex-1 flex-col gap-1 p-3">
        <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
            [{{ $product->game->name }}]
        </p>
        <p class="line-clamp-2 text-[11.5px] font-bold leading-[1.35] text-ink">{{ $product->name }}</p>

        @if ($matchedVariant)
            <p class="inline-flex w-fit items-center gap-1 rounded bg-accent/15 px-1.5 py-0.5 text-[11px] text-link">
                Varian tersedia: {{ $matchedVariant->name }}
            </p>
        @endif

        <div class="mt-auto pt-1">
            @if ($product->hasActiveDiscount())
                <span class="text-xs text-ink/45 line-through">{{ format_rp($pricing['base_price_bersih']) }}</span>
            @endif
            <span class="text-[11.5px] font-bold text-[#29B6F6]">{{ format_rp($pricing['final_price_bersih']) }}</span>
        </div>
    </div>
</a>
