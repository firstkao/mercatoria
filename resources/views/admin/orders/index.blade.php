@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Orders</h1>
        <a href="{{ route('office.orders.inject') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah Pesanan (Inject)</a>
    </div>

    <form method="POST" action="{{ route('office.orders.bulk-status') }}" class="mt-4">
        @csrf
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="w-10 px-3 py-2"></th>
                        <th class="px-3 py-2">No Order</th>
                        <th class="px-3 py-2">Customer</th>
                        <th class="px-3 py-2">Total</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b border-gray-50">
                            <td class="px-3 py-2"><input type="checkbox" name="no_orders[]" value="{{ $order->no_order }}" class="rounded border-gray-300"></td>
                            <td class="px-3 py-2 font-medium">{{ $order->no_order }}</td>
                            <td class="px-3 py-2">{{ $order->user->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ format_rp($order->total) }}</td>
                            <td class="px-3 py-2">{{ ucwords(str_replace('_', ' ', $order->status)) }}</td>
                            <td class="px-3 py-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('office.orders.show', $order->no_order) }}" class="text-sky-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'hangus'] as $status)
                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Ubah Status Terpilih</button>
        </div>
    </form>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
