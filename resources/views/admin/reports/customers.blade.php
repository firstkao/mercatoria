@extends('layouts.admin')

@section('title', 'Rank Pelanggan')

@section('content')
<div x-data="{ gift: { url: '', name: '' } }">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">Rank Pelanggan</h1>
            <p class="mt-1 text-sm text-text-muted">Hanya akun customer yang bukan Crush; metrik memakai pesanan selesai.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('office.reports.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold text-navy-500">Laporan Penjualan</a>
            <a href="{{ route('office.reports.customers.export', request()->query()) }}" class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-semibold text-white">Export CSV</a>
        </div>
    </div>

    <form method="GET" class="mt-5 flex flex-wrap items-end gap-3 rounded-xl border border-border bg-white p-4">
        <label class="min-w-56 flex-1 text-sm font-medium">Cari customer
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama, email, atau HP"
                   class="mt-1 block w-full rounded-lg border border-border px-3 py-2">
        </label>
        <label class="text-sm font-medium">Dari tanggal
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block rounded-lg border border-border px-3 py-2">
        </label>
        <label class="text-sm font-medium">Sampai tanggal
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block rounded-lg border border-border px-3 py-2">
        </label>
        <label class="text-sm font-medium">Urutkan
            <select name="sort" class="mt-1 block rounded-lg border border-border px-3 py-2">
                <option value="spend" @selected(($filters['sort'] ?? 'spend') === 'spend')>Pengeluaran</option>
                <option value="orders" @selected(($filters['sort'] ?? '') === 'orders')>Jumlah pesanan</option>
                <option value="aov" @selected(($filters['sort'] ?? '') === 'aov')>AOV</option>
            </select>
        </label>
        <x-button type="submit">Filter</x-button>
    </form>

    <div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
        <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Nama &amp; Badge</th><th class="px-4 py-3">Tot Order</th><th class="px-4 py-3">Total Belanja</th><th class="px-4 py-3">AOV</th><th class="px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($customers as $customer)
                    @php $rank = $customers->firstItem() + $loop->index; @endphp
                    @php
                        $totalOrders = (int) $customer->completed_orders;
                        $totalSpend = (float) ($customer->total_spend ?? 0);
                    @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $rank }}</td>
                        <td class="px-4 py-3"><span class="block font-semibold">{{ $customer->name }}</span><span class="text-xs text-text-muted">{{ $customer->email }}</span></td>
                        <td class="px-4 py-3">{{ number_format($totalOrders) }}</td>
                        <td class="px-4 py-3">{{ format_rupiah($totalSpend) }}</td>
                        <td class="px-4 py-3">{{ format_rupiah($totalOrders > 0 ? $totalSpend / $totalOrders : 0) }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('office.users.show', $customer) }}" class="mr-3 font-semibold text-navy-500 hover:underline">Detail</a>
                            <button type="button" aria-label="Kirim gift ke {{ $customer->name }}" x-on:click="gift = { url: @js(route('office.users.gifts.store', $customer)), name: @js($customer->name) }; $dispatch('open-modal', 'send-gift')">🎁</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Belum ada customer yang memenuhi filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $customers->links() }}</div>

    <x-modal name="send-gift" maxWidth="max-w-md">
        <form method="POST" :action="gift.url" x-data="{ type: 'coin' }">
            @csrf
            <h2 class="text-lg font-bold text-navy-500">🎁 Kirim Gift ke <span x-text="gift.name"></span></h2>
            <label class="mt-4 block text-sm font-medium">Jenis Gift
                <select name="type" x-model="type" class="mt-1 w-full rounded-lg border-border text-sm">
                    <option value="coin">Koin</option>
                    <option value="voucher">Voucher</option>
                    <option value="gift_box">Gift Box Fisik</option>
                </select>
            </label>
            <label class="mt-3 block text-sm font-medium" x-show="type === 'coin'">Nominal Koin
                <input type="number" name="coin_amount" min="1" :disabled="type !== 'coin'" class="mt-1 w-full rounded-lg border-border text-sm">
            </label>
            <label class="mt-3 block text-sm font-medium" x-show="type === 'voucher'" x-cloak>Voucher
                <select name="voucher_id" :disabled="type !== 'voucher'" class="mt-1 w-full rounded-lg border-border text-sm">
                    @foreach (\App\Models\Voucher::query()->orderBy('code')->limit(200)->get() as $voucher)
                        <option value="{{ $voucher->id }}">{{ $voucher->code }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-3 block text-sm font-medium">Pesan (opsional)
                <textarea name="note" rows="2" maxlength="1000" class="mt-1 w-full rounded-lg border-border text-sm"></textarea>
            </label>
            <p class="mt-3 text-xs text-text-muted">Pelanggan menerima email dari info@ dan notifikasi bell.</p>
            <div class="mt-5 flex justify-end gap-2">
                <x-button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'send-gift')">Batal</x-button>
                <x-button type="submit">Kirim Gift</x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
