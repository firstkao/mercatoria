@extends('layouts.app')

@section('title', 'Koin Saya — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .ko, .ko *, .ko *::before, .ko *::after { box-sizing: border-box; }
        .ko {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .ko a { text-decoration: none; }

        .ko-head { padding: 36px 40px 28px; background: #fff; }
        .ko-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }
        .ko-lead { margin: 6px 0 0; font-size: 15px; color: #6f6a72; }

        .ko-nav { display: flex; flex-wrap: wrap; gap: 0; margin-top: 24px; border-bottom: 1px solid #eee; background: #fff; padding: 0 20px; }
        .ko-nav a { display: block; padding: 14px 18px; font-size: 14px; color: #888; border-bottom: 2px solid transparent; }
        .ko-nav a:hover { color: #26222f; }
        .ko-nav a.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .ko-balance { margin-top: 24px; padding: 32px; background: #fff; text-align: center; }
        .ko-balance-label { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #888; }
        .ko-balance-value { margin: 8px 0 0; font-size: 48px; line-height: 1; font-weight: 700; color: #26222f; }

        .ko-subtabs { display: flex; flex-wrap: wrap; gap: 0; margin-top: 20px; border-bottom: 1px solid #eee; background: #fff; padding: 0 20px; }
        .ko-subtabs button {
            padding: 12px 18px; font: 400 14px/20px 'Roboto', system-ui, sans-serif;
            color: #888; background: none; border: 0; border-bottom: 2px solid transparent;
            cursor: pointer;
        }
        .ko-subtabs button:hover { color: #26222f; }
        .ko-subtabs button.is-on { color: #26222f; font-weight: 700; border-bottom-color: #26222f; }

        .ko-table-wrap { margin-top: 24px; background: #fff; padding: 24px; }
        .ko-table-title { margin: 0 0 16px; font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #26222f; }
        .ko-table-scroll { overflow-x: auto; }
        .ko-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ko-table th { padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #888; border-bottom: 1px solid #eee; }
        .ko-table td { padding: 12px; border-bottom: 1px solid #f5f5f5; }
        .ko-table tr:last-child td { border-bottom: 0; }

        .ko-pos { color: #43A047; font-weight: 600; }
        .ko-neg { color: #e53935; font-weight: 600; }

        .ko-badge { display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: #fff; }
        .ko-badge.is-aktif { background: #43A047; }
        .ko-badge.is-expired { background: #E53935; }

        .ko-empty { padding: 32px 0; text-align: center; color: #888; }

        @media (max-width: 767px) {
            .ko-head { padding: 24px 16px; }
            .ko-title { font-size: 26px; }
            .ko-balance { padding: 24px 16px; }
            .ko-balance-value { font-size: 36px; }
            .ko-table-wrap { padding: 16px; }
        }
    </style>

    <div class="ko">
        <div class="ko-head">
            <h1 class="ko-title">Koin Saya</h1>
            <p class="ko-lead">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>
        </div>

        <div class="ko-nav">
            <a href="{{ route('dashboard') }}">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}">Notifikasi</a>
            <a href="{{ route('profile.coins') }}" class="is-on">Koin</a>
            <a href="{{ route('profile.wallet') }}">Voucher</a>
            <a href="{{ route('profile.referral') }}">Undang Teman</a>
            <a href="{{ route('profile.edit') }}">Edit Profil</a>
        </div>

        <div class="ko-balance">
            <p class="ko-balance-label">Total Koin Aktif</p>
            <p class="ko-balance-value">{{ number_format($balance, 0, ',', '.') }}</p>
        </div>

        <div class="ko-subtabs" role="tablist">
            <button type="button" data-coin-tab="aktif" class="coin-tab is-on">Aktif</button>
            <button type="button" data-coin-tab="expiring" class="coin-tab">Akan Expired</button>
            <button type="button" data-coin-tab="riwayat" class="coin-tab">Riwayat</button>
        </div>

        <div class="ko-table-wrap">
            <h2 class="ko-table-title">Riwayat Koin</h2>
            <div class="ko-table-scroll">
                <table class="ko-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Sumber</th>
                            <th>Awal</th>
                            <th>Sisa</th>
                            <th>Kedaluwarsa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            @php
                                $isExpired = $tx->expired_at && $tx->expired_at->isPast();
                                $isExpiring = $tx->expired_at && ! $tx->expired_at->isPast() && $tx->expired_at->diffInDays(now()) <= 30;
                                $statusClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'aktif');
                            @endphp
                            <tr data-coin-row="{{ $statusClass }}">
                                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $tx->type }}</td>
                                <td class="{{ $tx->amount >= 0 ? 'ko-pos' : 'ko-neg' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td>{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                                <td>{{ $tx->expired_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @if ($isExpired)
                                        <span class="ko-badge is-expired">Habis</span>
                                    @else
                                        <span class="ko-badge is-aktif">Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ko-empty">Belum ada transaksi koin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                    tabs.forEach(function (t) { t.classList.remove('is-on'); });
                    tab.classList.add('is-on');
                    show(tab.getAttribute('data-coin-tab'));
                });
            });
            show('aktif');
        })();
    </script>
@endsection
