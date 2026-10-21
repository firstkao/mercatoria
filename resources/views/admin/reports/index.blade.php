@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">Laporan Penjualan</h1>
            <p class="mt-1 text-sm text-text-muted">Pembayaran dihitung saat diverifikasi; penjualan saat pesanan selesai.</p>
        </div>
        <a href="{{ route('office.customers.rank') }}" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold text-navy-500">Rank Pelanggan</a>
    </div>

    <form method="GET" class="mt-5 flex flex-wrap items-end gap-3 rounded-xl border border-border bg-white p-4">
        <label class="text-sm font-medium">Dari tanggal
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block rounded-lg border border-border px-3 py-2">
        </label>
        <label class="text-sm font-medium">Sampai tanggal
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block rounded-lg border border-border px-3 py-2">
        </label>
        <x-button type="submit">Terapkan</x-button>
        <a href="{{ route('office.reports.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm">Reset</a>
    </form>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            'Total DP Masuk' => $metrics['dp_received'],
            'Total Pelunasan' => $metrics['settlements_received'] + $metrics['full_payments_received'],
            'Total Penjualan Sah (Selesai)' => $metrics['completed_sales'],
            'Outstanding' => $metrics['outstanding'],
            'Hangus' => $metrics['forfeited_received'],
        ] as $label => $amount)
            <article class="rounded-xl border border-border bg-white p-5">
                <p class="text-sm text-text-muted">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-navy-500">{{ format_rupiah($amount) }}</p>
            </article>
        @endforeach
    </div>
    <p class="mt-4 text-xs text-text-light">Refund dilaporkan terpisah dari pembayaran masuk dan tidak dijumlahkan ke penjualan selesai.</p>
@endsection
