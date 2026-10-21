@extends('layouts.app')

@section('title', 'Koin Saya')

@section('content')
<div class='mx-auto max-w-7xl space-y-8 px-4 py-8'>
    <x-dashboard-tabs />
    <div class='flex flex-wrap items-end justify-between gap-3'>
        <div><h1 class='text-3xl font-bold'>Koin Saya &amp; Voucher</h1><p class='mt-1 text-sm text-text-muted'>1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p></div>
        <a href='{{ route('wallet.index') }}#voucher' class='text-sm font-semibold text-navy-500 hover:underline'>Voucher Saya</a>
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
                            <td class='whitespace-nowrap px-4 py-3'>{{ format_tanggal_id($transaction->created_at) }}</td>
                            <td class='px-4 py-3'>{{ $transaction->source_enum->label() }}@if ($transaction->source_enum === \App\Enums\CoinSource::SHARE) (IP ID)@endif<br><span class='text-xs text-text-muted'>{{ $transaction->description }}</span></td>
                            <td class='px-4 py-3 font-semibold {{ $transaction->amount > 0 ? 'text-green-700' : 'text-red-700' }}'>{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td class='px-4 py-3'>{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                            <td class='whitespace-nowrap px-4 py-3'>{{ $transaction->expires_at ? format_tanggal_id($transaction->expires_at, true, true) : '—' }}</td>
                            <td class='px-4 py-3'><span class='rounded-full px-2 py-1 text-xs font-semibold text-white {{ $expired ? 'bg-[#E53935]' : 'bg-[#43A047]' }}'>{{ $expired ? 'HABIS' : 'AKTIF' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan='6' class='px-4 py-8 text-center text-text-muted'>Belum ada riwayat koin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class='mt-4'>{{ $transactions->links() }}</div>
    </section>
    <section x-data="{ tab: 'active' }" id='voucher'>
        <h2 class='mb-4 text-xl font-bold'>Voucher Saya</h2>
        @php($voucherTabs = [['active', 'Aktif', $activeVouchers], ['expiring', 'Akan Expired', $expiringVouchers], ['history', 'Riwayat', $voucherHistory]])
        <div class='mb-4 flex flex-wrap gap-2'>
            @foreach ($voucherTabs as [$key, $label, $items])
                <button type='button' @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-navy-500 text-white' : 'border border-border bg-white text-text-muted'" class='rounded-full px-4 py-2 text-sm font-semibold'>{{ $label }} ({{ $items->count() }})</button>
            @endforeach
        </div>
        @foreach ($voucherTabs as [$key, $label, $items])
            <div x-show="tab === '{{ $key }}'" @if ($key !== 'active') x-cloak @endif class='space-y-3'>
                @forelse ($items as $userVoucher)
                    @php($voucher = $userVoucher->voucher)
                    <article class='flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border bg-white p-5'>
                        <div>
                            <p class='font-semibold'>🎟️ {{ $voucher?->name ?? 'Voucher' }} — Potongan {{ $voucher?->type === 'percent' ? rtrim(rtrim(number_format((float) $voucher->value, 2, ',', ''), '0'), ',').'%' : format_rupiah($voucher?->value ?? 0) }}@if ((float) ($voucher?->min_purchase ?? 0) > 0) (Min. Belanja {{ format_rupiah($voucher->min_purchase) }})@endif</p>
                            <p class='mt-1 text-xs text-text-muted'>{{ $voucher?->code }} · Berlaku s/d: {{ $voucher?->valid_until ? format_tanggal_id($voucher->valid_until).' ('.max(0, (int) now()->diffInDays($voucher->valid_until, false)).' hari)' : 'tanpa batas' }}</p>
                        </div>
                        @if ($key !== 'history')<a href='{{ route('cart.index') }}' class='rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:brightness-95'>Pakai →</a>@endif
                    </article>
                @empty
                    <p class='rounded-xl border border-border bg-white p-6 text-sm text-text-muted'>Belum ada voucher.</p>
                @endforelse
            </div>
        @endforeach
    </section>
</div>
@endsection
