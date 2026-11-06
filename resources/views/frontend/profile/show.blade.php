@extends('layouts.app')

@section('title', 'Dashboard — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        .pf-wrap, .pf-wrap *, .pf-wrap *::before, .pf-wrap *::after { box-sizing: border-box; }
        .pf-wrap {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .pf-wrap a { text-decoration: none; }

        .pf-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 36px 40px 28px;
            background: #fff;
        }
        .pf-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }
        .pf-lead { margin: 6px 0 0; font-size: 15px; color: #6f6a72; }
        .pf-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 14px; }
        .pf-meta-item { font-size: 12px; color: #888; }

        .pf-bar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            margin-top: 16px; padding: 12px 16px;
            background: #fafafa; border: 1px solid #eee;
        }
        .pf-bar-label { font-size: 13px; color: #555; }
        .pf-bar-label strong { color: #26222f; font-weight: 700; }
        .pf-wa {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; background: #43A047; color: #fff;
            font: 700 12px/18px 'Roboto', system-ui, sans-serif;
        }
        .pf-wa:hover { background: #388E3C; }

        .pf-progress { margin-top: 16px; max-width: 420px; }
        .pf-progress-top { display: flex; justify-content: space-between; font-size: 12px; color: #888; }
        .pf-progress-track { margin-top: 6px; height: 8px; background: #eee; overflow: hidden; }
        .pf-progress-fill { height: 100%; background: #FFC107; }
        .pf-progress-note { margin: 6px 0 0; font-size: 12px; color: #888; }

        .pf-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .pf-stat {
            padding: 20px;
            background: #fff;
        }
        .pf-label { margin: 0; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #888; }
        .pf-stat-value { margin: 8px 0 0; font-size: 22px; font-weight: 700; color: #26222f; }

        .pf-tabs {
            display: flex; flex-wrap: wrap; gap: 0;
            margin-top: 28px;
            border-bottom: 1px solid #eee;
            background: #fff;
            padding: 0 20px;
        }
        .pf-tab {
            display: inline-block; padding: 14px 18px;
            font-size: 14px; color: #888;
            border-bottom: 2px solid transparent;
        }
        .pf-tab:hover { color: #26222f; }
        .pf-tab.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .pf-sect { margin-top: 28px; }
        .pf-sect-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .pf-sect-h { margin: 0; font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #26222f; }
        .pf-card { background: #fff; padding: 20px; }
        .pf-empty { text-align: center; color: #888; font-size: 14px; }
        .pf-list { display: flex; flex-direction: column; gap: 10px; }
        .pf-row {
            display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;
            gap: 12px; padding: 16px 18px; background: #fff;
        }
        .pf-row-title { margin: 0; font-weight: 700; color: #26222f; }
        .pf-row-sub { margin: 4px 0 0; font-size: 13px; color: #6f6a72; }

        .pf-modal { padding: 0; border: 0; background: #fff; max-height: 90vh; overflow-y: auto; }
        .pf-modal::backdrop { background: rgba(0,0,0,.5); }
        .pf-modal-body { padding: 32px; }
        .pf-modal-title { margin: 0; font-size: 20px; font-weight: 700; color: #26222f; }
        .pf-hr { margin: 16px 0; border: 0; border-top: 1px solid #eee; }
        .pf-ol { margin: 16px 0 0; padding: 0; list-style: none; }
        .pf-ol li { display: flex; gap: 10px; margin-bottom: 12px; font-size: 14px; line-height: 22px; }
        .pf-num { color: #888; font-weight: 700; }
        .pf-modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }

        @media (max-width: 900px) { .pf-stats { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) {
            .pf-head { padding: 24px 16px; }
            .pf-title { font-size: 26px; }
            .pf-stats { grid-template-columns: 1fr; }
            .pf-modal-body { padding: 24px 16px; }
        }
    </style>

    <div class="pf-wrap">

        <div class="pf-head">
            <div>
                <h1 class="pf-title">Dashboard Pelanggan</h1>
                <p class="pf-lead">Selamat datang, {{ $user->name }}</p>
                <div class="pf-meta">
                    <x-user-badge :value="$stateBadge" type="state" />
                    @if ($tierBadge)
                        <x-user-badge :value="$tierBadge" type="tier" />
                    @endif
                    <span class="pf-meta-item">Terdaftar sejak {{ $user->created_at->format('d F Y') }}</span>
                </div>

                @if (in_array($userState ?? '', ['crush', 'ex', 'ons']))
                    @php $kuotaHabis = ($kuotaViewUsed ?? 0) >= ($kuotaViewTotal ?? 10) @endphp
                    <div class="pf-bar">
                        <span class="pf-bar-label">
                            Kuota View: <strong>{{ $kuotaViewUsed ?? 0 }}/{{ $kuotaViewTotal ?? 10 }}</strong>
                        </span>
                        @if (!empty($countdownState))
                            <span class="pf-bar-label">{{ $countdownState }}</span>
                        @endif
                        @if ($kuotaHabis && !empty($waUrlPrefill))
                            <a href="{{ $waUrlPrefill }}" target="_blank" rel="noopener" class="pf-wa">WA</a>
                        @endif
                    </div>
                @endif

                @isset($tierProgress)
                    <div class="pf-progress">
                        <div class="pf-progress-top">
                            <span>Progress Tier</span>
                            <span>
                                @if (!empty($tierProgress['current_tier']) && !empty($tierProgress['next_tier']))
                                    {{ $tierProgress['current_tier'] }} → {{ $tierProgress['next_tier'] }}
                                @else
                                    {{ format_rp($tierProgress['belanja'] ?? 0) }} / {{ format_rp($tierProgress['platinum'] ?? 0) }}
                                @endif
                            </span>
                        </div>
                        <div class="pf-progress-track">
                            <div class="pf-progress-fill" style="width: {{ $tierProgress['progress_pct'] ?? 0 }}%"></div>
                        </div>
                        @if (($tierProgress['belanja_sisa'] ?? null) !== null)
                            <p class="pf-progress-note">Sisa {{ format_rp($tierProgress['belanja_sisa']) }} ke tier berikutnya</p>
                        @endif
                    </div>
                @endisset
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="au-btn is-ghost is-sm">Keluar</button>
            </form>
        </div>

        <div class="pf-stats">
            <div class="pf-stat">
                <p class="pf-label">Total Pesanan</p>
                <p class="pf-stat-value">{{ $totalOrders }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">Total Belanja</p>
                <p class="pf-stat-value">{{ format_rp($totalBelanja) }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">Koin Aktif</p>
                <p class="pf-stat-value">{{ number_format($coinBalance, 0, ',', '.') }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">Undang Teman</p>
                <p class="pf-stat-value">{{ $undangBerhasil }} Berhasil</p>
            </div>
        </div>

        <div class="pf-tabs">
            <a href="{{ route('dashboard') }}" class="pf-tab is-on">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}" class="pf-tab">Notifikasi</a>
            <a href="{{ route('profile.coins') }}" class="pf-tab">Koin</a>
            <a href="{{ route('profile.wallet') }}" class="pf-tab">Voucher</a>
            <a href="{{ route('profile.referral') }}" class="pf-tab">Undang Teman</a>
            <a href="{{ route('profile.edit') }}" class="pf-tab">Edit Profil</a>
        </div>

        <section class="pf-sect">
            <div class="pf-sect-head">
                <h2 class="pf-sect-h">Tagihan Menunggu ({{ $tagihan->count() }})</h2>
                <a href="{{ route('orders.index') }}" class="au-link">Lihat Semua</a>
            </div>

            @if ($tagihan->isEmpty())
                <div class="pf-card pf-empty">Tidak ada tagihan menunggu.</div>
            @else
                <div class="pf-list">
                    @foreach ($tagihan as $order)
                        <div class="pf-row">
                            <div>
                                <p class="pf-row-title">{{ $order->no_order }}</p>
                                @if ($order->status === 'menunggu_pembayaran')
                                    <p class="pf-row-sub">{{ format_rp($order->tagihan_web) }} · Menunggu Pembayaran</p>
                                @elseif ($order->status === 'menunggu_denda')
                                    <p class="pf-row-sub">Denda {{ format_rp($order->penalty_amount) }} · Menunggu Denda</p>
                                @else
                                    <p class="pf-row-sub">Menunggu Pelunasan & CO Marketplace</p>
                                @endif
                            </div>
                            <a href="{{ route('orders.show', $order->no_order) }}" class="au-btn is-sm">Lihat</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="pf-sect">
            <h2 class="pf-sect-h">Daftar Pesanan Saya</h2>
            <div class="pf-list">
                @forelse ($orders as $order)
                    <div class="pf-row">
                        <div>
                            <p class="pf-row-title">{{ $order->no_order }}</p>
                            <p class="pf-row-sub">{{ $order->created_at->format('d M Y') }} · {{ format_rp($order->total) }}</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <x-order-status-badge :status="$order->status" />
                            <a href="{{ route('orders.show', $order->no_order) }}" class="au-link">Lihat</a>
                        </div>
                    </div>
                @empty
                    <div class="pf-card pf-empty">Belum ada pesanan. <a href="/" class="au-link">Mulai belanja</a></div>
                @endforelse
            </div>
        </section>

        @if (method_exists($user, 'shouldSeeTour') && $user->shouldSeeTour())
            <dialog id="tour-modal" class="pf-modal" style="margin:auto;max-width:560px;width:calc(100% - 2rem);">
                <div class="pf-modal-body">
                    <h3 class="pf-modal-title">Baru di Mercatoria?</h3>
                    <div class="pf-hr"></div>
                    <ol class="pf-ol">
                        <li><span class="pf-num">1.</span> <span><strong>Pilih Merch IP Favoritmu</strong> — Harga sudah termasuk ongkir CN-ID & bea.</span></li>
                        <li><span class="pf-num">2.</span> <span><strong>Pilih Skema Pembayaran</strong> — Full atau DP 50% via BCA, QRIS, atau ShopeePay.</span></li>
                        <li><span class="pf-num">3.</span> <span><strong>Pilih Platform Pengiriman</strong> — Toco atau Shopee.</span></li>
                        <li><span class="pf-num">4.</span> <span><strong>Pantau Status</strong> — Dari WH China sampai WH Indonesia.</span></li>
                        <li><span class="pf-num">5.</span> <span><strong>Kuota Akun Baru</strong> — Kamu punya kuota melihat 10 detail produk.</span></li>
                    </ol>
                    <div class="pf-modal-actions">
                        <a href="{{ route('page.cara-belanja') }}" class="au-btn is-ghost is-sm">Baca Selengkapnya</a>
                        <form method="POST" action="{{ route('profile.tour-seen') }}">
                            @csrf
                            <button type="submit" class="au-btn is-sm">Mengerti</button>
                        </form>
                    </div>
                </div>
            </dialog>
            <script>
                (function () {
                    var modal = document.getElementById('tour-modal');
                    if (modal && typeof modal.showModal === 'function') modal.showModal();
                })();
            </script>
        @endif
    </div>
@endsection
