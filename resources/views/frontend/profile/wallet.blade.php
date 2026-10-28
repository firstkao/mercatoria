@extends('layouts.app')

@section('title', 'Wallet Voucher — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">VOUCHER SAYA</h1>

    <div class="mt-4 space-y-3">
        @forelse ($userVouchers as $userVoucher)
            <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <div>
                    <p class="text-sm font-semibold">{{ $userVoucher->voucher->code }}</p>
                    <p class="text-xs text-gray-500">
                        Potong {{ format_rp($userVoucher->voucher->nominal) }}
                        · min. belanja {{ format_rp($userVoucher->voucher->min_belanja) }}
                        @if ($userVoucher->expires_at) · s/d {{ $userVoucher->expires_at->format('d/m/Y') }} @endif
                    </p>
                </div>
                <span class="rounded px-2 py-1 text-xs font-semibold
                    {{ $userVoucher->status === 'active' ? 'bg-green-100 text-green-700' : ($userVoucher->status === 'hangus' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ strtoupper($userVoucher->status) }}
                </span>
            </div>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-100">
                Belum ada voucher.
            </div>
        @endforelse
    </div>
@endsection
