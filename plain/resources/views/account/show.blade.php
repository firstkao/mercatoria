@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
    <section class="card" style="max-width: 1000px;">
        {{-- Welcome --}}
        <div class="account-hero">
            <div>
                <p class="eyebrow">Akun saya</p>
                <h1 style="margin:4px 0 8px;">Halo, {{ $user->full_name }} 👋</h1>
                <p class="muted" style="margin:0;">
                    <span class="badge badge--{{ $user->role->value }}">{{ $user->role->label() }}</span>
                    <span class="muted" style="margin-left:8px;">Terdaftar {{ $user->registered_at?->timezone('Asia/Jakarta')->translatedFormat('F Y') ?? '—' }}</span>
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        {{-- STRICT MODE (permintaan user #2/#4): tampilkan flash 'error' dari
             redirect katalog/produk dalam alert MERAH + tombol WA. --}}
        @if (session('error'))
            <div role="alert" style="background:#fdecea;border:1px solid #e74c3c;border-radius:10px;padding:20px;margin-bottom:20px;">
                <strong style="color:#c0392b;display:block;font-size:16px;margin-bottom:8px;">
                    ⚠️ {{ session('error') }}
                </strong>
                @php($waNumErr = preg_replace('/\D/', '', $contactWhatsapp !== '' ? $contactWhatsapp : '6281219683709'))
                @if (str_starts_with($waNumErr, '0'))
                    @php($waNumErr = '62' . substr($waNumErr, 1))
                @endif
                <a href="https://wa.me/{{ $waNumErr }}" target="_blank" rel="noopener"
                   class="button button--small"
                   style="display:inline-block;background:#25D366;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;">Chat Admin via WhatsApp</a>
            </div>
        @endif

        {{-- Kuota spammer --}}
        @if ($user->isSpammer())
            @php($remaining = $user->remainingViewQuota())

            {{-- PERMINTAAN #3: kalau kuota sudah 10/10, tampilkan banner merah
                 yang SAMA seperti di katalog — instruksi chat admin via WA
                 untuk reset kuota — persis di paling atas dashboard akun. --}}
            @if ($remaining <= 0)
                <div class="quota quota--warning" role="alert" style="background:#fdecea;border:1px solid #e74c3c;border-radius:10px;padding:20px;margin-bottom:20px;">
                    <strong style="color:#c0392b;display:block;font-size:16px;margin-bottom:8px;">
                        ⚠️ Kuota lihat produk Anda telah habis ({{ $viewQuota }}/{{ $viewQuota }}).
                    </strong>
                    <p style="margin:0 0 14px;color:#7a3b34;">
                        Silakan chat admin via WhatsApp untuk melakukan reset kuota.
                        Setelah direset, kamu bisa menjelajah katalog & membuka produk lagi seperti biasa.
                    </p>
                    @php($waNum = preg_replace('/\D/', '', $contactWhatsapp ?? ''))
                    @if (str_starts_with($waNum, '0'))
                        @php($waNum = '62' . substr($waNum, 1))
                    @endif
                    @if ($waNum !== '')
                        <a href="https://wa.me/{{ $waNum }}?text={{ rawurlencode('Halo admin, kuota lihat produk saya sudah habis. Mohon reset kuota saya ya.') }}"
                           target="_blank" rel="noopener"
                           class="btn btn--primary"
                           style="display:inline-block;background:#25D366;color:#fff;padding:10px 18px;border-radius:8px;font-weight:600;text-decoration:none;">
                            💬 Chat Admin via WhatsApp
                        </a>
                    @else
                        <span class="muted small">Nomor WhatsApp admin belum diatur — hubungi admin melalui halaman Kontak.</span>
                    @endif
                </div>
            @else
                <div class="quota {{ $remaining <= 2 ? 'quota--warning' : '' }}" role="status" style="margin-bottom:20px;">
                    <strong>Sisa lihat produk: {{ $remaining }} dari {{ $viewQuota }}</strong>
                    <p>
                        Akun ini akan terhapus otomatis pada
                        {{ $user->expires_at?->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                        jika belum ada pembayaran yang terverifikasi.
                    </p>
                </div>
            @endif
        @endif

        {{-- Stat cards --}}
        <div class="account-stats">
            <div class="account-stat">
                <span class="account-stat__icon">📦</span>
                <div>
                    <span class="account-stat__label">Total Pesanan</span>
                    <strong class="account-stat__value">{{ number_format($orderStats['total'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">💰</span>
                <div>
                    <span class="account-stat__label">Total Belanja</span>
                    <strong class="account-stat__value">{{ \App\Support\PriceCalculator::formatRupiah($totalSpent) }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">🪙</span>
                <div>
                    <span class="account-stat__label">Koin Aktif</span>
                    <strong class="account-stat__value">{{ number_format($activeCoins, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="account-stat">
                <span class="account-stat__icon">🎁</span>
                <div>
                    <span class="account-stat__label">Undang Teman</span>
                    <strong class="account-stat__value">{{ $user->referralsMade()->count() }}</strong>
                </div>
            </div>
        </div>

        {{-- Tagihan Menunggu (pindahan dari navbar 'Pesanan Saya' -> dashboard akun) --}}
        @if ($unpaidOrders->isNotEmpty())
            <div class="panel stack" style="margin-top:20px; border-left:4px solid var(--accent-strong);">
                <div class="panel__head">
                    <h2 style="margin:0;">Tagihan Menunggu</h2>
                    <a href="{{ route('account.orders.index') }}" class="link">Lihat semua pesanan</a>
                </div>
                @foreach ($unpaidOrders as $u)
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:8px 0; border-top:1px solid #eee;">
                        <div>
                            <strong style="font-family:monospace;">{{ $u->order_number }}</strong>
                            <span class="muted"> · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }} ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})</span>
                            @if ($u->payment_deadline_at)
                                <div class="muted" style="font-size:12px;">Batas bayar: {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</div>
                            @endif
                        </div>
                        <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti" class="button button--small">Bayar &amp; Unggah Bukti</a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Notifikasi baru --}}
        @if ($recentNotifs->isNotEmpty())
            <div class="panel" style="margin-top:20px;">
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
            <a href="{{ route('notifications.index') }}" class="account-action">
                <span class="account-action__icon">🔔</span>
                <span class="account-action__label">Notifikasi
                    @if ($unreadNotif > 0)
                        <span class="badge badge--danger" style="margin-left:4px;">{{ $unreadNotif }}</span>
                    @endif
                </span>
            </a>
            <a href="{{ route('account.profile.edit') }}" class="account-action">
                <span class="account-action__icon">⚙️</span>
                <span class="account-action__label">Edit Profil</span>
            </a>
            {{-- Permintaan user: menu "Katalog" dihapus dari dashboard akun
                 (sudah ada di header publik, tidak perlu duplikat di sini). --}}
        </div>

        {{-- Pesanan terbaru --}}
        @if ($recentOrders->isNotEmpty())
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
            <div class="empty" style="margin-top:24px;">
                <p>Kamu belum punya pesanan.</p>
                <a href="{{ route('catalog.index') }}" class="button" style="margin-top:12px;">Mulai Belanja</a>
            </div>
        @endif
    </section>
@endsection