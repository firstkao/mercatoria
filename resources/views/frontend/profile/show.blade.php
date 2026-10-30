@extends('layouts.app')

@section('title', 'Dashboard Pelanggan — Mercatoria')

@section('content')
    {{-- Header Dashboard (PRD 7.16 / Design Spec 10.1) --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Dashboard Pelanggan</h1>
            <p class="mt-1 text-[15px] text-ink/80">Selamat datang, {{ $user->name }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-user-badge :value="$stateBadge" type="state" />
                @if ($tierBadge)
                    <x-user-badge :value="$tierBadge" type="tier" />
                @endif
                <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">Terdaftar sejak: {{ $user->created_at->format('d F Y') }}</span>
            </div>

            {{-- Kuota View + Countdown + WA (DS 10.1: Crush/Ex/ONS) --}}
            @if (in_array($userState ?? '', ['crush', 'ex', 'ons']))
                @php $kuotaHabis = ($kuotaViewUsed ?? 0) >= ($kuotaViewTotal ?? 10) @endphp
                <div class="mt-3 flex flex-wrap items-center gap-3 rounded-lg border border-rule bg-white px-3 py-2">
                    <span class="font-mono text-[11px] uppercase tracking-[0.12em] text-ink/70">
                        Kuota View: <strong class="text-ink">{{ $kuotaViewUsed ?? 0 }}/{{ $kuotaViewTotal ?? 10 }}</strong>
                    </span>
                    @if (!empty($countdownState))
                        <span class="text-xs text-ink/60">{{ $countdownState }}</span>
                    @endif
                    @if ($kuotaHabis && !empty($waUrl))
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-[#43A047] px-3.5 py-1.5 text-xs font-semibold text-white transition-colors hover:opacity-90">
                            <x-icons.chat-bubble width="14" height="14" />
                            WA
                        </a>
                    @endif
                </div>
            @endif
            @isset($tierProgress)
                <div class="mt-3 w-full max-w-md">
                    <div class="flex items-center justify-between font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
                        <span>Progress Tier</span>
                        <span>
                            @if (!empty($tierProgress['current_tier']) && !empty($tierProgress['next_tier']))
                                {{ $tierProgress['current_tier'] }} → {{ $tierProgress['next_tier'] }}
                            @else
                                {{ format_rp($tierProgress['belanja'] ?? 0) }} / {{ format_rp($tierProgress['platinum'] ?? 0) }}
                            @endif
                        </span>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-rule/60">
                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $tierProgress['progress_pct'] ?? 0 }}%"></div>
                    </div>
                    @if (($tierProgress['belanja_sisa'] ?? null) !== null)
                        <p class="mt-1 font-mono text-[10px] text-ink/45">Sisa {{ format_rp($tierProgress['belanja_sisa']) }} ke tier berikutnya</p>
                    @endif
                </div>
            @endisset
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                    Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- Info Bar 4 Stats (PRD 7.16) --}}
    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">TOTAL PESANAN</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ $totalOrders }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">TOTAL BELANJA</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ format_rp($totalBelanja) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">KOIN AKTIF</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($coinBalance, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">UNDANG TEMAN</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ $undangBerhasil }} Berhasil</p>
        </div>
    </div>

    {{-- Tab Layout 4 tab (PRD 7.16) --}}
    <div class="mt-6 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.notifications') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="{{ route('profile.wallet') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    {{-- Tagihan Menunggu (PRD 18.4) --}}
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                <x-icons.exclamation-circle width="14" height="14" class="text-ink/60" />
                TAGIHAN MENUNGGU ({{ $tagihan->count() }})
            </h2>
            <a href="{{ route('orders.index') }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat Semua</a>
        </div>

        @if ($tagihan->isEmpty())
            <div class="mt-3 rounded-xl border border-rule bg-white p-6 text-center text-sm text-ink/45">
                Tidak ada tagihan menunggu.
            </div>
        @else
            <div class="mt-3 space-y-3">
                @foreach ($tagihan as $order)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rule bg-white p-4">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $order->no_order }}</p>
                            @if ($order->status === 'menunggu_pembayaran')
                                <p class="text-xs text-ink/60">{{ format_rp($order->tagihan_web) }} · Menunggu Pembayaran</p>
                            @else
                                <p class="text-xs text-ink/60">
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
                            <a href="{{ route('orders.show', $order->no_order) }}"
                               class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink">
                                Bayar &amp; Unggah Bukti
                            </a>
                        @else
                            <a href="{{ route('orders.show', $order->no_order) }}"
                               class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                                Lihat
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Daftar Pesanan Saya --}}
    <section class="mt-8">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">DAFTAR PESANAN SAYA</h2>
        <div class="mt-3 space-y-2">
            @forelse ($orders as $order)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rule bg-white px-4 py-3">
                    <div class="text-sm">
                        <span class="font-semibold text-ink">{{ $order->no_order }}</span>
                        <span class="text-ink/50">· {{ $order->created_at->format('d M Y') }} · {{ format_rp($order->total) }}</span>
                        <x-order-status-badge :status="$order->status" />
                    </div>
                    <a href="{{ route('orders.show', $order->no_order) }}" class="text-xs font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat</a>
                </div>
            @empty
                <div class="rounded-xl border border-rule bg-white p-6 text-center text-sm text-ink/45">
                    Belum ada pesanan. <a href="/" class="font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Modal Edukasi Cara Belanja (PRD 7.21 / 15) — 1x seumur hidup --}}
    @if (method_exists($user, 'shouldSeeTour') && $user->shouldSeeTour())
        <dialog id="tour-modal" class="rounded-xl border border-rule bg-white p-0" style="margin:auto;max-width:560px;width:calc(100% - 2rem);">
            <div class="p-6 sm:p-8">
                <p class="flex items-center gap-2 font-display text-xl font-semibold text-ink">
                    <x-icons.sparkles width="20" height="20" class="text-accent" />
                    Baru di Mercatoria? Ini Cara Belanja di Mercatoria
                </p>
                <div class="mt-4 h-px bg-rule"></div>
                <ol class="mt-5 space-y-3 text-sm leading-relaxed text-ink/85">
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">1.</span>
                        <span><strong class="text-ink">Pilih Merch IP Favoritmu</strong> — Semua harga yang tertera adalah HARGA BERSIH (sudah termasuk ongkir CN-ID &amp; bea).</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">2.</span>
                        <span><strong class="text-ink">Pilih Skema Pembayaran</strong> — Bisa Full Payment (100%) atau Down Payment (DP 50%) via BCA, QRIS, atau ShopeePay.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">3.</span>
                        <span><strong class="text-ink">Pilih Platform Pengiriman Lokal</strong> — Toco atau Shopee.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">4.</span>
                        <span><strong class="text-ink">Pantau Status Real-time</strong> — Barang diproses dari WH China hingga sampai WH Indonesia (pelunasan &amp; ambil ≤ 7 hari).</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">5.</span>
                        <span><strong class="text-ink">Kuota Akun Baru (Crush)</strong> — Kamu punya kuota melihat 10 detail produk. Order pertamamu langsung upgrade ke MVP!</span>
                    </li>
                </ol>
                <div class="mt-7 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('page.cara-belanja') }}" class="rounded-lg border border-rule px-5 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">
                        Baca Selengkapnya
                    </a>
                    <form method="POST" action="{{ route('profile.tour-seen') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90">
                            Mengerti
                        </button>
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
@endsection
