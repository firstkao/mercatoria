@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')
    @unless ($ratesConfigured)
        <div class="alert alert--warning">
            Kurs dan tarif ongkir belum diisi, jadi harga produk belum bisa dihitung.
            <a href="{{ route('admin.settings.pricing') }}">Isi di Pengaturan</a>
        </div>
    @endunless

    <div class="stats">
        @foreach ($stats as $label => $value)
            <div class="stat">
                <span class="stat__label">{{ $label }}</span>
                <strong class="stat__value">{{ number_format($value, 0, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>

    {{-- Aksi cepat --}}
    @php($badges = $adminBadges ?? [])
    @if (($badges['pendingProofs'] ?? 0) > 0 || ($badges['pendingOrders'] ?? 0) > 0 || ($badges['pendingResellers'] ?? 0) > 0)
        <section class="panel stack">
            <h2>Perlu Perhatian</h2>

            @if (($badges['pendingProofs'] ?? 0) > 0)
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="quick-action">
                    <strong>{{ $badges['pendingProofs'] }}</strong> bukti pembayaran menunggu verifikasi
                    <span class="quick-action__arrow">→</span>
                </a>
            @endif

            @if (($badges['pendingOrders'] ?? 0) > 0)
                <a href="{{ route('admin.orders.index') }}" class="quick-action">
                    <strong>{{ $badges['pendingOrders'] }}</strong> pesanan belum selesai diproses
                    <span class="quick-action__arrow">→</span>
                </a>
            @endif

            @if (($badges['pendingResellers'] ?? 0) > 0)
                <a href="{{ route('admin.reports.index') }}" class="quick-action">
                    <strong>{{ $badges['pendingResellers'] }}</strong> pendaftar reseller baru
                    <span class="quick-action__arrow">→</span>
                </a>
            @endif
        </section>
    @endif

    <section class="panel">
        <h2>Mulai dari sini</h2>
        <ol class="steps">
            <li><a href="{{ route('admin.settings.pricing') }}">Isi kurs, tarif ongkir, dan margin</a></li>
            <li><a href="{{ route('admin.products.create') }}">Tambah produk pertama</a></li>
        </ol>
    </section>
@endsection