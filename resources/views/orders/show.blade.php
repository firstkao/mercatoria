@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_no)

@section('content')
@php
    $verifiedAmount = $order->paidAmount();
    $balanceDue = max(0, (float) $order->total - $verifiedAmount);
    $refundAmount = (float) $order->refunds->whereIn('status', ['requested', 'processing', 'completed'])->sum('amount');
@endphp
<div class="mx-auto max-w-5xl px-4 py-8">
    <a href="{{ route('orders.index') }}" class="text-sm font-medium text-navy-500 hover:underline">← Pesanan Saya</a>
    <div class="mt-4 flex flex-wrap items-start justify-between gap-4 rounded-xl border border-border bg-white p-5">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">{{ $order->order_no }}</h1>
            <p class="mt-1 text-sm text-text-muted">Dibuat {{ $order->created_at->format('d M Y H:i') }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-semibold" style="background-color: {{ $order->status_enum->color() }}">{{ $order->status_enum->label() }}</span>
    </div>

    @if (session('success'))
        <p role="status" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <p role="alert" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
    @endif

    @if ($order->note_to_customer)
        <aside id="note-from-admin" class="mt-4 rounded-xl border-l-4 border-primary-500 bg-blue-50 p-4">
            <h2 class="font-semibold">Catatan dari Mercatoria</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $order->note_to_customer }}</p>
        </aside>
    @endif

    <section class="mt-5 rounded-xl border border-border bg-white">
        <h2 class="border-b border-border p-4 text-lg font-semibold">Rincian Pesanan</h2>
        <ul class="divide-y divide-border">
            @foreach ($order->items as $item)
                @php($snapshot = $item->snapshot)
                @if ($snapshot)
                    <li class="flex flex-wrap gap-4 p-4">
                        @if ($snapshot->product_image_path)
                            <img src="{{ route('orders.snapshots.image', [$order, $snapshot, 'product']) }}" alt="" class="h-20 w-20 rounded-lg border border-border object-cover">
                        @endif
                        @if ($snapshot->variant_image_path)
                            <img src="{{ route('orders.snapshots.image', [$order, $snapshot, 'variant']) }}" alt="" class="h-20 w-20 rounded-lg border border-border object-cover">
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">{{ $snapshot->product_name }}</p>
                            @if ($snapshot->variant_name)<p class="text-sm text-text-muted">{{ $snapshot->variant_name }}</p>@endif
                            <p class="mt-1 text-sm text-text-muted">{{ $snapshot->qty }} × {{ format_rupiah($snapshot->final_price) }}</p>
                            <p class="mt-1 font-semibold">{{ format_rupiah($snapshot->subtotal) }}</p>
                            @if ($snapshot->product_deleted)
                                <span class="mt-2 inline-block rounded bg-surface-2 px-2 py-1 text-xs text-text-muted">Produk dihapus dari katalog</span>
                            @endif
                        </div>
                    </li>
                @endif
            @endforeach
        </ul>
        <dl class="space-y-2 border-t border-border p-4 text-sm">
            <div class="flex justify-between"><dt>Total pesanan</dt><dd class="font-bold">{{ format_rupiah($order->total) }}</dd></div>
            <div class="flex justify-between"><dt>Sudah dibayar</dt><dd>{{ format_rupiah($verifiedAmount) }}</dd></div>
            <div class="flex justify-between"><dt>Sisa tagihan</dt><dd class="font-semibold">{{ format_rupiah($balanceDue) }}</dd></div>
            <div class="flex justify-between"><dt>Pembayaran</dt><dd>{{ strtoupper($order->payment_scheme) }} · {{ strtoupper($order->payment_method) }} · {{ ucfirst($order->platform) }}</dd></div>
            @if ($order->customer_note)
                <div class="border-t border-border pt-3"><dt class="font-semibold">Catatan Pembelian</dt><dd class="mt-1 whitespace-pre-line">{{ $order->customer_note }}</dd></div>
            @endif
        </dl>
    </section>

    @if (in_array($order->status, [\App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value, \App\Enums\OrderStatus::MENUNGGU_PELUNASAN->value], true))
        <section class="mt-5 rounded-xl border border-border bg-white p-5">
            <h2 class="text-lg font-semibold">{{ $order->status === \App\Enums\OrderStatus::MENUNGGU_PELUNASAN->value ? 'Unggah Bukti Pelunasan' : 'Bayar & Unggah Bukti' }}</h2>
            <p class="mt-1 text-sm text-text-muted">Tagihan saat ini {{ format_rupiah($order->status === \App\Enums\OrderStatus::MENUNGGU_PEMBAYARAN->value && $order->payment_scheme === 'dp' ? min($balanceDue, (float) $order->total / 2) : $balanceDue) }}. File JPG, PNG, atau WebP, maksimal 5 MB.</p>
            @if ($order->payments->contains(fn ($payment) => $payment->is_locked))
                <p class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Bukti pembayaran sedang diverifikasi admin.</p>
            @else
                <form method="POST" action="{{ route('orders.payment-proof', $order) }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label for="proof" class="mb-1 block text-sm font-medium">Bukti pembayaran</label>
                        <input id="proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required class="w-full rounded-lg border border-border p-2 text-sm">
                    </div>
                    <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2.5 font-semibold text-white hover:bg-primary-700">Kirim Bukti</button>
                </form>
            @endif
        </section>
    @endif

    <section class="mt-5 rounded-xl border border-border bg-white p-5">
        <h2 class="text-lg font-semibold">Riwayat Status</h2>
        @if ($order->statusLogs->isEmpty())
            <p class="mt-3 text-sm text-text-muted">Belum ada perubahan status.</p>
        @else
            <ol class="mt-4 space-y-4 border-l-2 border-border pl-5">
                @foreach ($order->statusLogs as $log)
                    <li class="relative">
                        <span class="absolute -left-[1.64rem] top-1 h-3 w-3 rounded-full {{ $loop->last ? 'bg-primary-600' : 'bg-gray-300' }}"></span>
                        <p class="{{ $loop->last ? 'font-bold' : 'font-medium' }}">{{ $log->status_enum->label() }}</p>
                        <p class="text-xs text-text-muted">{{ $log->created_at->format('d M Y H:i') }} · {{ $log->changedBy?->name ?? 'Sistem' }}</p>
                        @if ($log->note)<p class="mt-1 text-sm text-text-muted">{{ $log->note }}</p>@endif
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    @if ($verifiedAmount > $refundAmount && ! in_array($order->status, [\App\Enums\OrderStatus::HANGUS->value, \App\Enums\OrderStatus::DANA_DIKEMBALIKAN->value, \App\Enums\OrderStatus::PEMBAYARAN_GAGAL->value], true))
        <details class="mt-5 rounded-xl border border-border bg-white p-5">
            <summary class="cursor-pointer font-semibold">Ajukan refund</summary>
            <p class="mt-2 text-sm text-text-muted">Refund diproses manual oleh tim Mercatoria. Maksimum yang dapat diajukan: {{ format_rupiah($verifiedAmount - $refundAmount) }}.</p>
            <form method="POST" action="{{ route('orders.refunds.store', $order) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <div>
                    <label for="refund-amount" class="mb-1 block text-sm font-medium">Nominal refund (Rp)</label>
                    <input id="refund-amount" name="amount" type="number" step="1000" min="1" max="{{ $verifiedAmount - $refundAmount }}" required class="w-full rounded-lg border-border">
                </div>
                <div>
                    <label for="refund-reason" class="mb-1 block text-sm font-medium">Alasan</label>
                    <input id="refund-reason" name="reason" type="text" minlength="5" maxlength="1000" required class="w-full rounded-lg border-border">
                </div>
                <button type="submit" class="rounded-lg border border-border px-4 py-2 font-semibold text-navy-500 hover:bg-surface-2 sm:col-span-2">Kirim Permintaan Refund</button>
            </form>
        </details>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        if (typeof window.mercatoriaTrack === 'function') {
            @foreach ($ga4Events as $event)
                window.mercatoriaTrack(@js($event['name']), @js($event['params']));
            @endforeach
        }
    </script>
@endpush
