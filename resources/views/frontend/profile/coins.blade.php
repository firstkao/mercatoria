@extends('layouts.app')

@section('title', 'Koin Saya — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Koin Saya</h1>
    <p class="mt-1 text-sm text-ink/65">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-6 text-center">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">TOTAL KOIN AKTIF</p>
        <p class="mt-2 font-display text-5xl font-semibold text-ink">{{ number_format($balance, 0, ',', '.') }}</p>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-4">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Koin</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tanggal</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Sumber</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Awal</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Sisa</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Kedaluwarsa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr class="border-b border-rule/50">
                            <td class="py-2 text-ink/75">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2 text-ink/80">{{ $tx->type }}</td>
                            <td class="py-2 {{ $tx->amount >= 0 ? 'text-[#43A047]' : 'text-danger' }}">
                                {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-ink/80">{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                            <td class="py-2 text-ink/60">{{ $tx->expired_at?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-ink/45">Belum ada transaksi koin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
