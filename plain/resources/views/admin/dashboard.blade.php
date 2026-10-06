@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')

    {{-- ============ HEAD ============ --}}
    <header class="dsh-head">
        <div>
            <p class="dsh-head__eyebrow">Ringkasan</p>
            <h1 class="dsh-head__title">Selamat datang kembali,<br>{{ auth('admin')->user()->name }}</h1>
        </div>
        <time class="dsh-head__time">{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, j F Y · H:i') }} WIB</time>
    </header>

    @unless ($ratesConfigured)
        <div class="dsh-alert" role="alert">
            <span><strong>Kurs &amp; tarif ongkir belum diatur.</strong> Harga produk belum bisa dihitung.</span>
            <a href="{{ route('admin.settings.pricing') }}">Atur sekarang →</a>
        </div>
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

    <div class="dsh-groups">
        @foreach ($groups as $group)
            <section class="dsh-group">
                <h2 class="dsh-group__label">{{ $group['label'] }}</h2>
                <div class="dsh-group__items">
                    @foreach ($group['items'] as $item)
                        <div class="dsh-item">
                            <span class="dsh-item__value">{{ number_format($item['value'], 0, ',', '.') }}</span>
                            <span class="dsh-item__label">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

@endsection
