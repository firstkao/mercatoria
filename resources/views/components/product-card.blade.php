@props(['product', 'shipping', 'matchedVariant' => null])

@php
    $pricing = $shipping->calculateItemShipping($product, $matchedVariant);
    $tags = $product->tags;
    $href = '/'.$product->game->slug.'/'.$product->slug
        .($matchedVariant ? '?variant='.$matchedVariant->id : '');
@endphp

<a href="{{ $href }}" class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
    <div class="relative aspect-square overflow-hidden bg-gray-100">
        @if ($matchedVariant?->image_path)
            <img src="{{ Storage::url($matchedVariant->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @elseif ($product->image_path)
            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">Tanpa Foto</div>
        @endif

        @foreach ($tags as $tag)
            <span class="absolute left-0 top-2 rounded-r bg-red-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                {{ $tag->name }}
            </span>
        @endforeach
    </div>

    <div class="flex flex-1 flex-col gap-1 p-3">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
            [{{ $product->game->name }}]
        </p>
        <p class="line-clamp-2 text-sm font-medium">{{ $product->name }}</p>

        @if ($matchedVariant)
            <p class="inline-flex w-fit items-center gap-1 rounded bg-sky-50 px-1.5 py-0.5 text-[11px] text-sky-700">
                Varian tersedia: {{ $matchedVariant->name }}
            </p>
        @endif

        <div class="mt-auto pt-1 text-sm">
            @if ($product->hasActiveDiscount())
                <span class="text-xs text-gray-400 line-through">{{ format_rp($pricing['base_price_bersih']) }}</span>
            @endif
            <span class="font-semibold text-sky-600">{{ format_rp($pricing['final_price_bersih']) }}</span>
        </div>
    </div>
</a>
