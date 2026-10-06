@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
<section class="account-page">
    <div class="account-card">

        {{-- ============ HEADER ============ --}}
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">{{ $user->full_name ?? 'User' }}</h1>
            <div class="account-head__meta">
                <span class="account-head__role">{{ $user->role?->label() ?? 'User' }}</span>
                <span class="account-head__dot">·</span>
                <span>Terdaftar {{ $user->registered_at?->timezone('Asia/Jakarta')->translatedFormat('F Y') ?? '—' }}</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        {{-- ============ QUOTA (khusus spammer) ============ --}}
        @if ($user->isSpammer())
            @php
                $remaining = $user->remainingViewQuota() ?? 0;
                $viewQuota = $viewQuota ?? 10;
                $percent   = $viewQuota > 0 ? min(100, max(0, ($remaining / $viewQuota) * 100)) : 0;
            @endphp

            @if ($remaining <= 0)
                @php
                    $waNum = preg_replace('/\D/', '', $contactWhatsapp ?? '');
                    if (str_starts_with($waNum, '0')) {
                        $waNum = '62' . substr($waNum, 1);
                    }
                    if ($waNum === '') { $waNum = '6281219683709'; }
                    $waMessage = rawurlencode(
                        "Halo Admin,\n" .
                        "Saya ingin meminta reset kuota lihat produk karena sudah mencapai batas.\n" .
                        "Email akun saya: " . ($user->email ?? '') . "\n" .
                        "Mohon dibantu, terima kasih."
                    );
                @endphp

                <div class="quota-panel quota-panel--empty">
                    <div class="quota-panel__row">
                        <span class="quota-panel__label">Kuota lihat produk</span>
                        <span class="quota-panel__value"><strong>0</strong> / {{ $viewQuota }}</span>
                    </div>
                    <p class="quota-panel__hint" style="color:#b91c1c; margin-top:0;">
                        Kuota habis. Hubungi admin untuk reset agar bisa menjelajah katalog kembali.
                    </p>
                    <a href="https://wa.me/{{ $waNum }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="quota-panel__cta">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        Chat admin via WhatsApp
                    </a>
                </div>
            @else
                <div class="quota-panel {{ $remaining <= 2 ? 'quota-panel--warn' : '' }}">
                    <div class="quota-panel__row">
                        <span class="quota-panel__label">Kuota lihat produk</span>
                        <span class="quota-panel__value"><strong>{{ $remaining }}</strong> / {{ $viewQuota }}</span>
                    </div>
                    <div class="quota-bar">
                        <div class="quota-bar__fill" style="width: {{ $percent }}%"></div>
                    </div>
                    @if ($user->expires_at)
                        <p class="quota-panel__hint">
                            Akun terhapus otomatis pada
                            {{ $user->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                            jika belum ada pembayaran terverifikasi.
                        </p>
                    @endif
                </div>
            @endif
        @endif

        {{-- ============ METRICS STRIP ============ --}}
        <div class="metrics">
            <div class="metrics__item">
                <span class="metrics__label">Total Pesanan</span>
                <span class="metrics__value">{{ number_format($orderStats['total'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Total Belanja</span>
                <span class="metrics__value">{{ \App\Support\PriceCalculator::formatRupiah($totalSpent ?? 0) }}</span>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Koin Aktif</span>
                <span class="metrics__value">{{ number_format($activeCoins ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Undang Teman</span>
                <span class="metrics__value">{{ $referralCount ?? $user->referralsMade()->count() }}</span>
            </div>
        </div>

        {{-- ============ QUICK NAV ============ --}}
        <nav class="quick-nav">
            <a href="{{ route('account.orders.index') }}" class="quick-nav__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>Pesanan Saya</span>
            </a>
            <a href="{{ route('account.coins.index') }}" class="quick-nav__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                    <path d="M12 6v2M12 16v2"/>
                </svg>
                <span>Koin Saya</span>
            </a>
            <a href="{{ route('referral.index') }}" class="quick-nav__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Undang Teman</span>
            </a>
            <a href="{{ route('account.profile.edit') }}" class="quick-nav__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span>Edit Profil</span>
            </a>
        </nav>

        {{-- ============ TAGIHAN MENUNGGU ============ --}}
        @if (isset($unpaidOrders) && $unpaidOrders->isNotEmpty())
            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Tagihan Menunggu</h2>
                    <a href="{{ route('account.orders.index') }}" class="account-section__link">Semua pesanan →</a>
                </div>
                <ul class="account-unpaid-list">
                    @foreach ($unpaidOrders as $u)
                        <li class="account-unpaid-list__item">
                            <div class="account-unpaid-list__info">
                                <span class="account-unpaid-list__id">{{ $u->order_number }}</span>
                                <span class="account-unpaid-list__amount">{{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }} ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})</span>
                                @if ($u->payment_deadline_at)
                                    <div class="account-unpaid-list__deadline">
                                        Bayar sebelum {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti" class="account-unpaid-list__cta">
                                Bayar
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============ PESANAN TERBARU ============ --}}
        <div class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Pesanan Terbaru</h2>
                @if (isset($recentOrders) && $recentOrders->isNotEmpty())
                    <a href="{{ route('account.orders.index') }}" class="account-section__link">Semua →</a>
                @endif
            </div>

            @if (isset($recentOrders) && $recentOrders->isNotEmpty())
                <ul class="account-order-list">
                    @foreach ($recentOrders as $order)
                        <li class="account-order-list__item">
                            <a href="{{ route('account.orders.show', $order->order_number) }}" class="account-order-list__link">
                                <div>
                                    <div class="account-order-list__id">#{{ $order->order_number }}</div>
                                    <div class="account-order-list__date">{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</div>
                                </div>
                                <div style="text-align:right;">
                                    <div class="account-order-list__amount">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</div>
                                    <span class="account-order-list__status {{ in_array($order->status, ['menunggu_pembayaran','pembayaran_gagal']) ? 'account-order-list__status--danger' : (in_array($order->status, ['selesai']) ? 'account-order-list__status--ok' : '') }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="account-empty">
                    <p class="account-empty__title">Belum ada pesanan</p>
                    <p class="account-empty__sub">Yuk mulai jelajahi katalog dan temukan merch favoritmu.</p>
                    <a href="{{ route('catalog.index') }}" class="account-empty__cta">
                        Jelajahi katalog
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>

    </div>{{-- /.account-card --}}
</section>
@endsection
