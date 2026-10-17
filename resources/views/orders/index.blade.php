@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <h1 class="text-2xl font-bold text-navy-500">Pesanan Saya</h1>
    <div class="mt-5 divide-y divide-border rounded-xl border border-border bg-white">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-surface-2">
                <div>
                    <p class="font-semibold">{{ $order->order_no }}</p>
                    <p class="mt-1 text-sm text-text-muted">{{ $order->created_at->format('d M Y H:i') }}</p>
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
