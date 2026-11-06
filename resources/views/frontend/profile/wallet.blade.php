@extends('layouts.app')

@section('title', 'Voucher — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .vw, .vw *, .vw *::before, .vw *::after { box-sizing: border-box; }
        .vw {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .vw a { text-decoration: none; }

        .vw-head { padding: 36px 40px 28px; background: #fff; }
        .vw-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }

        .vw-nav { display: flex; flex-wrap: wrap; gap: 0; margin-top: 24px; border-bottom: 1px solid #eee; background: #fff; padding: 0 20px; }
        .vw-nav a { display: block; padding: 14px 18px; font-size: 14px; color: #888; border-bottom: 2px solid transparent; }
        .vw-nav a:hover { color: #26222f; }
        .vw-nav a.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .vw-subtabs { display: flex; flex-wrap: wrap; gap: 0; margin-top: 20px; border-bottom: 1px solid #eee; background: #fff; padding: 0 20px; }
        .vw-subtabs button { padding: 12px 18px; font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #888; background: none; border: 0; border-bottom: 2px solid transparent; cursor: pointer; }
        .vw-subtabs button:hover { color: #26222f; }
        .vw-subtabs button.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .vw-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; padding: 0 20px; }
        .vw-card { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; background: #fff; border: 1px solid #eee; }
        .vw-code { margin: 0; font-size: 14px; font-weight: 700; color: #26222f; }
        .vw-desc { margin: 4px 0 0; font-size: 13px; line-height: 20px; color: #555; }

        .vw-badge { display: inline-block; padding: 3px 10px; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #555; background: #f7f7f7; border: 1px solid #eee; white-space: nowrap; }
        .vw-badge.is-active { color: #fff; background: #43A047; border-color: #43A047; }
        .vw-badge.is-hangus { color: #fff; background: #E53935; border-color: #E53935; }

        .vw-empty { padding: 40px 20px; text-align: center; font-size: 14px; color: #888; background: #fff; border: 1px solid #eee; margin: 16px 20px 0; }

        @media (max-width: 767px) {
            .vw-head { padding: 24px 16px; }
            .vw-title { font-size: 26px; }
            .vw-list { padding: 0 12px; }
        }
    </style>

    <div class="vw">
        <div class="vw-head">
            <h1 class="vw-title">Voucher Saya</h1>
        </div>

        <div class="vw-nav">
            <a href="{{ route('dashboard') }}">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}">Notifikasi</a>
            <a href="{{ route('profile.coins') }}">Koin</a>
            <a href="{{ route('profile.wallet') }}" class="is-on">Voucher</a>
            <a href="{{ route('profile.referral') }}">Undang Teman</a>
            <a href="{{ route('profile.edit') }}">Edit Profil</a>
        </div>

        <section>
            <div class="vw-subtabs" role="tablist">
                <button type="button" data-voucher-tab="aktif" class="voucher-tab is-on">Aktif</button>
                <button type="button" data-voucher-tab="expiring" class="voucher-tab">Akan Expired</button>
                <button type="button" data-voucher-tab="riwayat" class="voucher-tab">Riwayat</button>
            </div>

            <div class="vw-list">
                @isset($vouchers)
                    @forelse ($vouchers as $userVoucher)
                        @php
                            $vStatus = $userVoucher->status ?? $userVoucher['status'] ?? null;
                            $vExp = $userVoucher->expires_at ?? $userVoucher['expires_at'] ?? null;
                            $isExpired = $vExp && \Illuminate\Support\Carbon::parse($vExp)->isPast();
                            $isExpiring = $vExp && ! $isExpired && \Illuminate\Support\Carbon::parse($vExp)->diffInDays(now()) <= 30;
                            $vTabClass = $isExpired ? 'riwayat' : ($isExpiring ? 'expiring' : 'aktif');
                        @endphp
                        <div class="vw-card" data-voucher-item="{{ $vTabClass }}">
                            <div>
                                <p class="vw-code">{{ $userVoucher->voucher->code ?? $userVoucher['code'] ?? '-' }}</p>
                                <p class="vw-desc">
                                    Potong {{ format_rp($userVoucher->voucher->nominal ?? $userVoucher['nominal'] ?? 0) }}
                                    · min. belanja {{ format_rp($userVoucher->voucher->min_belanja ?? $userVoucher['min_belanja'] ?? 0) }}
                                    @if ($vExp)
                                        · s/d {{ \Illuminate\Support\Carbon::parse($vExp)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <span class="vw-badge {{ $vStatus === 'active' ? 'is-active' : ($vStatus === 'hangus' ? 'is-hangus' : '') }}">
                                {{ strtoupper($vStatus ?? '-') }}
                            </span>
                        </div>
                    @empty
                        <div class="vw-empty">Belum ada voucher.</div>
                    @endforelse
                @else
                    <div class="vw-empty">Belum ada voucher.</div>
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
