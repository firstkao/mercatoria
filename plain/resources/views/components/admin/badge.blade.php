{{--
    Badge status.
    Varian: null (netral) | on | warn | danger | muted | info
    Pakai: <x-admin.badge variant="on">Tayang</x-admin.badge>
--}}
@props(['variant' => null])

<span {{ $attributes->class(array_filter(['badge', $variant ? 'badge--'.$variant : null])) }}>{{ $slot }}</span>
