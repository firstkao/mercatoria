@extends('layouts.app', ['title' => 'Dashboard Saya'])

@section('content')
@php
    $mpName  = '';
    $isToco  = false;
@endphp

<div class="account-page">
    <div class="account-card">

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
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="account-btn account-btn--ghost account-btn--sm">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

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

        {{-- ========== KUOTA KHUSUS SPAMMER ========== --}}
        @if ($user->isSpammer())
            @php
                $remaining = $user->remainingViewQuota() ?? 0;
                $viewQuotaMax = $viewQuota['max'] ?? ($viewQuota ?? 10);
                if (is_array($viewQuota)) {
                    $viewQuotaMax = $viewQuota['max'] ?? 10;
                }
                $pct = $viewQuotaMax > 0 ? max(0, min(100, ($remaining / $viewQuotaMax) * 100)) : 0;
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
                        <span class="quota-panel__value"><strong>0</strong> / {{ $viewQuotaMax }}</span>
                    </div>
                    <div class="quota-bar"><div class="quota-bar__fill" style="width:0%"></div></div>
                    <p class="quota-panel__hint">Hubungi admin lewat WhatsApp untuk reset kuota.</p>
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
                            <strong>{{ $remaining }}</strong> / {{ $viewQuotaMax }}
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

        {{-- ========== METRICS ========== --}}
        <div class="metrics"
             data-dashboard-stats
             data-stats-url="{{ route('account.stats') }}">
            <div class="metrics__item">
                <span class="metrics__label">Total Pesanan</span>
                <strong class="metrics__value" data-stat="total_orders">
                    {{ number_format($orderStats['total'] ?? 0, 0, ',', '.') }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Total Belanja</span>
                <strong class="metrics__value" data-stat="total_spent">
                    {{ \App\Support\PriceCalculator::formatRupiah($totalSpent ?? 0) }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Koin Aktif</span>
                <strong class="metrics__value" data-stat="active_coins">
                    {{ number_format($activeCoins ?? 0, 0, ',', '.') }}
                </strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Undang Teman</span>
                <strong class="metrics__value" data-stat="referral_count">
                    {{ $referralCount ?? 0 }}
                </strong>
            </div>
        </div>

        {{-- ========== QUICK NAV ========== --}}
        <nav class="quick-nav">
            <a href="{{ route('account.orders.index') }}" class="quick-nav__item">
                <span>Pesanan Saya</span>
            </a>
            <a href="{{ route('account.coins.index') }}" class="quick-nav__item">
                <span>Koin Saya</span>
            </a>
            <a href="{{ route('referral.index') }}" class="quick-nav__item">
                <span>Undang Teman</span>
            </a>
            <a href="{{ route('account.profile.edit') }}" class="quick-nav__item">
                <span>Edit Profil</span>
            </a>
        </nav>

        {{-- ========== TAGIHAN MENUNGGU ========== --}}
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

        {{-- ========== NOTIFIKASI BARU ========== --}}
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

        {{-- ========== PESANAN TERBARU / EMPTY ========== --}}
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

@push('scripts')
<script>
(function () {
    var root = document.querySelector('[data-dashboard-stats]');
    if (! root) return;

    var url = root.dataset.statsUrl;
    var inflight = null;

    function setValue(key, val) {
        var el = root.querySelector('[data-stat="' + key + '"]');
        if (! el) return;
        if (el.textContent.trim() === String(val)) return;
        el.textContent = val;
        el.classList.add('is-updated');
        setTimeout(function () { el.classList.remove('is-updated'); }, 900);
    }

    function refresh() {
        if (inflight) return inflight;   // hindari request ganda

        inflight = fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function (res) {
            if (! res.ok) return null;
            return res.json();
        })
        .then(function (data) {
            if (! data) return;
            setValue('total_orders',   data.total_orders);
            setValue('total_spent',    data.total_spent);
            setValue('active_coins',   data.active_coins);
            setValue('referral_count', data.referral_count);
        })
        .catch(function () { /* silent — biarkan angka lama tampil */ })
        .then(function () { inflight = null; });

        return inflight;
    }

    // 1. Update saat user balik ke tab ini (dari tab lain / app lain)
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refresh();
    });

    // 2. Update saat window dapat focus lagi
    window.addEventListener('focus', refresh);

    // 3. Update saat bfcache restore (user tekan tombol back)
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) refresh();
    });

    // 4. Safety net: tiap 60 detik sambil tab kelihatan
    setInterval(function () {
        if (document.visibilityState === 'visible') refresh();
    }, 60000);
})();
</script>
@endpush
