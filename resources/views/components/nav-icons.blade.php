@php
    $social = config('mercatoria.store.social', []);
    $wa = 'https://wa.me/'.config('mercatoria.store.wa_admin');
    $items = [
        ['label' => 'Cari', 'icon' => '🔍', 'href' => route('catalog.search')],
        ['label' => 'Instagram', 'icon' => '📷', 'href' => $social['instagram'] ?? '#'],
        ['label' => 'Facebook', 'icon' => '📘', 'href' => $social['facebook'] ?? '#'],
        ['label' => 'X', 'icon' => '❌', 'href' => $social['x'] ?? '#'],
        ['label' => 'Threads', 'icon' => '🧵', 'href' => $social['threads'] ?? '#'],
        ['label' => 'WhatsApp', 'icon' => '💬', 'href' => $wa],
    ];
@endphp

@foreach ($items as $item)
    <a href="{{ $item['href'] }}" aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}"
        @if ($item['label'] !== 'Cari') target="_blank" rel="noopener noreferrer" @endif
        class="rounded-lg p-2 text-base hover:bg-surface-2">{{ $item['icon'] }}</a>
@endforeach
