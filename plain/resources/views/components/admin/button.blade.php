{{--
    Tombol admin (satu-satunya cara membuat tombol).
    Varian: default | primary | danger | danger-outline | danger-text
    Ukuran: md (default) | sm
    Pakai: <x-admin.button variant="primary" icon="plus">Tambah</x-admin.button>
           <x-admin.button variant="danger" type="submit" form="x" size="sm">Hapus</x-admin.button>
           <x-admin.button href="{{ route('...') }}" variant="primary">Lihat</x-admin.button>
--}}
@props([
    'variant' => 'default',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'block' => false,
    'icon' => null,
])

@php
    $classes = array_filter([
        'btn',
        $variant !== 'default' ? 'btn--'.$variant : null,
        $size === 'sm' ? 'btn--small' : null,
        $block ? 'btn--block' : null,
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except('type')->class($classes) }}>
        @if ($icon)@include('admin.partials.icon', ['name' => $icon])@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)@include('admin.partials.icon', ['name' => $icon])@endif
        <span>{{ $slot }}</span>
    </button>
@endif
