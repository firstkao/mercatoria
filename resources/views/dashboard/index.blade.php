@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 rounded-xl border border-border bg-white p-6">
        <div>
            <h1 class="text-2xl font-bold text-text">Dashboard Pelanggan</h1>
            <p class="mt-1 hidden text-text-muted md:block">Selamat datang, <strong>{{ auth()->user()->name }}</strong></p>
            <p class="mt-1 text-text-muted md:hidden">Halo, <strong>{{ auth()->user()->name }}</strong></p>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-badge-state :state="auth()->user()" />
                @if ($tier)<span class='inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold' style='background-color: {{ $tier->color() }}; color: {{ $tier->textColor() }}'>{{ $tier->icon() }} {{ $tier->label() }}</span>@endif
                <span class="inline-flex items-center rounded-full border border-border px-3 py-1 text-xs text-text-light">
                    Terdaftar sejak: {{ format_tanggal_id(auth()->user()->created_at, false) }}
                </span>
            </div>
            @if ($remainingViews !== null || $stateDeadline || $whatsappLink)
                <div class='mt-3 flex flex-wrap items-center gap-3 text-sm text-text-muted'>
                    @if ($remainingViews !== null)<span>⚠️ Kuota View {{ 10 - $remainingViews }}/10 · Kuota jelajah katalog kamu tersisa {{ $remainingViews }} dari 10 produk.</span>@endif
                    @if ($stateDeadline)<span>⏳ Countdown {{ strtoupper(auth()->user()->currentState()->value) }}: {{ $stateDeadline->diffForHumans() }}</span>@endif
                    @if ($whatsappLink)<a href='{{ $whatsappLink }}' class='font-semibold text-green-700 hover:underline'>💬 Hubungi via WhatsApp</a>@endif
                </div>
            @endif
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

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Total Pesanan</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($totalOrders) }}</p>
        </article>
        <article class="rounded-xl border border-border bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-text-muted">Total Belanja</p>
            <p class="mt-1 text-2xl font-bold">{{ format_rupiah($totalSpent) }}</p>
        </article>
        <a href="{{ route('wallet.coins') }}" class="rounded-xl border border-border bg-white p-4 hover:bg-surface-2">
            <p class="text-xs uppercase tracking-wide text-text-muted">Koin Aktif</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($coinBalance, 0, ',', '.') }}</p>
        </a>
        <a href="{{ route('referrals.invite') }}" class="rounded-xl border border-border bg-white p-4 hover:bg-surface-2">
            <p class="text-xs uppercase tracking-wide text-text-muted">Undang Teman</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($completedReferrals) }} Berhasil</p>
        </a>
    </div>

    <x-dashboard-tabs />

    @if ($pendingBills->isNotEmpty())
        <section class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold uppercase tracking-wide text-amber-900">🚨 Tagihan Menunggu ({{ $pendingBills->count() }})</h2>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Lihat semua</a>
            </div>
            <ul class='mt-3 divide-y divide-amber-200'>
                @foreach ($pendingBills as $bill)
                    <li class='flex flex-wrap items-center justify-between gap-3 py-3'>
                        <div>
                            <a href='{{ route('orders.show', $bill) }}' class='font-semibold text-navy-500 hover:underline'>{{ $bill->order_no }}</a>
                            @if ($bill->status === \App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value)
                                <p class='text-sm text-text-muted'>Nominal tagihan: {{ format_rupiah($bill->total) }} · {{ $bill->status_enum->label() }}</p>
                            @else
                                @php($coAmount = max(0, (float) $bill->total - (float) $bill->payments->where('status', 'verified')->sum('amount') + (float) $bill->fee_marketplace))
                                <p class='text-sm text-text-muted'>Nominal CO marketplace: {{ format_rupiah($coAmount) }} · {{ $bill->status_enum->label() }}</p>
                            @endif
                        </div>
                        @if ($bill->status === \App\Enums\OrderStatus::MENUNGGU_PELUNASAN->value)
                            <div class='text-sm text-amber-900'>
                                <p>Menunggu Pelunasan &amp; CO Marketplace</p>
                                @if ($bill->pickup_deadline_at)
                                    @php($remaining = max(0, now()->diffInSeconds($bill->pickup_deadline_at, false)))
                                    <p>⏰ Sisa Waktu CO {{ ucfirst($bill->platform ?: 'Marketplace') }}: {{ str_pad((string) intdiv((int) $remaining, 86400), 2, '0', STR_PAD_LEFT) }} Hari {{ str_pad((string) (intdiv((int) $remaining, 3600) % 24), 2, '0', STR_PAD_LEFT) }} Jam (Auto Sistem)</p>
                                @endif
                            </div>
                        @else
                            <a href='{{ route('orders.show', $bill) }}#payment-proof-form' class='rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:brightness-95'>Bayar &amp; Unggah Bukti</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif


    <section id="orders" class="overflow-hidden rounded-xl border border-border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border p-5">
            <h2 class="text-lg font-bold">Daftar Pesanan Saya</h2>
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
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-surface-2">
                        <div>
                            <span class="font-semibold">{{ $order->order_no }}</span>
                            <span class="ml-2 rounded-full px-2 py-1 text-xs" style="background-color: {{ $order->status_enum->color() }}">{{ $order->status_enum->label() }}</span>
                            <p class="mt-1 text-xs text-text-muted">{{ format_tanggal_id($order->created_at) }} · {{ $order->items->count() }} item</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-semibold">{{ format_rupiah($order->total) }}</span>
                            <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-border px-3 py-1.5 text-sm font-semibold text-navy-500 hover:bg-white">Lihat</a>
                            @if ($order->status === \App\Enums\OrderStatus::SELESAI->value)
                                <a href="{{ route('orders.invoice', $order) }}" class="rounded-lg border border-border px-3 py-1.5 text-sm font-semibold text-navy-500 hover:bg-white">PDF</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-border p-4">{{ $orders->links() }}</div>
        @endif
    </section>
</div>
@endsection
