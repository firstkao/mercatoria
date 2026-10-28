@extends('layouts.app')

@section('title', 'Pesanan Saya — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Pesanan Saya</h1>

    @if ($orders->isEmpty())
        <div class="mt-6 rounded-xl bg-white p-10 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-100">
            Belum ada pesanan.
            <a href="/" class="ml-1 font-medium text-black underline">Mulai belanja</a>
        </div>
    @else
        <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No Order</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b border-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $order->no_order }}</td>
                            <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ format_rp($order->total) }}</td>
                            <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $order->status)) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('orders.show', $order->no_order) }}" class="text-sky-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
