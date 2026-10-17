@extends('layouts.app')

@section('title', 'Wallet')

@section('content')
<div class="mx-auto max-w-7xl space-y-8 px-4 py-8">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Wallet</h1>
            <p class="mt-1 text-sm text-text-muted">Kelola saldo koin dan voucher akunmu.</p>
        </div>
        <a href="{{ route('referrals.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Undang Teman</a>
    </div>

    <section>
        <div class="mb-4 flex items-end justify-between">
            <div><h2 class="text-xl font-bold">Koin Saya</h2><p class="text-sm text-text-muted">1 koin = Rp1. Koin aktif berlaku selama satu tahun.</p></div>
            <strong class="text-2xl">{{ number_format($balance, 0, ',', '.') }} koin</strong>
        </div>
        <div class="overflow-x-auto rounded-xl border border-border bg-white">
            <table class="min-w-full divide-y divide-border text-left text-sm">
                <thead class="bg-surface-2 text-xs uppercase text-text-muted"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Sumber</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Saldo</th><th class="px-4 py-3">Kedaluwarsa</th><th class="px-4 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-border">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $transaction->source_enum->label() }}<br><span class="text-xs text-text-muted">{{ $transaction->description }}</span></td>
                            <td class="px-4 py-3">{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $transaction->expires_at?->format('d-m-Y') ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $transaction->status === 'expired' || $transaction->isExpired() ? 'Kedaluwarsa' : 'Aktif' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Belum ada riwayat koin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        @foreach ([
            ['Voucher Aktif', $activeVouchers],
            ['Akan Kedaluwarsa (7 hari)', $expiringVouchers],
            ['Riwayat Voucher', $voucherHistory],
        ] as [$title, $items])
            <div class="rounded-xl border border-border bg-white p-5">
                <h2 class="mb-3 text-lg font-bold">{{ $title }}</h2>
                @forelse ($items as $userVoucher)
                    <div class="border-t border-border py-3">
                        <p class="font-semibold">{{ $userVoucher->voucher?->name ?? 'Voucher' }}</p>
                        <p class="text-sm text-text-muted">{{ $userVoucher->voucher?->code }}</p>
                        <p class="text-xs text-text-light">Berlaku sampai {{ $userVoucher->voucher?->valid_until?->format('d-m-Y') ?: 'tanpa batas' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-text-muted">Belum ada voucher.</p>
                @endforelse
            </div>
        @endforeach
    </section>
</div>
@endsection
