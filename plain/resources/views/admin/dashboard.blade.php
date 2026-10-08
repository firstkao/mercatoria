@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')

    <p class="muted mb-4">Selamat datang kembali, <strong class="text-strong">{{ auth('admin')->user()->name }}</strong> · {{ now()->timezone('Asia/Jakarta')->translatedFormat('l, j F Y · H:i') }} WIB</p>

    @unless ($ratesConfigured)
        <x-admin.alert variant="warning">
            <strong>Kurs &amp; tarif ongkir belum diatur.</strong> Harga produk belum bisa dihitung.
            <a href="{{ route('admin.settings.pricing') }}">Atur sekarang →</a>
        </x-admin.alert>
    @endunless

    {{-- ============ GROUPED METRICS ============ --}}
    @php
        $s = $stats ?? [];

        $groups = [
            [
                'label' => 'Pengguna',
                'items' => [
                    ['label' => 'Spammer',  'value' => $s['Spammer'] ?? 0],
                    ['label' => 'Customer', 'value' => $s['Customer'] ?? 0],
                    ['label' => 'Reseller', 'value' => $s['Pendaftar reseller baru'] ?? 0],
                ],
            ],
            [
                'label' => 'Penjualan',
                'items' => [
                    ['label' => 'Pesanan Baru',     'value' => $s['Pesanan baru'] ?? 0],
                    ['label' => 'Pembayaran Masuk', 'value' => $s['Bukti pembayaran pending'] ?? 0],
                ],
            ],
            [
                'label' => 'Katalog',
                'items' => [
                    ['label' => 'Total Produk', 'value' => $s['Produk tayang'] ?? 0],
                    ['label' => 'Draf Produk',  'value' => $s['Draf produk'] ?? 0],
                ],
            ],
        ];
    @endphp

    @foreach ($groups as $group)
        <x-admin.card class="mb-6">
            <h2 class="section-label">{{ $group['label'] }}</h2>
            <x-admin.metric-strip>
                @foreach ($group['items'] as $item)
                    <x-admin.metric :value="number_format($item['value'], 0, ',', '.')" :label="$item['label']" />
                @endforeach
            </x-admin.metric-strip>
        </x-admin.card>
    @endforeach

@endsection
