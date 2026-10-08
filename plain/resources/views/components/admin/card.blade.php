{{--
    Card / panel admin (satu-satunya container konten).
    padding: null (default, 24px) | flush (0, untuk tabel) | inner (latar abu)
    Pakai: <x-admin.card>...</x-admin.card>
           <x-admin.card padding="flush"> <table>...</table> </x-admin.card>
    Slot opsional: <x-slot:head>Judul</x-slot:head>
--}}
@props([
    'padding' => null,
    'as' => 'section',
])

@php
    $classes = array_filter(['panel', $padding ? 'panel--'.$padding : null]);
@endphp

<{{ $as }} {{ $attributes->class($classes) }}>
    @isset($head)
        <div class="panel__head">{{ $head }}</div>
    @endisset
    {{ $slot }}
</{{ $as }}>
