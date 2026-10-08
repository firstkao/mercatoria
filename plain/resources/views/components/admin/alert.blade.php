{{--
    Alert / notifikasi.
    Varian: success | warning | danger | info (default)
    Pakai: <x-admin.alert variant="success">Data disimpan.</x-admin.alert>
           <x-admin.alert variant="danger" title="Gagal">...</x-admin.alert>
--}}
@props([
    'variant' => 'info',
    'title' => null,
])

<div {{ $attributes->class(['alert', 'alert--'.$variant]) }} role="{{ $variant === 'danger' ? 'alert' : 'status' }}">
    @if ($title)
        <strong>{{ $title }}</strong>
    @endif
    {{ $slot }}
</div>
