@extends('layouts.app')

@section('title', 'Pesanan Saya — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pesanan</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Pesanan Saya</h1>

    @if ($orders->isEmpty())
        <div class="mt-6 rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/50">
            Belum ada pesanan.
            <a href="/" class="ml-1 font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
        </div>
    @else
        <div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">No Order</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Total</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b border-rule/50">
                            <td class="px-4 py-3 font-medium text-ink">{{ $order->no_order }}</td>
                            <td class="px-4 py-3 text-ink/70">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-ink/80">{{ format_rp($order->total) }}</td>
                            <td class="px-4 py-3"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('orders.show', $order->no_order) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
