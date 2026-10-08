{{--
    Loading state standar (spinner + label).
    Pakai: <x-admin.loading-state label="Memuat data…" />
--}}
@props(['label' => 'Memuat…'])

<div {{ $attributes->class(['loading-state']) }} role="status" aria-live="polite">
    <span class="spinner" aria-hidden="true"></span>
    <span>{{ $label }}</span>
</div>
