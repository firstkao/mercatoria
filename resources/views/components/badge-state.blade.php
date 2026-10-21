@props(['state' => null, 'tier' => null, 'user' => null, 'short' => false])

@php
    if ($user) {
        $state = $user->currentState();
        $tier = $user->currentTier();
    }

    $value = $state instanceof \BackedEnum ? $state->value : (string) $state;
    $tierValue = $tier instanceof \BackedEnum ? $tier->value : (string) $tier;

    $states = [
        'crush'   => ['🌸 Crush',   'bg-[#F48FB1] text-[#212121]'],
        'mvp'     => ['⭐ MVP',     'bg-[#43A047] text-white'],
        'ex'      => ['💤 Ex',      'bg-[#64B5F6] text-[#212121]'],
        'ons'     => ['🌙 ONS',     'bg-[#FFB74D] text-[#212121]'],
        'blocked' => ['🔒 Blocked', 'bg-[#E53935] text-white'],
    ];

    $tiers = [
        'silver'   => ['🥈 MVP Silver',   '🥈 Silver',   'bg-[#B0BEC5] text-[#212121]'],
        'gold'     => ['🥇 MVP Gold',     '🥇 Gold',     'bg-[#FFC107] text-[#212121]'],
        'platinum' => ['💎 MVP Platinum', '💎 Platinum', 'bg-[#4FC3F7] text-white'],
    ];

    [$label, $class] = $states[$value] ?? [ucfirst($value), 'bg-surface-2 text-text-muted'];
    $pill = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold';
    $isCustomer = in_array($value, ['mvp', 'ex', 'ons', 'blocked'], true);
    $tierBadge = $isCustomer ? ($tiers[$tierValue] ?? null) : null;
@endphp

<span {{ $attributes->merge(['class' => "$pill $class"]) }}>{{ $label }}</span>
@if ($tierBadge)
    <span class="{{ $pill }} {{ $tierBadge[2] }}">{{ $short ? $tierBadge[1] : $tierBadge[0] }}</span>
@endif