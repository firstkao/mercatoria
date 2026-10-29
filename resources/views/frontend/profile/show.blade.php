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
                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $stateBadge }}</span>
                @if ($tierBadge)
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $tierBadge }}</span>
                @endif
                <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">Terdaftar sejak: {{ $user->created_at->format('d F Y') }}</span>
            </div>
            @if ($tierBadge)
                @php
                    $pct = $tierProgress['platinum'] > 0
                        ? min(100, round($tierProgress['belanja'] / $tierProgress['platinum'] * 100))
                        : 0;
                @endphp
                <div class="mt-3 w-full max-w-md">
                    <div class="flex items-center justify-between font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
                        <span>Progress Tier</span>
                        <span>{{ format_rp($tierProgress['belanja']) }} / {{ format_rp($tierProgress['platinum']) }}</span>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-rule/60">
                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endif
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
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin Saya</a>
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
@endsection
