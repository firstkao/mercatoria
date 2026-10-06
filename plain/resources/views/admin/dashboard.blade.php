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

    {{-- ============ PERLU TINDAKAN ============ --}}
    @php($badges = $adminBadges ?? [])
    @php($pendingProofs = $badges['pendingProofs'] ?? 0)
    @php($pendingOrders = $badges['pendingOrders'] ?? 0)
    @php($pendingResellers = $badges['pendingResellers'] ?? 0)

    <section class="dsh-section">
        <h2 class="dsh-section__title">Perlu tindakan</h2>
        <div class="dsh-attention">

            @if ($pendingProofs > 0)
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="dsh-card">
                    <span class="dsh-card__count">{{ $pendingProofs }}</span>
                    <p class="dsh-card__title">Bukti pembayaran menunggu verifikasi</p>
                    <p class="dsh-card__sub">Konfirmasi untuk melanjutkan proses pesanan</p>
                    <span class="dsh-card__action">
                        Tinjau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @else
                <div class="dsh-card dsh-card--empty">
                    <span class="dsh-card__count">0</span>
                    <p class="dsh-card__title">Bukti pembayaran</p>
                    <p class="dsh-card__sub">Semua bersih, tidak ada yang menunggu</p>
                </div>
            @endif

            @if ($pendingOrders > 0)
                <a href="{{ route('admin.orders.index') }}" class="dsh-card">
                    <span class="dsh-card__count">{{ $pendingOrders }}</span>
                    <p class="dsh-card__title">Pesanan belum selesai diproses</p>
                    <p class="dsh-card__sub">Perlu dikemas dan dikirim ke pembeli</p>
                    <span class="dsh-card__action">
                        Proses
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @else
                <div class="dsh-card dsh-card--empty">
                    <span class="dsh-card__count">0</span>
                    <p class="dsh-card__title">Pesanan diproses</p>
                    <p class="dsh-card__sub">Tidak ada pesanan yang menumpuk</p>
                </div>
            @endif

            @if ($pendingResellers > 0)
                <a href="{{ route('admin.reports.index') }}" class="dsh-card">
                    <span class="dsh-card__count">{{ $pendingResellers }}</span>
                    <p class="dsh-card__title">Pendaftar reseller baru</p>
                    <p class="dsh-card__sub">Tinjau dan setujui permintaan reseller</p>
                    <span class="dsh-card__action">
                        Tinjau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @else
                <div class="dsh-card dsh-card--empty">
                    <span class="dsh-card__count">0</span>
                    <p class="dsh-card__title">Pendaftar reseller</p>
                    <p class="dsh-card__sub">Tidak ada pendaftar baru</p>
                </div>
            @endif

        </div>
    </section>
