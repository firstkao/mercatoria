@props(['mobile' => false])

@php
    $links = [
        ['label' => 'Beranda',   'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Katalog',   'href' => route('catalog.index'), 'active' => request()->routeIs('catalog.*', 'games.*', 'developers.*', 'products.*')],
    ];

    $size = $mobile ? 'block px-3 py-2 text-sm' : 'px-3 py-2 text-sm';
@endphp

@foreach ($links as $link)
    <a href="{{ $link['href'] }}"
       class="{{ $size }} {{ $link['active'] ? 'font-semibold text-navy-500' : 'text-text-muted hover:text-text' }}">
        {{ $link['label'] }}
    </a>
@endforeach