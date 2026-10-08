{{--
    Baris metrik (grid angka besar). Isi dengan <x-admin.metric>.
    Pakai:
      <x-admin.metric-strip>
          <x-admin.metric :value="'12'" label="Produk tayang" />
          <x-admin.metric :value="'3'" label="Draf" />
      </x-admin.metric-strip>
--}}
@props([])

<div {{ $attributes->class(['metric-strip']) }}>{{ $slot }}</div>
