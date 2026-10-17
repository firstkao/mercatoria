@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="text-2xl font-bold text-navy-500">Pesanan</h1><p class="mt-1 text-sm text-text-muted">Kelola status, pembayaran, dan refund.</p></div>
    <a href="{{ route('office.refunds.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold text-navy-500 hover:bg-surface-2">Kelola Refund</a>
</div>
<form method="GET" action="{{ route('office.orders.index') }}" class="mt-5 grid gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-[1fr_1fr_auto]">
    <label class="sr-only" for="order-search">Cari order</label>
    <input id="order-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="No order, customer, atau email" class="rounded-lg border-border text-sm">
    <label class="sr-only" for="order-status">Status</label>
    <select id="order-status" name="status" class="rounded-lg border-border text-sm">
        <option value="">Semua status</option>
        @foreach (\App\Enums\OrderStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>

@if ($orders->isNotEmpty())
    <form id="bulk-status-form" method="POST" action="{{ route('office.orders.bulk-status') }}" class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-border bg-white p-4">
        @csrf
        @method('PATCH')
        <label class="sr-only" for="bulk-status">Status baru</label>
        <select id="bulk-status" name="status" required class="rounded-lg border-border text-sm">
            <option value="">Ubah status terpilih ke...</option>
            @foreach (\App\Enums\OrderStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold">Terapkan massal</button>
        <span class="text-xs text-text-muted">Perubahan ditolak bila salah satu order tidak bisa berpindah ke status pilihan.</span>
    </form>
@endif
@if ($errors->any())<p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>@endif

<div class="mt-4 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="w-full min-w-[850px] text-left text-sm">
        <thead class="bg-navy-500 text-white"><tr><th class="px-4 py-3">Pilih</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Pembayaran</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse ($orders as $order)
                <tr class="hover:bg-surface-2">
                    <td class="px-4 py-3"><input form="bulk-status-form" type="checkbox" name="order_ids[]" value="{{ $order->id }}" aria-label="Pilih {{ $order->order_no }}" class="rounded border-border"></td>
                    <td class="px-4 py-3"><a href="{{ route('office.orders.show', $order) }}" class="font-semibold text-navy-500 hover:underline">{{ $order->order_no }}</a></td>
                    <td class="px-4 py-3">{{ $order->customer_name }}<span class="block text-xs text-text-muted">{{ $order->customer_email }}</span></td>
                    <td class="px-4 py-3">{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">{{ format_rupiah($order->total) }}</td>
                    <td class="px-4 py-3">{{ $order->status_enum->label() }}</td>
                    <td class="px-4 py-3">{{ $order->pending_payments_count ? 'Menunggu verifikasi' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-8 text-center text-text-muted">Pesanan tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $orders->links() }}</div>
@endsection
