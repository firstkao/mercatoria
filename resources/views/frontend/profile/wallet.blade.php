@extends('layouts.app')

@section('title', 'Voucher — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">
        <div class="ac-head">
            <h1 class="ac-title">Voucher Saya</h1>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'wallet'])

        <section class="ac-sec">
            <div class="ac-chips" role="tablist">
                <button type="button" data-voucher-tab="aktif" class="ac-chip voucher-tab is-on">Aktif</button>
                <button type="button" data-voucher-tab="expiring" class="ac-chip voucher-tab">Akan Expired</button>
                <button type="button" data-voucher-tab="riwayat" class="ac-chip voucher-tab">Riwayat</button>
            </div>

            <div class="ac-rows">
                @isset($vouchers)
                    @forelse ($vouchers as $userVoucher)
                        @php
                            $vStatus = $userVoucher->status ?? $userVoucher['status'] ?? null;
                            $vExp = $userVoucher->expires_at ?? $userVoucher['expires_at'] ?? null;
                            $isExpired = $vExp && \Illuminate\Support\Carbon::parse($vExp)->isPast();
                            $isExpiring = $vExp && ! $isExpired && \Illuminate\Support\Carbon::parse($vExp)->diffInDays(now()) <= 30;
                            $vTabClass = $isExpired ? 'riwayat' : ($isExpiring ? 'expiring' : 'aktif');
                        @endphp
                        <div class="ac-row" data-voucher-item="{{ $vTabClass }}">
                            <div>
                                <p class="ac-row-t">{{ $userVoucher->voucher->code ?? $userVoucher['code'] ?? '-' }}</p>
                                <p class="ac-row-s">
                                    Potong {{ format_rp($userVoucher->voucher->nominal ?? $userVoucher['nominal'] ?? 0) }}
                                    · min. belanja {{ format_rp($userVoucher->voucher->min_belanja ?? $userVoucher['min_belanja'] ?? 0) }}
                                    @if ($vExp)
                                        · s/d {{ \Illuminate\Support\Carbon::parse($vExp)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <span class="ac-badge {{ $vStatus === 'active' ? 'is-ok' : ($vStatus === 'hangus' ? 'is-bad' : '') }}">{{ strtoupper($vStatus ?? '-') }}</span>
                        </div>
                    @empty
                        <div class="ac-empty">Belum ada voucher.</div>
                    @endforelse
                @else
                    <div class="ac-empty">Belum ada voucher.</div>
                @endisset
            </div>
        </section>
    </div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.voucher-tab');
            var items = document.querySelectorAll('[data-voucher-item]');
            if (!tabs.length) return;
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('is-on'); });
                    tab.classList.add('is-on');
                    var mode = tab.getAttribute('data-voucher-tab');
                    items.forEach(function (item) {
                        item.style.display = item.getAttribute('data-voucher-item') === mode ? '' : 'none';
                    });
                });
            });
            items.forEach(function (item) {
                item.style.display = item.getAttribute('data-voucher-item') === 'aktif' ? '' : 'none';
            });
        })();
    </script>
@endsection
