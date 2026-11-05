@extends('layouts.app')

@section('title', 'Dashboard Pelanggan — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Dashboard Pelanggan — CSS biasa, scope .pf-* (tidak bergantung build Tailwind). */
        .pf-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .pf-head-main { min-width: 0; }

        .pf-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 12px; }
        .pf-meta-item { font-size: 11px; line-height: 16px; letter-spacing: .06em; text-transform: uppercase; color: #888; }

        .pf-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 14px; padding: 10px 14px;
                  background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .pf-bar-plain { margin-top: 14px; }
        .pf-bar-label { font-size: 11px; line-height: 16px; letter-spacing: .06em; text-transform: uppercase; color: #555; }
        .pf-bar-label strong { color: #000; font-weight: 700; }
        .pf-bar-note { font-size: 12px; line-height: 18px; color: #888; }
        .pf-wa { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #43A047; color: #fff;
                 font: 700 12px/18px 'Roboto', system-ui, sans-serif; text-decoration: none; }
        .pf-wa:hover { background: #388E3C; }

        .pf-progress { margin-top: 14px; max-width: 420px; }
        .pf-progress-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .pf-progress-val { font-size: 11px; line-height: 16px; letter-spacing: .06em; text-transform: uppercase; color: #888; }
        .pf-progress-track { margin-top: 6px; height: 8px; overflow: hidden; background: #e1e1e1; }
        .pf-progress-fill { height: 100%; background: #FFC107; }
        .pf-progress-note { margin: 6px 0 0; font-size: 11px; line-height: 16px; color: #888; }

        .pf-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-top: 20px; }
        .pf-stat { padding: 16px 18px 18px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .pf-label { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .pf-stat-value { margin: 8px 0 0; font-size: 22px; line-height: 28px; font-weight: 700; color: #000; }

        .pf-tabs { display: flex; flex-wrap: wrap; gap: 0; margin: 26px 0 0; border-bottom: 1px solid #e1e1e1; }
        .pf-tab { display: inline-block; padding: 11px 16px; margin-bottom: -1px; border-bottom: 2px solid transparent;
                  font: 400 13px/18px 'Roboto', system-ui, sans-serif; color: #888; text-decoration: none; transition: color .15s; }
        .pf-tab:hover { color: #000; }
        .pf-tab.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .pf-sect { margin-top: 28px; }
        .pf-sect-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .pf-sect-h { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 11px; line-height: 16px;
                     font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }

        .pf-card { background: #fff; border: 1px solid #e1e1e1; border-radius: 0; padding: 18px 20px; }
        .pf-card.pf-empty { margin-top: 12px; text-align: center; font-size: 13px; line-height: 20px; color: #888; }

        .pf-list { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
        .pf-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
                  padding: 14px 16px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .pf-row-main { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 13px; line-height: 19px; }
        .pf-row-title { margin: 0; font-size: 13px; line-height: 19px; font-weight: 700; color: #000; }
        .pf-row-sub { margin: 4px 0 0; font-size: 12px; line-height: 18px; color: #555; }

        .pf-modal { padding: 0; border: 1px solid #e1e1e1; border-radius: 0; background: #fff; color: #333;
                    max-height: 90vh; overflow-y: auto; }
        .pf-modal::backdrop { background: rgba(0, 0, 0, .5); }
        .pf-modal-body { padding: 28px 32px 32px; }
        .pf-modal-title { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 18px; line-height: 24px; font-weight: 700; color: #000; }
        .pf-hr { height: 0; margin: 16px 0 0; border: 0; border-top: 1px solid #e1e1e1; }
        .pf-ol { display: flex; flex-direction: column; gap: 12px; margin: 18px 0 0; padding: 0; list-style: none; }
        .pf-ol li { display: flex; gap: 10px; font-size: 13px; line-height: 20px; color: #333; }
        .pf-ol .pf-num { flex: none; font-size: 11px; line-height: 20px; color: #888; }
        .pf-ol strong { color: #000; font-weight: 700; }
        .pf-modal-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 24px; }

        @media (max-width: 720px) { .pf-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) {
            .pf-stats { grid-template-columns: 1fr; }
            .pf-modal-body { padding: 22px 18px 26px; }
        }
    </style>

    <div class="au">

        {{-- Header Dashboard (PRD 7.16 / Design Spec 10.1) --}}
        <div class="pf-head">
            <div class="pf-head-main">
                <p class="au-eyebrow">Akun</p>
                <h1 class="au-title">Dashboard Pelanggan</h1>
                <p class="au-lead">Selamat datang, {{ $user->name }}</p>
                <div class="pf-meta">
                    <x-user-badge :value="$stateBadge" type="state" />
                    @if ($tierBadge)
                        <x-user-badge :value="$tierBadge" type="tier" />
                    @endif
                    <span class="pf-meta-item">Terdaftar sejak: {{ $user->created_at->format('d F Y') }}</span>
                </div>

                {{-- Kuota View + Countdown + WA (DS 10.1: Crush/Ex/ONS) --}}
                @if (in_array($userState ?? '', ['crush', 'ex', 'ons']))
                    @php $kuotaHabis = ($kuotaViewUsed ?? 0) >= ($kuotaViewTotal ?? 10) @endphp
                    <div class="pf-bar">
                        <span class="pf-bar-label">
                            Kuota View: <strong>{{ $kuotaViewUsed ?? 0 }}/{{ $kuotaViewTotal ?? 10 }}</strong>
                        </span>
                        @if (!empty($countdownState))
                            <span class="pf-bar-note">{{ $countdownState }}</span>
                        @endif
                        @if ($kuotaHabis && !empty($waUrlPrefill))
                            <a href="{{ $waUrlPrefill }}" target="_blank" rel="noopener" class="pf-wa">
                                <x-icons.chat-bubble width="14" height="14" />
                                WA
                            </a>
                        @endif
                    </div>
                @endif
                @if (($userState ?? '') === 'blocked' && !empty($waUrlPrefill))
                    <div class="pf-bar-plain">
                        <a href="{{ $waUrlPrefill }}" target="_blank" rel="noopener" class="pf-wa">
                            <x-icons.chat-bubble width="14" height="14" />
                            WA
                        </a>
                    </div>
                @endif
                @isset($tierProgress)
                    <div class="pf-progress">
                        <div class="pf-progress-top">
                            <span class="pf-label">Progress Tier</span>
                            <span class="pf-progress-val">
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

            <div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="au-btn is-ghost is-sm">Keluar</button>
                </form>
            </div>
        </div>

        {{-- Info Bar 4 Stats (PRD 7.16) --}}
        <div class="pf-stats">
            <div class="pf-stat">
                <p class="pf-label">TOTAL PESANAN</p>
                <p class="pf-stat-value">{{ $totalOrders }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">TOTAL BELANJA</p>
                <p class="pf-stat-value">{{ format_rp($totalBelanja) }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">KOIN AKTIF</p>
                <p class="pf-stat-value">{{ number_format($coinBalance, 0, ',', '.') }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">UNDANG TEMAN</p>
                <p class="pf-stat-value">{{ $undangBerhasil }} Berhasil</p>
            </div>
        </div>

        {{-- Tab Layout 4 tab (PRD 7.16) --}}
        <div class="pf-tabs">
            <a href="{{ route('dashboard') }}" class="pf-tab is-on">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}" class="pf-tab">Notifikasi</a>
            <a href="{{ route('profile.coins') }}" class="pf-tab">Koin</a>
            <a href="{{ route('profile.wallet') }}" class="pf-tab">Voucher</a>
            <a href="{{ route('profile.referral') }}" class="pf-tab">Undang Teman</a>
            <a href="{{ route('profile.edit') }}" class="pf-tab">Edit Profil</a>
        </div>

        {{-- Tagihan Menunggu (PRD 18.4) --}}
        <section class="pf-sect">
            <div class="pf-sect-head">
                <h2 class="pf-sect-h">
                    <x-icons.exclamation-circle width="14" height="14" class="text-ink/60" />
                    TAGIHAN MENUNGGU ({{ $tagihan->count() }})
                </h2>
                <a href="{{ route('orders.index') }}" class="au-link">Lihat Semua</a>
            </div>

            @if ($tagihan->isEmpty())
                <div class="pf-card pf-empty">
                    Tidak ada tagihan menunggu.
                </div>
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
                                    <p class="pf-row-sub">
                                        Menunggu Pelunasan &amp; CO Marketplace
                                        @if ($order->pickup_deadline_at)
                                            @php
                                                $sisa = now()->diff($order->pickup_deadline_at);
                                                $sisaText = sprintf('%02d Hari %02d Jam', (int) $sisa->days, (int) $sisa->h);
                                            @endphp
                                            · <x-icons.clock width="12" height="12" class="inline align-[-2px]" />
                                            Sisa Waktu CO: {{ $sisaText }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            @if ($order->status === 'menunggu_pembayaran')
                                <a href="{{ route('orders.show', $order->no_order) }}" class="au-btn is-sm">Bayar &amp; Unggah Bukti</a>
                            @elseif ($order->status === 'menunggu_denda')
                                <a href="{{ route('orders.show', $order->no_order) }}" class="au-btn is-sm">Bayar &amp; Unggah Bukti Denda</a>
                            @else
                                <a href="{{ route('orders.show', $order->no_order) }}" class="au-btn is-ghost is-sm">Lihat</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Daftar Pesanan Saya --}}
        <section class="pf-sect">
            <h2 class="pf-sect-h">DAFTAR PESANAN SAYA</h2>
            <div class="pf-list">
                @forelse ($orders as $order)
                    <div class="pf-row">
                        <div class="pf-row-main">
                            <span class="pf-row-title">{{ $order->no_order }}</span>
                            <span class="pf-row-sub">· {{ $order->created_at->format('d M Y') }} · {{ format_rp($order->total) }}</span>
                            <x-order-status-badge :status="$order->status" />
                        </div>
                        <a href="{{ route('orders.show', $order->no_order) }}" class="au-link">Lihat</a>
                    </div>
                @empty
                    <div class="pf-card pf-empty">
                        Belum ada pesanan. <a href="/" class="au-link">Mulai belanja</a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Modal Edukasi Cara Belanja (PRD 7.21 / 15) — 1x seumur hidup --}}
        @if (method_exists($user, 'shouldSeeTour') && $user->shouldSeeTour())
            <dialog id="tour-modal" class="pf-modal" style="margin:auto;max-width:560px;width:calc(100% - 2rem);">
                <div class="pf-modal-body">
                    <p class="pf-modal-title">
                        <x-icons.sparkles width="20" height="20" class="text-accent" />
                        Baru di Mercatoria? Ini Cara Belanja di Mercatoria
                    </p>
                    <div class="pf-hr"></div>
                    <ol class="pf-ol">
                        <li>
                            <span class="pf-num">1.</span>
                            <span><strong>Pilih Merch IP Favoritmu</strong> — Semua harga yang tertera adalah HARGA BERSIH (sudah termasuk ongkir CN-ID &amp; bea).</span>
                        </li>
                        <li>
                            <span class="pf-num">2.</span>
                            <span><strong>Pilih Skema Pembayaran</strong> — Bisa Full Payment (100%) atau Down Payment (DP 50%) via BCA, QRIS, atau ShopeePay.</span>
                        </li>
                        <li>
                            <span class="pf-num">3.</span>
                            <span><strong>Pilih Platform Pengiriman Lokal</strong> — Toco atau Shopee.</span>
                        </li>
                        <li>
                            <span class="pf-num">4.</span>
                            <span><strong>Pantau Status Real-time</strong> — Barang diproses dari WH China hingga sampai WH Indonesia (pelunasan &amp; ambil ≤ 7 hari).</span>
                        </li>
                        <li>
                            <span class="pf-num">5.</span>
                            <span><strong>Kuota Akun Baru (Crush)</strong> — Kamu punya kuota melihat 10 detail produk. Order pertamamu langsung upgrade ke MVP!</span>
                        </li>
                    </ol>
                    <div class="pf-modal-actions">
                        <a href="{{ route('page.cara-belanja') }}" class="au-btn is-ghost is-sm">Baca Selengkapnya</a>
                        <form method="POST" action="{{ route('profile.tour-seen') }}" class="inline">
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
