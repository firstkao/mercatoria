@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 rounded-xl border border-border bg-white p-6">
        <div>
            <h1 class="text-2xl font-bold text-text">Dashboard</h1>
            <p class="mt-1 text-text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></p>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-badge-state :state="auth()->user()->currentState()" />
                <span class="inline-flex items-center rounded-full border border-border px-3 py-1 text-xs text-text-light">
                    Terdaftar: {{ auth()->user()->created_at->format('d M Y') }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <x-notification-bell />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-surface-2">Keluar</button>
            </form>
        </div>
    </div>

    <x-verify-banner />
    <x-profile-banner />

    @if ($pendingBills->isNotEmpty())
        <section class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold uppercase tracking-wide text-amber-900">Tagihan Menunggu</h2>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Lihat semua</a>
            </div>
            <ul class="mt-3 divide-y divide-amber-200">
                @foreach ($pendingBills as $bill)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <a href="{{ route('orders.show', $bill) }}" class="font-semibold text-navy-500 hover:underline">{{ $bill->order_no }}</a>
                            <p class="text-sm text-text-muted">
                                {{ format_rupiah($bill->total) }}
                                @if ($bill->paidAmount() > 0)
                                    · sudah dibayar {{ format_rupiah($bill->paidAmount()) }}
                                @endif
                            </p>
                        </div>
                        @if ($bill->status === \App\Enums\OrderStatus::MENUNGGU_PELUNASAN->value && $bill->pickup_deadline_at)
                            <span class="text-sm text-amber-900">Batas {{ $bill->pickup_deadline_at->format('d M Y H:i') }}</span>
                        @endif
                        <a href="{{ route('orders.show', $bill) }}" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                            {{ $bill->status === \App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value ? 'Bayar & Unggah Bukti' : 'Lunasi & Ambil Sekarang' }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Total Pesanan</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($totalOrders) }}</p>
        </article>
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Total Belanja</p>
            <p class="mt-1 text-2xl font-bold">{{ format_rupiah($totalSpent) }}</p>
        </article>
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Koin Aktif</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($coinBalance, 0, ',', '.') }}</p>
        </article>
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Teman Diundang</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($referralCount) }}</p>
        </article>
    </div>

    <section id="orders" class="overflow-hidden rounded-xl border border-border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border p-5">
            <h2 class="text-lg font-bold">Pesanan Saya</h2>
            <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Semua Pesanan</a>
        </div>
        @if ($orders->isEmpty())
            <div class="p-10 text-center">
                <p class="text-4xl" aria-hidden="true">📦</p>
                <p class="mt-3 text-text-muted">Belum ada pesanan. Yuk mulai belanja merch favoritmu!</p>
                <x-button href="{{ route('catalog.index') }}" class="mt-4">Jelajahi Katalog</x-button>
            </div>
        @else
            <div class="divide-y divide-border">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-surface-2">
                        <div>
                            <span class="font-semibold">{{ $order->order_no }}</span>
                            <span class="ml-2 rounded-full px-2 py-1 text-xs" style="background-color: {{ $order->status_enum->color() }}">{{ $order->status_enum->label() }}</span>
                            <p class="mt-1 text-xs text-text-muted">{{ $order->created_at->format('d M Y H:i') }} · {{ $order->items->count() }} item</p>
                        </div>
                        <span class="font-semibold">{{ format_rupiah($order->total) }}</span>
                    </a>
                @endforeach
            </div>
            <div class="border-t border-border p-4">{{ $orders->links() }}</div>
        @endif
    </section>
</div>
@endsection
