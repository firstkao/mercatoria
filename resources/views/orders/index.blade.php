@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <x-dashboard-tabs />
    <h1 class="text-2xl font-bold text-navy-500">Pesanan Saya</h1>
    @if ($pendingBills->isNotEmpty())
        <section class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-amber-950">🚨 TAGIHAN MENUNGGU ({{ $pendingBills->count() }})</h2>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Lihat semua</a>
            </div>
            <ul class="mt-3 divide-y divide-amber-200">
                @foreach ($pendingBills as $bill)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <a href="{{ route('orders.show', $bill) }}" class="font-semibold text-navy-500 hover:underline">{{ $bill->order_no }}</a>
                            @if ($bill->status === \App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value)
                                <p class="text-sm text-amber-950">Nominal tagihan: {{ format_rupiah($bill->total) }}</p>
                            @else
                                @php($coAmount = max(0, (float) $bill->total - (float) $bill->payments->where('status', 'verified')->sum('amount') + (float) $bill->fee_marketplace))
                                <p class="text-sm text-amber-950">Nominal CO marketplace: {{ format_rupiah($coAmount) }} · Checkout di {{ ucfirst($bill->platform) }} sebelum {{ $bill->pickup_deadline_at ? format_tanggal_id($bill->pickup_deadline_at).' '.$bill->pickup_deadline_at->format('H:i') : 'batas waktu 7 hari' }}.</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-950">{{ $bill->status_enum->label() }}</span>
                            @if ($bill->status === \App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value)
                                <a href="{{ route('orders.show', $bill) }}#payment-proof-form" class="rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700">Bayar &amp; Unggah Bukti</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="mt-5 divide-y divide-border rounded-xl border border-border bg-white">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-surface-2">
                <div>
                    <p class="font-semibold">{{ $order->order_no }}</p>
                    <p class="mt-1 text-sm text-text-muted">{{ format_tanggal_id($order->created_at) }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block rounded-full px-3 py-1 text-xs font-medium" style="background-color: {{ $order->status_enum->color() }}">{{ $order->status_enum->label() }}</span>
                    <p class="mt-1 font-semibold">{{ format_rupiah($order->total) }}</p>
                </div>
            </a>
        @empty
            <p class="p-8 text-center text-text-muted">Belum ada pesanan.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $orders->links() }}</div>
</div>
@endsection
