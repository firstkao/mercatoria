@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $base = 'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'auth'      => 'bg-black text-white hover:bg-gray-800',
        'primary'   => 'bg-primary-300 text-white hover:bg-primary-400',
        'secondary' => 'border border-border bg-white text-text hover:bg-surface-2',
        'ghost'     => 'bg-transparent text-primary-300 hover:bg-primary-50',
        'danger'    => 'bg-danger-500 text-white hover:opacity-90',
        'success'   => 'bg-success-500 text-white hover:opacity-90',
        'warning'   => 'bg-warning-500 text-white hover:opacity-90',
    ][$variant] ?? '';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $variants"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $variants"]) }}>{{ $slot }}</button>
@endif