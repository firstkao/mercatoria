@extends('layouts.app')

@section('title', 'Koin Saya — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Koin Saya</h1>
    <p class="mt-1 text-sm text-gray-500">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>

    <div class="mt-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <p class="text-xs text-gray-500">TOTAL KOIN AKTIF</p>
        <p class="mt-1 text-5xl font-semibold">{{ number_format($balance, 0, ',', '.') }}</p>
    </div>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Riwayat Koin</h2>
        <table class="mt-3 w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="py-2">Tanggal</th>
                    <th class="py-2">Sumber</th>
                    <th class="py-2">Awal</th>
                    <th class="py-2">Sisa</th>
                    <th class="py-2">Kedaluwarsa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr class="border-b border-gray-50">
                        <td class="py-2">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ $tx->type }}</td>
                        <td class="py-2 {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-2">{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                        <td class="py-2">{{ $tx->expired_at?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada transaksi koin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
