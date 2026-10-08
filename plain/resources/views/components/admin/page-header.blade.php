{{--
    Header halaman (dipakai di dalam content area, menggantikan .dsh-head/.rpt-head).
    Pakai:
      <x-admin.page-header eyebrow="Laporan" title="Penjualan" meta="1–31 Okt 2026">
          <x-slot:actions><x-admin.button variant="primary">Ekspor</x-admin.button></x-slot:actions>
      </x-admin.page-header>
--}}
@props([
    'title' => null,
    'eyebrow' => null,
    'meta' => null,
])

<div {{ $attributes->class(['page-head']) }}>
    <div>
        @if ($eyebrow)
            <p class="page-head__eyebrow">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h1 class="page-head__title">{{ $title }}</h1>
        @endif
        @if ($meta)
            <p class="page-head__meta">{{ $meta }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="page-head__actions">{{ $actions }}</div>
    @endisset
</div>
