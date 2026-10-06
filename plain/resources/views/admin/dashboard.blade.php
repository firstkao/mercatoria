@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')

    @unless ($ratesConfigured)
        <div class="ad-alert" role="alert">
            <div class="ad-alert__body">
                <strong class="ad-alert__title">Kurs &amp; tarif ongkir belum diatur</strong>
                <p class="ad-alert__text">Harga produk belum bisa dihitung sampai kurs, tarif ongkir, dan margin diisi.</p>
            </div>
            <a href="{{ route('admin.settings.pricing') }}" class="ad-alert__cta">
                Atur sekarang
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    @endunless

    {{-- ============ METRICS STRIP ============ --}}
    <div class="ad-metrics">
        @foreach ($stats as $label => $value)
            <div class="ad-metrics__item">
                <span class="ad-metrics__label">{{ $label }}</span>
                <span class="ad-metrics__value">{{ number_format($value, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>

    {{-- ============ PERLU PERHATIAN ============ --}}
    @php($badges = $adminBadges ?? [])
    @php($hasAttention = ($badges['pendingProofs'] ?? 0) > 0
        || ($badges['pendingOrders'] ?? 0) > 0
        || ($badges['pendingResellers'] ?? 0) > 0)

    @if ($hasAttention)
        <section class="ad-section">
            <div class="ad-section__head">
                <h2 class="ad-section__title">Perlu Perhatian</h2>
            </div>

            <ul class="ad-todo">
                @if (($badges['pendingProofs'] ?? 0) > 0)
                    <li class="ad-todo__item">
                        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="ad-todo__link">
                            <span class="ad-todo__count">{{ $badges['pendingProofs'] }}</span>
                            <span class="ad-todo__text">
                                <span class="ad-todo__title">Bukti pembayaran menunggu verifikasi</span>
                                <span class="ad-todo__sub">Konfirmasi untuk melanjutkan proses pesanan</span>
                            </span>
                            <svg class="ad-todo__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                @endif

                @if (($badges['pendingOrders'] ?? 0) > 0)
                    <li class="ad-todo__item">
                        <a href="{{ route('admin.orders.index') }}" class="ad-todo__link">
                            <span class="ad-todo__count">{{ $badges['pendingOrders'] }}</span>
                            <span class="ad-todo__text">
                                <span class="ad-todo__title">Pesanan belum selesai diproses</span>
                                <span class="ad-todo__sub">Perlu dikemas dan dikirim ke pembeli</span>
                            </span>
                            <svg class="ad-todo__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                @endif

                @if (($badges['pendingResellers'] ?? 0) > 0)
                    <li class="ad-todo__item">
                        <a href="{{ route('admin.reports.index') }}" class="ad-todo__link">
                            <span class="ad-todo__count">{{ $badges['pendingResellers'] }}</span>
                            <span class="ad-todo__text">
                                <span class="ad-todo__title">Pendaftar reseller baru</span>
                                <span class="ad-todo__sub">Tinjau dan setujui permintaan reseller</span>
                            </span>
                            <svg class="ad-todo__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                @endif
            </ul>
        </section>
    @endif

    {{-- ============ MULAI DARI SINI ============ --}}
    <section class="ad-section">
        <div class="ad-section__head">
            <h2 class="ad-section__title">Mulai dari sini</h2>
        </div>
        <ol class="ad-steps">
            <li class="ad-steps__item">
                <a href="{{ route('admin.settings.pricing') }}" class="ad-steps__link">
                    Isi kurs, tarif ongkir, dan margin
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </li>
            <li class="ad-steps__item">
                <a href="{{ route('admin.products.create') }}" class="ad-steps__link">
                    Tambah produk pertama
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </li>
        </ol>
    </section>

@endsection
