@extends('layouts.app')

@section('title', 'Koin Saya &mdash; Mercatoria')

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

    {{-- Sub-tab (Design Spec 10.2) --}}
    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule" role="tablist">
        <button type="button" data-coin-tab="aktif" class="coin-tab border-b-2 border-ink px-4 py-2 text-sm font-semibold text-ink">Aktif</button>
        <button type="button" data-coin-tab="expiring" class="coin-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Akan Expired</button>
        <button type="button" data-coin-tab="riwayat" class="coin-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Riwayat</button>
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
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        @php
                            $isExpired = $tx->expired_at && $tx->expired_at->isPast();
                            $isExpiring = $tx->expired_at && ! $tx->expired_at->isPast() && $tx->expired_at->diffInDays(now()) <= 30;
                            $statusClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'aktif');
                        @endphp
                        <tr class="border-b border-rule/50" data-coin-row="{{ $statusClass }}">
                            <td class="py-2 text-ink/75">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2 text-ink/80">{{ $tx->type }}</td>
                            <td class="py-2 {{ $tx->amount >= 0 ? 'text-[#43A047]' : 'text-danger' }}">
                                {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-ink/80">{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                            <td class="py-2 text-ink/60">{{ $tx->expired_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-2">
                                @if ($isExpired)
                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold text-white" style="background:#E53935">HABIS</span>
                                @else
                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold text-white" style="background:#43A047">AKTIF</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink/45">Belum ada transaksi koin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Voucher Saya (Design Spec 10.2) &mdash; data $vouchers dari controller --}}
    <section class="mt-8">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Voucher Saya</h2>
        <div class="mt-3 space-y-3">
            @isset($vouchers)
                @forelse ($vouchers as $userVoucher)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-rule bg-white p-4">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $userVoucher->voucher->code ?? $userVoucher['code'] ?? '-' }}</p>
                            <p class="text-xs text-ink/60">
                                Potong {{ format_rp($userVoucher->voucher->nominal ?? $userVoucher['nominal'] ?? 0) }}
                                · min. belanja {{ format_rp($userVoucher->voucher->min_belanja ?? $userVoucher['min_belanja'] ?? 0) }}
                                @if ($userVoucher->expires_at ?? $userVoucher['expires_at'] ?? null)
                                    · s/d {{ \Illuminate\Support\Carbon::parse($userVoucher->expires_at ?? $userVoucher['expires_at'])->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        @php($vStatus = $userVoucher->status ?? $userVoucher['status'] ?? null)
                        <span class="rounded px-2 py-1 text-xs font-semibold
                            {{ $vStatus === 'active' ? 'bg-green-100 text-green-700' : ($vStatus === 'hangus' ? 'bg-red-100 text-red-700' : 'bg-paper text-ink/60') }}">
                            {{ strtoupper($vStatus ?? '-') }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                        Belum ada voucher.
                    </div>
                @endforelse
            @else
                <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                    Belum ada voucher.
                </div>
            @endisset
        </div>
    </section>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.coin-tab');
            var rows = document.querySelectorAll('[data-coin-row]');
            function show(mode) {
                rows.forEach(function (row) {
                    var kind = row.getAttribute('data-coin-row');
                    var visible = mode === 'riwayat'
                        || (mode === 'aktif' && kind !== 'expired')
                        || (mode === 'expiring' && kind === 'expiring');
                    row.style.display = visible ? '' : 'none';
                });
            }
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                        t.classList.add('border-transparent', 'text-ink/55');
                    });
                    tab.classList.remove('border-transparent', 'text-ink/55');
                    tab.classList.add('border-ink', 'font-semibold', 'text-ink');
                    show(tab.getAttribute('data-coin-tab'));
                });
            });
            show('aktif');
        })();
    </script>
@endsection
