@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')
<div class="dsh">

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

    {{-- ============ METRICS ============ --}}
    <div class="dsh-metrics">
        @foreach ($stats as $label => $value)
            <div class="dsh-metric">
                <span class="dsh-metric__value">{{ number_format($value, 0, ',', '.') }}</span>
                <span class="dsh-metric__label">{{ $label }}</span>
            </div>
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

    {{-- ============ PANDUAN AWAL ============ --}}
    <section class="dsh-section">
        <h2 class="dsh-section__title">Panduan awal</h2>
        <ol class="dsh-steps">
            <li class="dsh-step">
                <span class="dsh-step__num">01</span>
                <span class="dsh-step__text">Isi kurs, tarif ongkir, dan margin produk</span>
                <a href="{{ route('admin.settings.pricing') }}" class="dsh-step__link">
                    Buka
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </li>
            <li class="dsh-step">
                <span class="dsh-step__num">02</span>
                <span class="dsh-step__text">Tambah produk pertama ke katalog</span>
                <a href="{{ route('admin.products.create') }}" class="dsh-step__link">
                    Tambah
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </li>
        </ol>
    </section>

</div>
@endsection
