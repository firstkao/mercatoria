@extends('layouts.app')

@section('title', 'Dashboard — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">

        <div class="ac-head">
            <div>
                <h1 class="ac-title">Dashboard Pelanggan</h1>
                <p class="ac-lead">Selamat datang, {{ $user->name }}</p>
                <div class="ac-meta">
                    <x-user-badge :value="$stateBadge" type="state" />
                    @if ($tierBadge)
                        <x-user-badge :value="$tierBadge" type="tier" />
                    @endif
                    <span>Terdaftar sejak {{ $user->created_at->format('d F Y') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ac-btn is-ghost is-sm">Keluar</button>
            </form>
        </div>

        @if (in_array($userState ?? '', ['crush', 'ex', 'ons']))
            @php $kuotaHabis = ($kuotaViewUsed ?? 0) >= ($kuotaViewTotal ?? 10); @endphp
            <div class="ac-note">
                <span>Kuota View: <b>{{ $kuotaViewUsed ?? 0 }}/{{ $kuotaViewTotal ?? 10 }}</b></span>
                @if (!empty($countdownState))
                    <span>{{ $countdownState }}</span>
                @endif
                @if ($kuotaHabis && !empty($waUrlPrefill))
                    <a href="{{ $waUrlPrefill }}" target="_blank" rel="noopener" class="ac-btn is-wa is-sm">WA</a>
                @endif
            </div>
        @endif

        @isset($tierProgress)
            <div class="ac-progress">
                <div class="ac-progress-top">
                    <span>Progress Tier</span>
                    <span>
                        @if (!empty($tierProgress['current_tier']) && !empty($tierProgress['next_tier']))
                            {{ $tierProgress['current_tier'] }} → {{ $tierProgress['next_tier'] }}
                        @else
                            {{ format_rp($tierProgress['belanja'] ?? 0) }} / {{ format_rp($tierProgress['platinum'] ?? 0) }}
                        @endif
                    </span>
                </div>
                <div class="ac-progress-track">
                    <div class="ac-progress-fill" style="width: {{ $tierProgress['progress_pct'] ?? 0 }}%"></div>
                </div>
                @if (($tierProgress['belanja_sisa'] ?? null) !== null)
                    <p class="ac-progress-note">Sisa {{ format_rp($tierProgress['belanja_sisa']) }} ke tier berikutnya</p>
                @endif
            </div>
        @endisset

        <div class="ac-stats">
            <div class="ac-stat">
                <p class="ac-stat-l">Total Pesanan</p>
                <p class="ac-stat-v">{{ $totalOrders }}</p>
            </div>
            <div class="ac-stat">
                <p class="ac-stat-l">Total Belanja</p>
                <p class="ac-stat-v">{{ format_rp($totalBelanja) }}</p>
            </div>
            <div class="ac-stat">
                <p class="ac-stat-l">Koin Aktif</p>
                <p class="ac-stat-v">{{ number_format($coinBalance, 0, ',', '.') }}</p>
            </div>
            <div class="ac-stat">
                <p class="ac-stat-l">Undang Teman</p>
                <p class="ac-stat-v">{{ $undangBerhasil }} Berhasil</p>
            </div>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'orders'])

        <section class="ac-sec">
            <div class="ac-sec-h">
                <h2>Tagihan Menunggu ({{ $tagihan->count() }})</h2>
                <a href="{{ route('orders.index') }}" class="ac-link">Lihat Semua</a>
            </div>

            @if ($tagihan->isEmpty())
                <div class="ac-empty">Tidak ada tagihan menunggu.</div>
            @else
                <div class="ac-rows">
                    @foreach ($tagihan as $order)
                        <div class="ac-row">
                            <div>
                                <p class="ac-row-t">{{ $order->no_order }}</p>
                                @if ($order->status === 'menunggu_pembayaran')
                                    <p class="ac-row-s">{{ format_rp($order->tagihan_web) }} · Menunggu Pembayaran</p>
                                @elseif ($order->status === 'menunggu_denda')
                                    <p class="ac-row-s">Denda {{ format_rp($order->penalty_amount) }} · Menunggu Denda</p>
                                @else
                                    <p class="ac-row-s">Menunggu Pelunasan & CO Marketplace</p>
                                @endif
                            </div>
                            <a href="{{ route('orders.show', $order->no_order) }}" class="ac-btn is-sm">Lihat</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Daftar Pesanan Saya</h2></div>
            @forelse ($orders as $order)
                @if ($loop->first)<div class="ac-rows">@endif
                <div class="ac-row">
                    <div>
                        <p class="ac-row-t">{{ $order->no_order }}</p>
                        <p class="ac-row-s">{{ $order->created_at->format('d M Y') }} · {{ format_rp($order->total) }}</p>
                    </div>
                    <div class="ac-row-end">
                        <x-order-status-badge :status="$order->status" />
                        <a href="{{ route('orders.show', $order->no_order) }}" class="ac-link">Lihat</a>
                    </div>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <div class="ac-empty">Belum ada pesanan. <a href="/" class="ac-link">Mulai belanja</a></div>
            @endforelse
        </section>

        @if (method_exists($user, 'shouldSeeTour') && $user->shouldSeeTour())
            <dialog id="tour-modal" class="ac-modal">
                <div class="ac-modal-body">
                    <h3 class="ac-modal-title">Baru di Mercatoria?</h3>
                    <ol class="ac-ol">
                        <li><span class="ac-num">1.</span> <span><b>Pilih Merch IP Favoritmu</b> — Harga sudah termasuk ongkir CN-ID & bea.</span></li>
                        <li><span class="ac-num">2.</span> <span><b>Pilih Skema Pembayaran</b> — Full atau DP 50% via BCA, QRIS, atau ShopeePay.</span></li>
                        <li><span class="ac-num">3.</span> <span><b>Pilih Platform Pengiriman</b> — Toco atau Shopee.</span></li>
                        <li><span class="ac-num">4.</span> <span><b>Pantau Status</b> — Dari WH China sampai WH Indonesia.</span></li>
                        <li><span class="ac-num">5.</span> <span><b>Kuota Akun Baru</b> — Kamu punya kuota melihat 10 detail produk.</span></li>
                    </ol>
                    <div class="ac-modal-actions">
                        <a href="{{ route('page.cara-belanja') }}" class="ac-btn is-ghost is-sm">Baca Selengkapnya</a>
                        <form method="POST" action="{{ route('profile.tour-seen') }}">
                            @csrf
                            <button type="submit" class="ac-btn is-sm">Mengerti</button>
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
