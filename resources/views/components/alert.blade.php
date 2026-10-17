@props(['type' => 'info'])

@php
    $styles = [
        'info'    => 'border-info-500 bg-info-50',
        'success' => 'border-success-500 bg-success-50',
        'warning' => 'border-warning-500 bg-warning-50',
        'danger'  => 'border-danger-500 bg-danger-50',
    ][$type] ?? 'border-info-500 bg-info-50';
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border-l-4 p-3 text-sm text-text $styles"]) }} role="alert">
    {{ $slot }}
</div>