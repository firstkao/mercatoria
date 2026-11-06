@extends('layouts.app')

@section('title', 'Koin Saya — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">
        <div class="ac-head">
            <div>
                <h1 class="ac-title">Koin Saya</h1>
                <p class="ac-lead">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>
            </div>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'coins'])

        <div class="ac-stats is-1">
            <div class="ac-stat">
                <p class="ac-stat-l">Total Koin Aktif</p>
                <p class="ac-stat-v is-xl">{{ number_format($balance, 0, ',', '.') }}</p>
            </div>
        </div>

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Riwayat Koin</h2></div>

            <div class="ac-chips" role="tablist">
                <button type="button" data-coin-tab="aktif" class="ac-chip coin-tab is-on">Aktif</button>
                <button type="button" data-coin-tab="expiring" class="ac-chip coin-tab">Akan Expired</button>
                <button type="button" data-coin-tab="riwayat" class="ac-chip coin-tab">Riwayat</button>
            </div>

            <div class="ac-table-wrap">
                <table class="ac-table">
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
                                <td class="{{ $tx->amount >= 0 ? 'ac-pos' : 'ac-neg' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td>{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                                <td>{{ $tx->expired_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @if ($isExpired)
                                        <span class="ac-badge is-bad">Habis</span>
                                    @else
                                        <span class="ac-badge is-ok">Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ac-empty">Belum ada transaksi koin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
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
