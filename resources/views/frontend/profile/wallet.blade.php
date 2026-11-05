@extends('layouts.app')

@section('title', 'Voucher — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .vw, .vw *, .vw *::before, .vw *::after { box-sizing: border-box; }
        .vw { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .vw a { text-decoration: none; }

        .vw-nav { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .vw-nav a { display: block; padding: 10px 16px; font-size: 14px; line-height: 20px; color: #555; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: color .15s; }
        .vw-nav a:hover { color: #000; }
        .vw-nav a.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .vw-subtabs { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .vw-subtabs button { padding: 8px 16px; font: 400 13px/20px 'Roboto', system-ui, sans-serif; color: #555; background: none; border: 0; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; transition: color .15s; }
        .vw-subtabs button:hover { color: #000; }
        .vw-subtabs button.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .vw-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
        .vw-card { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; background: #fff; border: 1px solid #e1e1e1; }
        .vw-code { margin: 0; font-size: 14px; font-weight: 700; color: #000; }
        .vw-desc { margin: 4px 0 0; font-size: 13px; line-height: 20px; color: #555; }

        .vw-badge { display: inline-block; padding: 3px 10px; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #555; background: #f7f7f7; border: 1px solid #e1e1e1; border-radius: 0; white-space: nowrap; }
        .vw-badge.is-active { color: #fff; background: #43A047; border-color: #43A047; }
        .vw-badge.is-hangus { color: #fff; background: #E53935; border-color: #E53935; }

        .vw-empty { padding: 32px 20px; text-align: center; font-size: 14px; color: #888; background: #fff; border: 1px solid #e1e1e1; }
    </style>

    <div class="vw">
        <p class="au-eyebrow">Akun</p>
        <h1 class="au-title">Voucher Saya</h1>

        <div class="vw-nav">
            <a href="{{ route('dashboard') }}">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}">Notifikasi</a>
            <a href="{{ route('profile.coins') }}">Koin</a>
            <a href="{{ route('profile.wallet') }}" class="is-on">Voucher</a>
            <a href="{{ route('profile.referral') }}">Undang Teman</a>
            <a href="{{ route('profile.edit') }}">Edit Profil</a>
        </div>

        {{-- Voucher Saya (Design Spec 10.2) — data $vouchers dari controller --}}
        <section>

            {{-- Sub-tab Voucher (DS 10.2: Aktif / Akan Expired / Riwayat) --}}
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
                        <div class="vw-empty">
                            Belum ada voucher.
                        </div>
                    @endforelse
                @else
                    <div class="vw-empty">
                        Belum ada voucher.
                    </div>
                @endisset
            </div>
        </section>
    </div>

    <script>
        // Voucher tabs (DS 10.2)
        (function () {
            var tabs = document.querySelectorAll('.voucher-tab');
            var items = document.querySelectorAll('[data-voucher-item]');
            if (!tabs.length) return;
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('is-on');
                    });
                    tab.classList.add('is-on');
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
