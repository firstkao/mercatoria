@extends('layouts.app')

@section('title', 'Dashboard Pelanggan — Mercatoria')

@section('content')
    {{-- Header Dashboard (PRD 7.16 / Design Spec 10.1) --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard Pelanggan</h1>
            <p class="mt-1 text-lg">Selamat datang, {{ $user->name }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $stateBadge }}</span>
                @if ($tierBadge)
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $tierBadge }}</span>
                @endif
                <span class="text-gray-500">Terdaftar sejak: {{ $user->created_at->format('d F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Info Bar 4 Stats (PRD 7.16) --}}
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">TOTAL PESANAN</p>
            <p class="mt-1 text-2xl font-semibold">{{ $totalOrders }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">TOTAL BELANJA</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($totalBelanja) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">KOIN AKTIF</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($coinBalance, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">UNDANG TEMAN</p>
            <p class="mt-1 text-2xl font-semibold">{{ $undangBerhasil }} Berhasil</p>
        </div>
    </div>

    {{-- Tab Layout 4 tab (PRD 7.16) --}}
    <div class="mt-6 flex flex-wrap gap-2 border-b border-gray-200">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-black px-4 py-2 text-sm font-semibold">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Edit Profil</a>
    </div>

    {{-- Tagihan Menunggu (PRD 18.4) --}}
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">TAGIHAN MENUNGGU ({{ $tagihan->count() }})</h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-sky-600 hover:underline">Lihat Semua</a>
        </div>

        @if ($tagihan->isEmpty())
            <div class="mt-3 rounded-xl bg-white p-6 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-100">
                Tidak ada tagihan menunggu.
            </div>
        @else
            <div class="mt-3 space-y-3">
                @foreach ($tagihan as $order)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                        <div>
                            <p class="text-sm font-semibold">{{ $order->no_order }}</p>
                            @if ($order->status === 'menunggu_pembayaran')
                                <p class="text-xs text-gray-500">{{ format_rp($order->tagihan_web) }} · Menunggu Pembayaran</p>
                            @else
                                <p class="text-xs text-gray-500">
                                    Menunggu Pelunasan &amp; CO Marketplace
                                    @if ($order->pickup_deadline_at)
                                        · Sisa Waktu CO: {{ $order->pickup_deadline_at->diffForHumans(now(), ['parts' => 2]) }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        @if ($order->status === 'menunggu_pembayaran')
                            <a href="{{ route('orders.show', $order->no_order) }}"
                               class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white">
                                Bayar &amp; Unggah Bukti
                            </a>
                        @else
                            <a href="{{ route('orders.show', $order->no_order) }}"
                               class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold hover:border-black">
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
        <h2 class="text-sm font-semibold">DAFTAR PESANAN SAYA</h2>
        <div class="mt-3 space-y-2">
            @forelse ($orders as $order)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
                    <div class="text-sm">
                        <span class="font-semibold">{{ $order->no_order }}</span>
                        <span class="text-gray-400">· {{ $order->created_at->format('d M Y') }} · {{ format_rp($order->total) }}</span>
                        <span class="ml-1 rounded bg-gray-100 px-2 py-0.5 text-xs">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                    <a href="{{ route('orders.show', $order->no_order) }}" class="text-xs text-sky-600 hover:underline">Lihat</a>
                </div>
            @empty
                <div class="rounded-xl bg-white p-6 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-100">
                    Belum ada pesanan. <a href="/" class="text-black underline">Mulai belanja</a>
                </div>
            @endforelse
        </div>
    </section>
@endsection
