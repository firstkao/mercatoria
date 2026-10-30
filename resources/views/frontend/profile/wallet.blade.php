@extends('layouts.app')

@section('title', 'Wallet Voucher &mdash; Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">VOUCHER SAYA</h1>

    <div class="mt-5 space-y-3">
        @forelse ($userVouchers as $userVoucher)
            <div class="flex items-center justify-between gap-3 rounded-xl border border-rule bg-white p-4">
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $userVoucher->voucher->code }}</p>
                    <p class="text-xs text-ink/60">
                        Potong {{ format_rp($userVoucher->voucher->nominal) }}
                        · min. belanja {{ format_rp($userVoucher->voucher->min_belanja) }}
                        @if ($userVoucher->expires_at) · s/d {{ $userVoucher->expires_at->format('d/m/Y') }} @endif
                    </p>
                </div>
                <span class="rounded px-2 py-1 text-xs font-semibold
                    {{ $userVoucher->status === 'active' ? 'bg-green-100 text-green-700' : ($userVoucher->status === 'hangus' ? 'bg-red-100 text-red-700' : 'bg-paper text-ink/60') }}">
                    {{ strtoupper($userVoucher->status) }}
                </span>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                Belum ada voucher.
            </div>
        @endforelse
    </div>
@endsection
