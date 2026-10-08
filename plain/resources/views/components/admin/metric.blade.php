{{--
    Baris angka besar (dipakai di dashboard/laporan).
    Pakai: <x-admin.metric :value="number_format($total)" label="Total pesanan" />
--}}
@props([
    'value' => null,
    'label' => null,
])

<div {{ $attributes->class(['metric']) }}>
    <span class="metric__value">{{ $value }}</span>
    <span class="metric__label">{{ $label }}</span>
</div>
