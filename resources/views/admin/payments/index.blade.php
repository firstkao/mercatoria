@extends('admin.layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    <h1 class="text-xl font-semibold">Verifikasi Pembayaran</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">No Order</th>
                    <th class="px-3 py-2">Tipe</th>
                    <th class="px-3 py-2">Nominal</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Upload</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr class="border-b border-gray-50">
                        <td class="px-3 py-2 font-medium">{{ $payment->order->no_order ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $payment->type }}</td>
                        <td class="px-3 py-2">{{ format_rp($payment->amount) }}</td>
                        <td class="px-3 py-2">{{ $payment->status }}</td>
                        <td class="px-3 py-2">{{ $payment->uploaded_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-right">
                            @if ($payment->order)
                                <a href="{{ route('office.orders.show', $payment->order->no_order) }}" class="text-sky-600 hover:underline">Detail</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
