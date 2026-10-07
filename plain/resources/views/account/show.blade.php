@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
<div class="account-page">
    <div class="account-card">

        {{-- Header --}}
       <header class="account-head">
        <div class="account-head__row">
            <div class="account-head__main">
                <p class="account-head__eyebrow">Akun Saya</p>
                <h1 class="account-head__title">{{ $user->full_name ?? 'User' }}</h1>
                <div class="account-head__meta">
                    <span class="account-head__role">{{ $user->role?->label() ?? 'User' }}</span>
                    <span class="account-head__dot">·</span>
                    <span>
                        Terdaftar
                        {{ $user->registered_at?->timezone('Asia/Jakarta')->translatedFormat('F Y') ?? '—' }}
                    </span>
                </div>
            </div>
    
            <div class="account-head__actions">
                <a href="{{ route('account.profile.edit') }}" class="account-btn account-btn--ghost account-btn--sm">
                    Edit Profil
                </a>
                <a href="{{ route('catalog.index') }}" class="account-btn account-btn--sm">
                    Mulai Belanja
                </a>
            </div>
        </div>
    
        {{-- Baris fakta (email, tgl lahir, WA) tetap di bawah --}}
        <dl class="account-head__facts">
            <div>
                <dt>Email</dt>
                <dd>{{ $user->email ?? '—' }}</dd>
            </div>
            <div>
                <dt>Tanggal Lahir</dt>
                <dd>{{ $user->birth_date?->timezone('Asia/Jakarta')->translatedFormat('j F Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt>WhatsApp</dt>
                <dd>{{ $user->whatsapp ?? '—' }}</dd>
            </div>
        </dl>
    </header>
        
        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        {{-- Kuota khusus spammer --}}
        @if ($user->isSpammer())
            @php
                $remaining = $user->remainingViewQuota() ?? 0;
                $viewQuota = $viewQuota ?? 10;
                $pct = $viewQuota > 0 ? max(0, min(100, ($remaining / $viewQuota) * 100)) : 0;
            @endphp

            @if ($remaining <= 0)
                @php
                    $waNum = preg_replace('/\D/', '', $contactWhatsapp ?? '');
                    if (str_starts_with($waNum, '0')) { $waNum = '62' . substr($waNum, 1); }
                    if ($waNum === '') { $waNum = '6281219683709'; }
                    $waMessage = rawurlencode(
                        "Halo Admin, saya ingin reset kuota lihat produk.\n" .
                        "Email akun: " . ($user->email ?? '') . "\n" .
                        "Mohon dibantu, terima kasih."
                    );
                @endphp

                <div class="quota-panel quota-panel--empty" role="alert">
                    <div class="quota-panel__row">
                        <span class="quota-panel__label">Kuota lihat produk</span>
                        <span class="quota-panel__value"><strong>0</strong> / {{ $viewQuota }}</span>
                    </div>
                    <div class="quota-bar"><div class="quota-bar__fill" style="width:0%"></div></div>
                    <p class="quota-panel__hint">
                        Hubungi admin lewat WhatsApp untuk reset kuota.
                    </p>
                    <a href="https://wa.me/{{ $waNum }}?text={{ $waMessage }}"
                       target="_blank" rel="noopener"
                       class="quota-panel__action">
                        Chat admin
                    </a>
                </div>
            @else
                <div class="quota-panel {{ $remaining <= 2 ? 'quota-panel--warn' : '' }}" role="status">
                    <div class="quota-panel__row">
                        <span class="quota-panel__label">Sisa lihat produk</span>
                        <span class="quota-panel__value">
                            <strong>{{ $remaining }}</strong> / {{ $viewQuota }}
                        </span>
                    </div>
                    <div class="quota-bar">
                        <div class="quota-bar__fill" style="width:{{ $pct }}%"></div>
                    </div>
                    @if ($user->expires_at)
                        <p class="quota-panel__hint">
                            Akun terhapus otomatis pada
                            {{ $user->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }}
                            WIB bila belum ada pembayaran terverifikasi.
                        </p>
                    @endif
                </div>
            @endif
        @endif

        {{-- Metrics --}}
        <div class="metrics">
            <div class="metrics__item">
                <span class="metrics__label">Total Pesanan</span>
                <strong class="metrics__value">
                    {{ number_format($orderStats['total'] ?? 0, 0, ',', '.') }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Total Belanja</span>
                <strong class="metrics__value">
                    {{ \App\Support\PriceCalculator::formatRupiah($totalSpent ?? 0) }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Koin Aktif</span>
                <strong class="metrics__value">
                    {{ number_format($activeCoins ?? 0, 0, ',', '.') }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Undang Teman</span>
                <strong class="metrics__value">{{ $referralCount ?? 0 }}</strong>
            </div>
        </div>

        {{-- Quick nav --}}
        <nav class="quick-nav">
            <a href="{{ route('account.orders.index') }}" class="quick-nav__item">Pesanan Saya</a>
            <a href="{{ route('account.coins.index') }}" class="quick-nav__item">Koin Saya</a>
            <a href="{{ route('referral.index') }}" class="quick-nav__item">Undang Teman</a>
            <a href="{{ route('account.profile.edit') }}" class="quick-nav__item">Edit Profil</a>
        </nav>

        {{-- Tagihan Menunggu --}}
        @if (isset($unpaidOrders) && $unpaidOrders->isNotEmpty())
            <section class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Tagihan Menunggu</h2>
                    <a href="{{ route('account.orders.index') }}" class="account-section__link">Lihat semua</a>
                </div>
                <ul class="account-unpaid-list">
                    @foreach ($unpaidOrders as $u)
                        <li class="account-unpaid-list__item">
                            <div class="account-unpaid-list__info">
                                <span class="account-unpaid-list__id">{{ $u->order_number }}</span>
                                <span class="account-unpaid-list__amount">
                                    · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }}
                                    ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})
                                </span>
                                @if ($u->payment_deadline_at)
                                    <div class="account-unpaid-list__deadline">
                                        Batas bayar:
                                        {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}
                                        WIB
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti"
                               class="account-unpaid-list__cta">
                                Bayar &amp; unggah bukti
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Notifikasi Baru --}}
        @if (Route::has('notifications.index') && isset($recentNotifs) && $recentNotifs->isNotEmpty())
            <section class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Notifikasi Baru</h2>
                    <a href="{{ route('notifications.index') }}" class="account-section__link">Lihat semua</a>
                </div>
                <ul class="account-notif-list">
                    @foreach ($recentNotifs as $notif)
                        <li>
                            <a href="{{ route('notifications.read', $notif) }}" class="account-notif-list__item">
                                <span class="account-notif-list__dot"></span>
                                <div>
                                    <p class="account-notif-list__title">{{ $notif->title }}</p>
                                    <span class="account-notif-list__time">
                                        {{ $notif->created_at->timezone('Asia/Jakarta')->diffForHumans(short: true) }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Pesanan Terbaru / Empty --}}
        <section class="account-section">
            @if (isset($recentOrders) && $recentOrders->isNotEmpty())
                <div class="account-section__head">
                    <h2 class="account-section__title">Pesanan Terbaru</h2>
                    <a href="{{ route('account.orders.index') }}" class="account-section__link">Lihat semua</a>
                </div>
                <ul class="account-order-list">
                    @foreach ($recentOrders as $order)
                        @php
                            $badge = $order->statusBadgeClass();
                            $mod = str_contains($badge, 'danger')
                                ? 'account-order-list__status--danger'
                                : (str_contains($badge, 'success') ? 'account-order-list__status--ok' : '');
                        @endphp
                        <li class="account-order-list__item">
                            <a href="{{ route('account.orders.show', $order->order_number) }}"
                               class="account-order-list__link">
                                <div>
                                    <span class="account-order-list__id">{{ $order->order_number }}</span>
                                    <div class="account-order-list__date">
                                        {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="account-order-list__amount">
                                        {{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}
                                    </div>
                                    <span class="account-order-list__status {{ $mod }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="account-empty">
                    <h2 class="account-empty__title">Belum ada pesanan.</h2>
                    <p class="account-empty__sub">
                        Begitu kamu checkout, riwayat pesanan akan muncul di sini.
                    </p>
                    <a href="{{ route('catalog.index') }}" class="account-empty__cta">Mulai belanja</a>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
