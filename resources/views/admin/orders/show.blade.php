@extends('admin.layouts.admin')

@section('title', 'Order '.$order->no_order)

@section('content')
    <h1 class="text-xl font-semibold">{{ $order->no_order }}</h1>
    <p class="mt-1 text-sm text-gray-500">
        {{ $order->user->name ?? '-' }} · {{ strtoupper($order->scheme) }} · {{ strtoupper($order->payment_method) }} ·
        {{ ucwords(str_replace('_', ' ', $order->status)) }}
    </p>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold">Item (snapshot)</h2>
            <table class="mt-2 w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr><th class="py-1">Item</th><th class="py-1">Qty</th><th class="py-1">Subtotal</th></tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-gray-50">
                            <td class="py-1">
                                {{ $item->snapshot->product_name }}
                                @if ($item->snapshot->variant_name) — {{ $item->snapshot->variant_name }} @endif
                            </td>
                            <td class="py-1">{{ $item->snapshot->qty }}</td>
                            <td class="py-1">{{ format_rp($item->snapshot->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-2 text-sm">Total: <strong>{{ format_rp($order->total) }}</strong> · Tagihan web: <strong>{{ format_rp($order->tagihan_web) }}</strong></p>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold">Ubah Status</h2>
            <form method="POST" action="{{ route('office.orders.status', $order->no_order) }}" class="mt-2 flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach (['menunggu_pembayaran', 'ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>

            <h2 class="mt-4 text-sm font-semibold">Catat Pelunasan (settlement)</h2>
            <form method="POST" action="{{ route('office.orders.settlements', $order->no_order) }}" class="mt-2 flex gap-2">
                @csrf
                <input type="number" step="0.01" name="amount" required placeholder="Nominal"
                       class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Catat</button>
            </form>
        </div>
    </div>

    <div class="mt-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Pembayaran</h2>
        <table class="mt-2 w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr><th class="py-1">Tipe</th><th class="py-1">Nominal</th><th class="py-1">Status</th><th class="py-1">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach ($order->payments as $payment)
                    <tr class="border-b border-gray-50">
                        <td class="py-1">{{ $payment->type }}</td>
                        <td class="py-1">{{ format_rp($payment->amount) }}</td>
                        <td class="py-1">{{ $payment->status }}</td>
                        <td class="py-1">
                            @if ($payment->status === 'pending')
                                <form method="POST" action="{{ route('office.payments.verify', [$order->no_order, $payment->id]) }}" class="inline">
                                    @csrf
                                    <button class="text-green-600 hover:underline">Verifikasi</button>
                                </form>
                                <form method="POST" action="{{ route('office.payments.reject', [$order->no_order, $payment->id]) }}" class="ml-2 inline">
                                    @csrf
                                    <button class="text-red-600 hover:underline">Tolak</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Riwayat Status</h2>
        <ol class="mt-2 space-y-1 text-sm">
            @foreach ($order->statusLogs as $log)
                <li>{{ $log->created_at->format('d/m/Y H:i') }} — {{ $log->status }} ({{ $log->changed_by }}) @if($log->note) · {{ $log->note }} @endif</li>
            @endforeach
        </ol>
    </div>
@endsection
