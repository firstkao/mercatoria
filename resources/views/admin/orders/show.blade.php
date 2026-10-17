@extends('layouts.admin')

@section('title', 'Order '.$order->order_no)

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div><a href="{{ route('office.orders.index') }}" class="text-sm text-navy-500 hover:underline">← Pesanan</a><h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $order->order_no }}</h1><p class="mt-1 text-sm text-text-muted">{{ $order->customer_name }} · {{ $order->customer_email }} · {{ $order->customer_phone }}</p></div>
    <span class="rounded-full px-3 py-1 text-sm font-semibold" style="background-color: {{ $order->status_enum->color() }}">{{ $order->status_enum->label() }}</span>
</div>
@if (session('success'))<p role="status" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>@endif
@if ($errors->any())<p role="alert" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>@endif

<div class="mt-5 grid gap-5 lg:grid-cols-[1fr_20rem]">
    <div class="space-y-5">
        <section class="rounded-xl border border-border bg-white">
            <h2 class="border-b border-border p-4 text-lg font-semibold">Item &amp; Snapshot</h2>
            <div class="divide-y divide-border">
                @foreach ($order->items as $item)
                    @php($snapshot = $item->snapshot)
                    @if ($snapshot)
                        <article class="p-4">
                            <div class="flex gap-4">
                                @if ($snapshot->product_image_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($snapshot->product_image_path) }}" alt="" class="h-20 w-20 rounded-lg border border-border object-cover">@endif
                                <div class="flex-1">
                                    <h3 class="font-semibold">{{ $snapshot->product_name }}</h3>
                                    @if ($snapshot->variant_name)<p class="text-sm text-text-muted">{{ $snapshot->variant_name }}</p>@endif
                                    <p class="mt-1 text-sm">{{ $snapshot->qty }} × {{ format_rupiah($snapshot->final_price) }} · Subtotal {{ format_rupiah($snapshot->subtotal) }}</p>
                                    @if ($snapshot->product_deleted)<span class="mt-1 inline-block rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">Produk telah dihapus</span>@endif
                                </div>
                            </div>
                            <details class="mt-4 rounded-lg border-l-4 border-[#1E88E5] bg-[#F5F7FA] p-3">
                                <summary class="cursor-pointer select-none font-semibold">📦 Detail Ongkir</summary>
                                <dl class="mt-3 grid gap-2 font-mono text-sm sm:grid-cols-2">
                                    <div class="flex justify-between gap-2"><dt>Berat</dt><dd>{{ number_format($snapshot->weight_gram ?? 0) }} g</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Store</dt><dd>{{ $snapshot->store_name ?: '—' }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Harga produk</dt><dd>{{ format_rupiah($snapshot->base_price) }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Ongkir lokal CN</dt><dd>{{ format_rupiah($snapshot->ongkir_lokal_cn_idr) }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Ongkir CN–BTM</dt><dd>{{ format_rupiah($snapshot->ongkir_cn_btm_idr) }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Ongkir BTM–JKT</dt><dd>{{ format_rupiah($snapshot->ongkir_btm_jkt_idr) }}</dd></div>
                                    <div class="flex justify-between gap-2 font-bold"><dt>Total ongkir/item</dt><dd>{{ format_rupiah($snapshot->ongkir_total_idr) }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Diskon</dt><dd>{{ format_rupiah($snapshot->discount_amount) }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt>Profit order</dt><dd>{{ number_format((float) $order->frozen_profit_percent, 2, ',', '.') }}%</dd></div>
                                    <div class="flex justify-between gap-2 font-bold"><dt>Subtotal item</dt><dd>{{ format_rupiah($snapshot->subtotal) }}</dd></div>
                                </dl>
                            </details>
                        </article>
                    @endif
                @endforeach
            </div>
            <dl class="space-y-2 border-t border-border p-4 text-sm">
                <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ format_rupiah($order->subtotal) }}</dd></div>
                <div class="flex justify-between"><dt>Diskon</dt><dd>{{ format_rupiah($order->discount_amount) }}</dd></div>
                <div class="flex justify-between"><dt>Biaya marketplace</dt><dd>{{ format_rupiah($order->fee_marketplace) }}</dd></div>
                <div class="flex justify-between"><dt>Fee QRIS</dt><dd>{{ format_rupiah($order->fee_qris) }}</dd></div>
                <div class="flex justify-between"><dt>Ongkir</dt><dd>{{ format_rupiah($order->shipping_cost) }}</dd></div>
                <div class="flex justify-between border-t border-border pt-2 text-base font-bold"><dt>Total</dt><dd>{{ format_rupiah($order->total) }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="text-lg font-semibold">Catatan Customer</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-text-muted">{{ $order->customer_note ?: 'Tidak ada catatan.' }}</p>
            <h3 class="mt-5 font-semibold">Note to Customer</h3>
            @if ($order->note_to_customer)<p class="mt-2 whitespace-pre-line text-sm">{{ $order->note_to_customer }}</p>@endif
            <form method="POST" action="{{ route('office.orders.note', $order) }}" class="mt-3 space-y-2">
                @csrf
                @method('PATCH')
                <label class="sr-only" for="note-to-customer">Note to Customer</label>
                <textarea id="note-to-customer" name="note_to_customer" maxlength="5000" rows="3" required class="w-full rounded-lg border-border text-sm" placeholder="Tulis pesan untuk customer...">{{ old('note_to_customer', $order->note_to_customer) }}</textarea>
                <button type="submit" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold">Kirim catatan</button>
            </form>
        </section>

        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="text-lg font-semibold">Timeline Status</h2>
            <ol class="mt-4 space-y-3">
                @foreach ($order->statusLogs as $log)
                    <li class="border-l-2 {{ $loop->last ? 'border-primary-600 font-semibold' : 'border-border' }} pl-3">
                        <p>{{ $log->status_enum->label() }}</p>
                        <p class="text-xs font-normal text-text-muted">{{ $log->created_at->format('d M Y H:i') }} · {{ $log->changedBy?->name ?? 'Sistem' }}</p>
                        @if ($log->note)<p class="text-sm font-normal text-text-muted">{{ $log->note }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </section>
    </div>

    <aside class="space-y-5">
        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="font-semibold">Ubah Status</h2>
            <form method="POST" action="{{ route('office.orders.status', $order) }}" class="mt-3 space-y-3">
                @csrf
                @method('PATCH')
                <label class="sr-only" for="new-status">Status baru</label>
                <select id="new-status" name="status" required class="w-full rounded-lg border-border text-sm">
                    <option value="">Pilih status...</option>
                    @foreach (\App\Enums\OrderStatus::cases() as $status)
                        @if ($order->canTransitionTo($status))
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endif
                    @endforeach
                </select>
                <label class="sr-only" for="status-note">Catatan perubahan status</label>
                <input id="status-note" name="note" type="text" maxlength="1000" placeholder="Catatan (opsional)" class="w-full rounded-lg border-border text-sm">
                <button type="submit" class="w-full rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white">Simpan Status</button>
            </form>
        </section>
        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="font-semibold">Pembayaran</h2>
            <div class="mt-3 space-y-3">
                @forelse ($order->payments as $payment)
                    <div class="rounded-lg border border-border p-3 text-sm">
                        <div class="flex justify-between gap-2"><strong>{{ ucfirst($payment->type) }}</strong><span>{{ format_rupiah($payment->amount) }}</span></div>
                        <p class="mt-1 text-xs text-text-muted">{{ strtoupper($payment->method) }} · {{ ucfirst($payment->status) }}</p>
                        @if ($payment->proof_path)
                            <a href="{{ route('office.payments.proof', $payment) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-navy-500 underline">Lihat bukti</a>
                        @endif
                        @if ($payment->is_locked && $payment->status === 'pending')
                            <div class="mt-3 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('office.payments.verify', $payment) }}">@csrf @method('PATCH')<button class="rounded bg-green-700 px-3 py-2 text-xs font-semibold text-white">Verifikasi</button></form>
                                <form method="POST" action="{{ route('office.payments.reject', $payment) }}" class="flex flex-wrap gap-2">@csrf @method('PATCH')<input type="text" name="rejection_reason" required minlength="5" maxlength="1000" placeholder="Alasan penolakan" class="min-w-0 flex-1 rounded border-border text-xs"><button class="rounded bg-red-700 px-3 py-2 text-xs font-semibold text-white">Tolak</button></form>
                            </div>
                        @endif
                        @if ($payment->rejection_reason)<p class="mt-2 text-xs text-red-700">Alasan: {{ $payment->rejection_reason }}</p>@endif
                    </div>
                @empty
                    <p class="text-sm text-text-muted">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </section>
        @if ($order->refunds->isNotEmpty())
            <section class="rounded-xl border border-border bg-white p-4">
                <h2 class="font-semibold">Refund</h2>
                <div class="mt-3 space-y-3">
                    @foreach ($order->refunds as $refund)
                        <div class="rounded-lg border border-border p-3 text-sm">
                            <p class="font-semibold">{{ ucfirst($refund->type) }} · {{ format_rupiah($refund->amount) }}</p>
                            <p class="mt-1">{{ $refund->reason }}</p>
                            <p class="mt-1 text-xs text-text-muted">Status: {{ $refund->status_enum->label() }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </aside>
</div>
@endsection
