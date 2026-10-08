{{--
    Empty state standar.
    Pakai:
      <x-admin.empty-state title="Belum ada produk" hint="Tambahkan produk pertama.">
          <x-slot:actions><x-admin.button variant="primary" href="...">Tambah</x-admin.button></x-slot:actions>
      </x-admin.empty-state>
--}}
@props([
    'title' => 'Belum ada data',
    'hint' => null,
])

<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-state__title">{{ $title }}</span>
    @if ($hint)
        <span class="empty-state__hint">{{ $hint }}</span>
    @endif
    @isset($actions)
        <div class="empty-state__actions">{{ $actions }}</div>
    @endisset
</div>
