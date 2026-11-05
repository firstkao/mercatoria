@extends('layouts.app')

@section('title', 'Koin Saya — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .ko, .ko *, .ko *::before, .ko *::after { box-sizing: border-box; }
        .ko { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .ko a { text-decoration: none; }

        .ko-nav { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .ko-nav a { display: block; padding: 10px 16px; font-size: 14px; line-height: 20px; color: #555; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: color .15s; }
        .ko-nav a:hover { color: #000; }
        .ko-nav a.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .ko-balance { margin: 24px 0 0; padding: 28px 32px; background: #fff; border: 1px solid #e1e1e1; text-align: center; }
        .ko-balance-label { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #888; }
        .ko-balance-value { margin: 8px 0 0; font-size: 42px; line-height: 1; font-weight: 700; color: #000; }

        .ko-subtabs { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .ko-subtabs button { padding: 8px 16px; font: 400 13px/20px 'Roboto', system-ui, sans-serif; color: #555; background: none; border: 0; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; transition: color .15s; }
        .ko-subtabs button:hover { color: #000; }
        .ko-subtabs button.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .ko-table-wrap { margin: 24px 0 0; background: #fff; border: 1px solid #e1e1e1; padding: 20px 24px; }
        .ko-table-title { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #000; }
        .ko-table-scroll { overflow-x: auto; margin-top: 14px; }
        .ko-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ko-table th { padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #888; border-bottom: 1px solid #e1e1e1; white-space: nowrap; }
        .ko-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; color: #333; }
        .ko-table tr:last-child td { border-bottom: 0; }

        .ko-pos { color: #43A047; }
        .ko-neg { color: #e53935; }

        .ko-badge { display: inline-block; padding: 2px 8px; font-size: 10px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #fff; border-radius: 0; }
        .ko-badge.is-aktif { background: #43A047; }
        .ko-badge.is-expired { background: #E53935; }

        .ko-empty { padding: 24px 0; text-align: center; font-size: 14px; color: #888; }
    </style>

    <div class="ko">
        <p class="au-eyebrow">Akun</p>
        <h1 class="au-title">Koin Saya</h1>
        <p class="au-lead">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>

        <div class="ko-nav">
            <a href="{{ route('dashboard') }}">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}">Notifikasi</a>
            <a href="{{ route('profile.coins') }}" class="is-on">Koin</a>
            <a href="{{ route('profile.wallet') }}">Voucher</a>
            <a href="{{ route('profile.referral') }}">Undang Teman</a>
            <a href="{{ route('profile.edit') }}">Edit Profil</a>
        </div>

        <div class="ko-balance">
            <p class="ko-balance-label">TOTAL KOIN AKTIF</p>
            <p class="ko-balance-value">{{ number_format($balance, 0, ',', '.') }}</p>
        </div>

        {{-- Sub-tab Voucher (DS 10.2: Aktif / Akan Expired / Riwayat) --}}
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
                                        <span class="ko-badge is-expired">HABIS</span>
                                    @else
                                        <span class="ko-badge is-aktif">AKTIF</span>
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
                    tabs.forEach(function (t) {
                        t.classList.remove('is-on');
                    });
                    tab.classList.add('is-on');
                    show(tab.getAttribute('data-coin-tab'));
                });
            });
            show('aktif');
        })();
    </script>
@endsection
