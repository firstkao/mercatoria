@extends('layouts.app')

@section('title', 'Dashboard — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Halo, {{ $user->name }}</h1>
    <p class="mt-1 text-sm text-gray-500">Bergabung sejak {{ $user->created_at->format('d/m/Y') }}</p>

    {{-- Info Bar (PRD 7.16) --}}
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total Pesanan</p>
            <p class="mt-1 text-2xl font-semibold">{{ $totalOrders }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total Belanja</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($totalBelanja) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Koin Aktif</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($coinBalance, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Undang Teman</p>
            <p class="mt-1 text-2xl font-semibold">{{ $referralCount }}</p>
        </div>
    </div>

    {{-- Tab Layout (PRD 7.16) --}}
    <div class="mt-6 flex flex-wrap gap-2 border-b border-gray-200">
        <a href="{{ route('orders.index') }}" class="border-b-2 border-black px-4 py-2 text-sm font-semibold">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2 text-sm text-gray-500 hover:text-black">Edit Profil</a>
    </div>

    {{-- Tagihan Menunggu (PRD 18.4) --}}
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">TAGIHAN MENUNGGU</h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-sky-600 hover:underline">Lihat semua</a>
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
                            <p class="text-xs text-gray-500">
                                {{ $order->status === 'menunggu_pembayaran' ? format_rp($order->tagihan_web) : 'CO di marketplace' }}
                                · {{ ucwords(str_replace('_', ' ', $order->status)) }}
                            </p>
                        </div>
                        <a href="{{ route('orders.show', $order->no_order) }}"
                           class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white">
                            {{ $order->status === 'menunggu_pembayaran' ? 'Bayar & Unggah Bukti' : 'Lihat Detail' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
