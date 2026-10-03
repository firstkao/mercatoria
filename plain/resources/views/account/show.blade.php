@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
    <section class="card" style="max-width: 1000px;">
        {{-- Welcome --}}
        <div class="account-hero">
            <div>
                <p class="eyebrow">Akun saya</p>
                <h1 style="margin:4px 0 8px;">Halo, {{ $user->full_name ?? 'User' }} 👋</h1>
                <p class="muted" style="margin:0;">
                    <span class="badge badge--{{ $user->role?->value ?? 'default' }}">{{ $user->role?->label() ?? 'User' }}</span>
                    <span class="muted" style="margin-left:8px;">Terdaftar {{ $user->registered_at?->timezone('Asia/Jakarta')->translatedFormat('F Y') ?? '—' }}</span>
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        {{-- Kuota spammer --}}
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
                    // Fallback jika nomor belum diatur di database
                    if ($waNum === '') {
                        $waNum = '6281219683709';
                    }
                    // Email hanya ada di URL, tidak ditampilkan di UI
                    $waMessage = rawurlencode(
                        "Halo Admin,\n" .
                        "Saya ingin meminta reset kuota lihat produk karena sudah mencapai batas.\n" .
                        "Email akun saya: " . ($user->email ?? '') . "\n" .
                        "Mohon dibantu, terima kasih."
                    );
                @endphp

                {{-- Banner kuota habis (merah = peringatan) --}}
                <div class="quota-banner" role="alert" style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 10px; padding: 24px; margin-bottom: 24px; text-align: center;">
                    <strong style="color: #991b1b; display: block; font-size: 16px; margin-bottom: 8px;">
                        ⚠️ Kuota lihat produk Anda telah habis.
                    </strong>
                    <p style="margin: 0 0 16px; color: #b91c1c; font-size: 14px; line-height: 1.5;">
                        Silakan hubungi admin melalui WhatsApp untuk melakukan reset kuota agar Anda dapat kembali menjelajah katalog.
                    </p>
                    <a href="https://wa.me/{{ $waNum }}?text={{ $waMessage }}"
                       target="_blank" rel="noopener"
                       style="display: inline-block; background: #25D366; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.2s;">
                        💬 Chat Admin via WhatsApp
                    </a>
                </div>
            @else
                {{-- Banner sisa kuota --}}
                <div class="quota {{ $remaining <= 2 ? 'quota--warning' : '' }}" role="status" style="margin-bottom: 24px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 16px 20px;">
                    <strong style="color: #0369a1; display: block; margin-bottom: 4px;">Sisa lihat produk: {{ $remaining }} dari {{ $viewQuota }}</strong>
                    @if ($user->expires_at)
                        <p style="margin: 0; color: #0c4a6e; font-size: 14px;">
                            Akun ini akan terhapus otomatis pada
                            {{ $user->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                            jika belum ada pembayaran yang terverifikasi.
                        </p>
                    @endif
                </div>
            @endif
        @endif

        {{-- Stat cards --}}
        <div class="account-stats">
            <div class="account-stat">
                <span class="account-stat__icon">📦</span>
                <div>
                    <span class="account-stat__label">Total Pesanan</span>
                    <strong class="account-stat__value">{{ number_format($orderStats['total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">💰</span>
                <div>
                    <span class="account-stat__label">Total Belanja</span>
                    <strong class="account-stat__value">{{ \App\Support\PriceCalculator::formatRupiah($totalSpent ?? 0) }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">🪙</span>
                <div>
                    <span class="account-stat__label">Koin Aktif</span>
                    <strong class="account-stat__value">{{ number_format($activeCoins ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">🎁</span>
                <div>
                    <span class="account-stat__label">Undang Teman</span>
                    {{-- Idealnya $referralCount dikirim dari controller; fallback ke query kalau belum --}}
                    <strong class="account-stat__value">{{ $referralCount ?? $user->referralsMade()->count() }}</strong>
                </div>
            </div>
        </div>

        {{-- Tagihan Menunggu --}}
        @if (isset($unpaidOrders) && $unpaidOrders->isNotEmpty())
            <div class="panel stack" style="margin-top:24px; border-left:4px solid var(--accent-strong);">
                <div class="panel__head">
                    <h2 style="margin:0;">Tagihan Menunggu</h2>
                    <a href="{{ route('account.orders.index') }}" class="link">Lihat semua pesanan</a>
                </div>
                @foreach ($unpaidOrders as $u)
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:12px 0; border-top:1px solid #eee;">
                        <div>
                            <strong style="font-family:monospace;">{{ $u->order_number }}</strong>
                            <span class="muted"> · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }} ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})</span>
                            @if ($u->payment_deadline_at)
                                <div class="muted" style="font-size:12px; margin-top:4px;">Batas bayar: {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</div>
                            @endif
                        </div>
                        <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti" class="button button--small">Bayar &amp; Unggah Bukti</a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Notifikasi baru --}}
        @if (Route::has('notifications.index') && isset($recentNotifs) && $recentNotifs->isNotEmpty())
            <div class="panel" style="margin-top:24px;">
                <div class="panel__head">
                    <h2 style="margin:0;">Notifikasi Baru</h2>
                    <a href="{{ route('notifications.index') }}" class="link">Lihat semua</a>
                </div>
                @foreach ($recentNotifs as $notif)
                    <a href="{{ route('notifications.read', $notif) }}" class="notif-inline">
                        <span class="notif-inline__dot"></span>
                        <div>
                            <strong>{{ $notif->title }}</strong>
                            <span class="muted small">{{ $notif->created_at->timezone('Asia/Jakarta')->diffForHumans(short: true) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Quick actions --}}
        <div class="account-actions">
            <a href="{{ route('account.orders.index') }}" class="account-action">
                <span class="account-action__icon">📋</span>
                <span class="account-action__label">Pesanan Saya</span>
            </a>
            <a href="{{ route('account.coins.index') }}" class="account-action">
                <span class="account-action__icon">🪙</span>
                <span class="account-action__label">Koin Saya</span>
            </a>
            <a href="{{ route('referral.index') }}" class="account-action">
                <span class="account-action__icon">🎁</span>
                <span class="account-action__label">Undang Teman</span>
            </a>
            @if (Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}" class="account-action">
                    <span class="account-action__icon">🔔</span>
                    <span class="account-action__label">Notifikasi
                        @if (($unreadNotif ?? 0) > 0)
                            <span class="badge badge--danger" style="margin-left:4px;">{{ $unreadNotif }}</span>
                        @endif
                    </span>
                </a>
            @endif
            <a href="{{ route('account.profile.edit') }}" class="account-action">
                <span class="account-action__icon">⚙️</span>
                <span class="account-action__label">Edit Profil</span>
            </a>
        </div>

        {{-- Pesanan terbaru --}}
        @if (isset($recentOrders) && $recentOrders->isNotEmpty())
            <div class="panel" style="margin-top:24px;">
                <div class="panel__head">
                    <h2 style="margin:0;">Pesanan Terbaru</h2>
                    <a href="{{ route('account.orders.index') }}" class="link">Lihat semua</a>
                </div>
                <ul class="order-mini-list">
                    @foreach ($recentOrders as $order)
                        <li class="order-mini">
                            <a href="{{ route('account.orders.show', $order->order_number) }}" class="order-mini__link">
                                <div>
                                    <strong style="font-family:monospace;">#{{ $order->order_number }}</strong>
                                    <span class="muted small">{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</span>
                                </div>
                                <div style="text-align:right;">
                                    <strong style="color:var(--accent-strong);">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong>
                                    <span class="badge {{ $order->statusBadgeClass() }} small" style="display:block;margin-top:4px;">{{ $order->statusLabel() }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="empty" style="margin-top:24px; text-align: center; padding: 32px 0;">
                <p class="muted">Kamu belum punya pesanan.</p>
                <a href="{{ route('catalog.index') }}" class="button" style="margin-top:12px;">Mulai Belanja</a>
            </div>
        @endif
    </section>
@endsection
