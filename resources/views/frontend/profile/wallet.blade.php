@extends('layouts.app')

@section('title', 'Voucher — MERCATORIA')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Voucher Saya</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.notifications') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="{{ route('profile.wallet') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Voucher</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>


    {{-- Voucher Saya (Design Spec 10.2) — data $vouchers dari controller --}}
    <section class="mt-8">

        {{-- Sub-tab Voucher (DS 10.2: Aktif / Akan Expired / Riwayat) --}}
        <div class="mt-3 flex flex-wrap gap-1 border-b border-rule" role="tablist">
            <button type="button" data-voucher-tab="aktif" class="voucher-tab border-b-2 border-ink px-4 py-2 text-sm font-semibold text-ink">Aktif</button>
            <button type="button" data-voucher-tab="expiring" class="voucher-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Akan Expired</button>
            <button type="button" data-voucher-tab="riwayat" class="voucher-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Riwayat</button>
        </div>

        <div class="mt-3 space-y-3">
            @isset($vouchers)
                @forelse ($vouchers as $userVoucher)
                    @php
                        $vStatus = $userVoucher->status ?? $userVoucher['status'] ?? null;
                        $vExp = $userVoucher->expires_at ?? $userVoucher['expires_at'] ?? null;
                        $isExpired = $vExp && \Illuminate\Support\Carbon::parse($vExp)->isPast();
                        $isExpiring = $vExp && ! $isExpired && \Illuminate\Support\Carbon::parse($vExp)->diffInDays(now()) <= 30;
                        $vTabClass = $isExpired ? 'riwayat' : ($isExpiring ? 'expiring' : 'aktif');
                    @endphp
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-rule bg-white p-4" data-voucher-item="{{ $vTabClass }}">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $userVoucher->voucher->code ?? $userVoucher['code'] ?? '-' }}</p>
                            <p class="text-xs text-ink/60">
                                Potong {{ format_rp($userVoucher->voucher->nominal ?? $userVoucher['nominal'] ?? 0) }}
                                · min. belanja {{ format_rp($userVoucher->voucher->min_belanja ?? $userVoucher['min_belanja'] ?? 0) }}
                                @if ($vExp)
                                    · s/d {{ \Illuminate\Support\Carbon::parse($vExp)->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="rounded px-2 py-1 text-xs font-semibold
                            {{ $vStatus === 'active' ? 'bg-[#43A047] text-white' : ($vStatus === 'hangus' ? 'bg-[#E53935] text-white' : 'bg-paper text-ink/60') }}">
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

        // Voucher tabs (DS 10.2)
        (function () {
            var tabs = document.querySelectorAll('.voucher-tab');
            var items = document.querySelectorAll('[data-voucher-item]');
            if (!tabs.length) return;
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                        t.classList.add('border-transparent', 'text-ink/55');
                    });
                    tab.classList.remove('border-transparent', 'text-ink/55');
                    tab.classList.add('border-ink', 'font-semibold', 'text-ink');
                    var mode = tab.getAttribute('data-voucher-tab');
                    items.forEach(function (item) {
                        item.style.display = item.getAttribute('data-voucher-item') === mode ? '' : 'none';
                    });
                });
            });
            // Default: show aktif
            items.forEach(function (item) {
                item.style.display = item.getAttribute('data-voucher-item') === 'aktif' ? '' : 'none';
            });
        })();
    </script>
@endsection
