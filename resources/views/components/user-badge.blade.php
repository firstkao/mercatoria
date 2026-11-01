@props(['value', 'type' => 'state'])

@php
    $state = [
        'Crush' => ['bg' => '#F48FB1', 'text' => '#212121', 'icon' => 'heart'],
        'MVP' => ['bg' => '#43A047', 'text' => '#FFFFFF', 'icon' => 'star'],
        'Ex' => ['bg' => '#64B5F6', 'text' => '#212121', 'icon' => 'clock'],
        'ONS' => ['bg' => '#FFB74D', 'text' => '#212121', 'icon' => 'moon'],
        'Blocked' => ['bg' => '#E53935', 'text' => '#FFFFFF', 'icon' => 'lock'],
        'Grace' => ['bg' => '#FFD54F', 'text' => '#212121', 'icon' => 'hourglass-split'],
    ];
    $tier = [
        'MVP Silver' => ['bg' => '#B0BEC5', 'text' => '#212121', 'icon' => 'shield'],
        'MVP Gold' => ['bg' => '#FFC107', 'text' => '#212121', 'icon' => 'trophy'],
        'MVP Platinum' => ['bg' => '#4FC3F7', 'text' => '#FFFFFF', 'icon' => 'sparkles'],
    ];
    $map = $type === 'tier' ? $tier : $state;
    $meta = $map[$value] ?? ['bg' => '#D8D2C4', 'text' => '#141B22', 'icon' => null];
@endphp

<span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[10px] font-semibold"
      style="background:{{ $meta['bg'] }};color:{{ $meta['text'] }}">
    @if ($type === 'tier')
        <x-icons.trophy width="10" height="10" />
    @endif
    {{ $value }}
</span>
