@props(['state'])

@php
    $value = $state instanceof \BackedEnum ? $state->value : (string) $state;

    $map = [
        'crush' => ['🌸 Crush', 'bg-crush/20 text-text'],
        'mvp'   => ['⭐ MVP',   'bg-mvp/15 text-success-700'],
        'ex'    => ['Ex',       'bg-ex/20 text-info-700'],
        'ons'   => ['ONS',      'bg-ons/25 text-warning-700'],
    ];

    [$label, $class] = $map[$value] ?? [ucfirst($value), 'bg-surface-2 text-text-muted'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold $class"]) }}>
    {{ $label }}
</span>