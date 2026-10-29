@props(['status', 'size' => 'sm'])

@php
    $sizeClass = $size === 'lg' ? 'px-3 py-1 text-sm' : 'px-2 py-0.5 text-[11px]';
@endphp

<span class="inline-flex items-center rounded {{ $sizeClass }} font-semibold {{ order_status_color($status) }}">{{ order_status_label($status) }}</span>
