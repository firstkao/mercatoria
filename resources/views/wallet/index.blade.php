@extends('layouts.app')

@section('title', 'Koin Saya')

@section('content')
<div class='mx-auto max-w-7xl space-y-8 px-4 py-8'>
    <x-dashboard-tabs />
    <div class='flex flex-wrap items-end justify-between gap-3'>
        <div><h1 class='text-3xl font-bold'>Koin Saya</h1><p class='mt-1 text-sm text-text-muted'>1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p></div>
        <a href='{{ route('wallet.index') }}' class='text-sm font-semibold text-navy-500 hover:underline'>Wallet &amp; Voucher</a>
    </div>
    <section class='rounded-xl border border-border bg-white p-6'>
        <p class='text-xs font-bold tracking-widest text-text-muted'>TOTAL KOIN AKTIF</p>
        <p class='mt-2 text-[56px] font-bold leading-none text-navy-500'>{{ number_format($balance, 0, ',', '.') }}</p>
        <p class='mt-2 text-sm text-text-muted'>Koin dapat dipakai sebagai potongan checkout, maksimal 5% dari subtotal.</p>
    </section>
    <section>
        <h2 class='mb-4 text-xl font-bold'>Riwayat Koin</h2>
        <div class='overflow-x-auto rounded-xl border border-border bg-white'>
            <table class='min-w-full divide-y divide-border text-left text-sm'>
                <thead class='bg-surface-2 text-xs uppercase text-text-muted'><tr><th class='px-4 py-3'>Tanggal</th><th class='px-4 py-3'>Sumber</th><th class='px-4 py-3'>Awal</th><th class='px-4 py-3'>Sisa</th><th class='px-4 py-3'>Kedaluwarsa</th><th class='px-4 py-3'>Status</th></tr></thead>
                <tbody class='divide-y divide-border'>
                    @forelse ($transactions as $transaction)
                        @php($expired = $transaction->status === 'expired' || $transaction->isExpired())
                        <tr>
                            <td class='whitespace-nowrap px-4 py-3'>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                            <td class='px-4 py-3'>{{ $transaction->source_enum->label() }}<br><span class='text-xs text-text-muted'>{{ $transaction->description }}</span></td>
                            <td class='px-4 py-3 font-semibold {{ $transaction->amount > 0 ? 'text-green-700' : 'text-red-700' }}'>{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td class='px-4 py-3'>{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                            <td class='whitespace-nowrap px-4 py-3'>{{ $transaction->expires_at?->format('d-m-Y') ?: '—' }}</td>
                            <td class='px-4 py-3'><span class='rounded-full px-2 py-1 text-xs font-semibold {{ $expired ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}'>{{ $expired ? 'HABIS' : 'AKTIF' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan='6' class='px-4 py-8 text-center text-text-muted'>Belum ada riwayat koin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class='mt-4'>{{ $transactions->links() }}</div>
    </section>
    <section class='grid gap-6 lg:grid-cols-3'>
        @foreach ([['Voucher Aktif', $activeVouchers], ['Akan Expired (7 hari)', $expiringVouchers], ['Riwayat Voucher', $voucherHistory]] as [$title, $items])
            <div class='rounded-xl border border-border bg-white p-5'><h2 class='mb-3 text-lg font-bold'>{{ $title }}</h2>
                @forelse ($items as $userVoucher)
                    <div class='border-t border-border py-3'><p class='font-semibold'>{{ $userVoucher->voucher?->name ?? 'Voucher' }}</p><p class='text-sm text-text-muted'>{{ $userVoucher->voucher?->code }}</p><p class='text-xs text-text-light'>Berlaku sampai {{ $userVoucher->voucher?->valid_until?->format('d-m-Y') ?: 'tanpa batas' }}</p></div>
                @empty<p class='text-sm text-text-muted'>Belum ada voucher.</p>@endforelse
            </div>
        @endforeach
    </section>
</div>
@endsection
