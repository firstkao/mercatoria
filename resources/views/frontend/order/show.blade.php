@extends('layouts.app')

@section('title', 'Pesanan '.$order->no_order.' — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Pesanan Dibuat</h1>

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs text-gray-400">Nomor Pesanan</p>
                <p class="text-lg font-semibold">{{ $order->no_order }}</p>
            </div>
            <span class="rounded bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
                {{ ucwords(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>

        <p class="mt-4 text-sm text-gray-600">
            Silakan lakukan pembayaran sesuai tagihan di bawah. Batas waktu upload bukti bayar 1×24 jam.
        </p>

        <table class="mt-4 w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="py-2">Item</th>
                    <th class="py-2">Harga</th>
                    <th class="py-2">Qty</th>
                    <th class="py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    @php $snapshot = $item->snapshot; @endphp
                    <tr class="border-b border-gray-50">
                        <td class="py-2">
                            <p class="font-medium">{{ $snapshot->product_name }}</p>
                            @if ($snapshot->variant_name)
                                <p class="text-xs text-gray-500">Varian: {{ $snapshot->variant_name }}</p>
                            @endif
                            @if ($snapshot->store_name)
                                <p class="text-[11px] text-gray-400">Store: {{ $snapshot->store_name }}</p>
                            @endif
                        </td>
                        <td class="py-2">{{ format_rp($snapshot->final_price) }}</td>
                        <td class="py-2">{{ $snapshot->qty }}</td>
                        <td class="py-2">{{ format_rp($snapshot->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="mt-4 space-y-1 text-sm">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ format_rp($order->subtotal) }}</dd></div>
            @if ((float) $order->discount_amount > 0)
                <div class="flex justify-between text-red-600">
                    <dt>Diskon {{ $order->discount_type === 'coin' ? 'Koin' : 'Voucher' }}</dt>
                    <dd>- {{ format_rp($order->discount_amount) }}</dd>
                </div>
            @endif
            <div class="flex justify-between font-medium"><dt>Total Harga Barang</dt><dd>{{ format_rp($order->total) }}</dd></div>
            <div class="flex justify-between text-gray-500"><dt>Fee QRIS</dt><dd>{{ format_rp($order->fee_qris) }}</dd></div>
            <div class="flex justify-between text-lg font-semibold"><dt>Bayar di Web</dt><dd>{{ format_rp($order->tagihan_web) }}</dd></div>
            @if ($order->estimasi_co_marketplace)
                <div class="flex justify-between text-gray-500"><dt>Estimasi CO Marketplace</dt><dd>{{ format_rp($order->estimasi_co_marketplace) }}</dd></div>
            @endif
            @if ($order->fee_co_marketplace)
                <div class="flex justify-between text-gray-500"><dt>Biaya CO Marketplace</dt><dd>{{ format_rp($order->fee_co_marketplace) }}</dd></div>
            @endif
        </dl>

        @if ($order->customer_note)
            <p class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-600">
                Catatan: {{ $order->customer_note }}
            </p>
        @endif

        <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
            Koin atau voucher yang dipakai pada pesanan ini hangus permanen bila pembayaran gagal (PRD 9.3).
        </p>
    </div>
@endsection
