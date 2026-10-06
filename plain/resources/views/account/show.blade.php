@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
<section class="card" style="max-width:1000px;">

    {{-- Welcome --}}
    <div class="account-hero">
        <div>
            <p class="eyebrow">Akun saya</p>
            <h1 style="margin:4px 0 8px;">Halo, {{ $user->full_name ?? 'User' }}</h1>
            <p class="muted" style="margin:0;">
                <span class="badge badge--{{ $user->role?->value ?? 'default' }}">
                    {{ $user->role?->label() ?? 'User' }}
                </span>
                <span class="muted" style="margin-left:8px;">
                    Terdaftar {{ $user->registered_at?->timezone('Asia/Jakarta')->translatedFormat('F Y') ?? '—' }}
                </span>
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    {{-- Kuota khusus spammer --}}
    @if ($user->isSpammer())
        @php
            $remaining = $user->remainingViewQuota() ?? 0;
            $viewQuota = $viewQuota ?? 10;
        @endphp

        @if ($remaining <= 0)
            @php
                $waNum = preg_replace('/\D/', '', $contactWhatsapp ?? '');
                if (str_starts_with($waNum, '0')) {
                    $waNum = '62' . substr($waNum, 1);
                }
                if ($waNum === '') {
                    $waNum = '6281219683709';
                }
                $waMessage = rawurlencode(
                    "Halo Admin, saya ingin reset kuota lihat produk.\n" .
                    "Email akun: " . ($user->email ?? '') . "\n" .
                    "Mohon dibantu, terima kasih."
                );
            @endphp

            <div class="quota quota--empty" role="alert">
                <strong class="quota__title">Kuota lihat produk habis</strong>
                <p class="quota__desc">
                    Hubungi admin lewat WhatsApp untuk reset kuota.
                </p>
                <a href="https://wa.me/{{ $waNum }}?text={{ $waMessage }}"
                   target="_blank" rel="noopener"
                   class="button button--whatsapp">
                    Chat admin
                </a>
            </div>
        @else
            <div class="quota {{ $remaining <= 2 ? 'quota--warning' : '' }}" role="status">
                <strong class="quota__title">
                    Sisa lihat produk: {{ $remaining }} / {{ $viewQuota }}
                </strong>
                @if ($user->expires_at)
                    <p class="quota__desc">
                        Akun terhapus otomatis pada
                        {{ $user->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                        bila belum ada pembayaran terverifikasi.
                    </p>
                @endif
            </div>
        @endif
    @endif

    {{-- Stat cards --}}
    <div class="account-stats">
        <div class="account-stat">
            <span class="account-stat__label">Total Pesanan</span>
            <strong class="account-stat__value">
                {{ number_format($orderStats['total'] ?? 0, 0, ',', '.') }}
            </strong>
        </div>
        <div class="account-stat">
            <span class="account-stat__label">Total Belanja</span>
            <strong class="account-stat__value">
                {{ \App\Support\PriceCalculator::formatRupiah($totalSpent ?? 0) }}
            </strong>
        </div>
        <div class="account-stat">
            <span class="account-stat__label">Koin Aktif</span>
            <strong class="account-stat__value">
                {{ number_format($activeCoins ?? 0, 0, ',', '.') }}
            </strong>
        </div>
        <div class="account-stat">
            <span class="account-stat__label">Undang Teman</span>
            <strong class="account-stat__value">
                {{ $referralCount ?? 0 }}
            </strong>
        </div>
    </div>

    {{-- Tagihan menunggu --}}
    @if (isset($unpaidOrders) && $unpaidOrders->isNotEmpty())
        <div class="panel panel--accent stack">
            <div class="panel__head">
                <h2>Tagihan Menunggu</h2>
                <a href="{{ route('account.orders.index') }}" class="link">Lihat semua</a>
            </div>
            @foreach ($unpaidOrders as $u)
                <div class="row">
                    <div>
                        <strong class="mono">{{ $u->order_number }}</strong>
                        <span class="muted">
                            · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }}
                            ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})
                        </span>
                        @if ($u->payment_deadline_at)
                            <div class="muted small">
                                Batas bayar:
                                {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti"
                       class="button button--small">
                        Bayar &amp; unggah bukti
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Notifikasi baru --}}
    @if (Route::has('notifications.index') && isset($recentNotifs) && $recentNotifs->isNotEmpty())
        <div class="panel">
            <div class="panel__head">
                <h2>Notifikasi Baru</h2>
                <a href="{{ route('notifications.index') }}" class="link">Lihat semua</a>
            </div>
            @foreach ($recentNotifs as $notif)
                <a href="{{ route('notifications.read', $notif) }}" class="notif-inline">
                    <span class="notif-inline__dot"></span>
                    <div>
                        <strong>{{ $notif->title }}</strong>
                        <span class="muted small">
                            {{ $notif->created_at->timezone('Asia/Jakarta')->diffForHumans(short: true) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Quick actions --}}
    <div class="account-actions">
        <a href="{{ route('account.orders.index') }}" class="account-action">
            <span class="account-action__label">Pesanan Saya</span>
        </a>
        <a href="{{ route('account.coins.index') }}" class="account-action">
            <span class="account-action__label">Koin Saya</span>
        </a>
        <a href="{{ route('referral.index') }}" class="account-action">
            <span class="account-action__label">Undang Teman</span>
        </a>
        <a href="{{ route('account.profile.edit') }}" class="account-action">
            <span class="account-action__label">Edit Profil</span>
        </a>
    </div>

    {{-- Pesanan terbaru --}}
    @if (isset($recentOrders) && $recentOrders->isNotEmpty())
        <div class="panel">
            <div class="panel__head">
                <h2>Pesanan Terbaru</h2>
                <a href="{{ route('account.orders.index') }}" class="link">Lihat semua</a>
            </div>
            <ul class="order-mini-list">
                @foreach ($recentOrders as $order)
                    <li class="order-mini">
                        <a href="{{ route('account.orders.show', $order->order_number) }}" class="order-mini__link">
                            <div>
                                <strong class="mono">#{{ $order->order_number }}</strong>
                                <span class="muted small">
                                    {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}
                                </span>
                            </div>
                            <div class="order-mini__meta">
                                <strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong>
                                <span class="badge {{ $order->statusBadgeClass() }} small">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="empty">
            <p class="muted">Kamu belum punya pesanan.</p>
            <a href="{{ route('catalog.index') }}" class="button">Mulai belanja</a>
        </div>
    @endif

</section>
@endsection
