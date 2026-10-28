@extends('layouts.app')

@section('title', 'Pesanan '.$order->no_order.' — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Detail Pesanan</h1>

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs text-gray-400">Nomor Pesanan</p>
                <p class="text-lg font-semibold tracking-wide">{{ $order->no_order }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
                    {{ ucwords(str_replace(['_', 'wh'], [' ', 'WH'], $order->status)) }}
                </span>
                <a href="{{ route('orders.invoice', $order->no_order) }}"
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:border-black">
                    Download Invoice PDF
                </a>
            </div>
        </div>

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
        </dl>

        @if ($order->customer_note)
            <p class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-600">
                Catatan: {{ $order->customer_note }}
            </p>
        @endif

        @if ((float) $order->discount_amount > 0 && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
            <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                Koin atau voucher yang dipakai pada pesanan ini hangus permanen bila pembayaran gagal (PRD 9.3).
            </p>
        @endif
    </div>

    @if ($order->status === 'menunggu_pembayaran')
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold">Bayar &amp; Unggah Bukti</h2>
            <p class="mt-1 text-sm text-gray-600">
                Tagihan: <strong>{{ format_rp($order->tagihan_web) }}</strong>
                @if ($order->payment_deadline_at)
                    · Batas waktu: {{ $order->payment_deadline_at->format('d/m/Y H:i') }}
                @endif
            </p>

            <form method="POST" action="{{ route('orders.payments.store', $order->no_order) }}" enctype="multipart/form-data"
                  class="mt-4 space-y-3">
                @csrf
                <input type="file" name="proof" accept=".jpg,.jpeg,.png" required
                       class="block w-full text-sm text-gray-600">
                <p class="text-xs text-gray-400">Format JPG/PNG, maksimal 1 MB. Hanya bisa diunggah 1 kali.</p>
                @error('proof')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">
                    UNGGAH BUKTI
                </button>
            </form>
        </div>
    @elseif ($order->status === 'ditahan')
        <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-6">
            <h2 class="text-sm font-semibold text-sky-900">Bukti Bayar Terkirim</h2>
            <p class="mt-1 text-sm text-sky-800">
                Menunggu verifikasi admin. Form upload terkunci sampai admin menyetujui atau menolak bukti kamu.
            </p>
        </div>
    @endif

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Riwayat Status</h2>
        <ol class="mt-3 space-y-3 border-l border-gray-200 pl-4">
            @foreach ($order->statusLogs as $log)
                <li class="relative">
                    <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full {{ $loop->last ? 'bg-black' : 'bg-gray-300' }}"></span>
                    <p class="text-sm {{ $loop->last ? 'font-semibold' : 'text-gray-700' }}">
                        {{ ucwords(str_replace(['_', 'wh'], [' ', 'WH'], $log->status)) }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $log->created_at->format('d/m/Y H:i') }} · oleh {{ $log->changed_by }}
                        @if ($log->note) — {{ $log->note }} @endif
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
@endsection
