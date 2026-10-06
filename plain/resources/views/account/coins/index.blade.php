@extends('layouts.app', ['title' => 'Koin Saya'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Koin Saya</h1>
            <div class="account-head__meta">
                <span>1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</span>
            </div>
        </header>

        {{-- Saldo --}}
        <section class="account-section account-section--balance">
            <span class="coin-balance__label">Total Koin Aktif</span>
            <strong class="coin-balance__value">{{ number_format($activeCoins, 0, ',', '.') }}</strong>
        </section>

        {{-- Riwayat --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Riwayat Koin</h2>
            </div>

            @if($coinHistory->isEmpty())
                <div class="account-empty">
                    <h3 class="account-empty__title">Belum ada riwayat koin.</h3>
                    <p class="account-empty__sub">Koin dari order, referral, dan promo akan muncul di sini.</p>
                </div>
            @else
                <div class="account-table-wrap">
                    <table class="account-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Sumber</th>
                                <th class="is-right">Awal</th>
                                <th class="is-right">Sisa</th>
                                <th>Kedaluwarsa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coinHistory as $lot)
                                @php
                                    $isExpired = $lot->expires_at && $lot->expires_at->isPast();
                                    $isExhausted = $lot->remaining == 0;
                                @endphp
                                <tr>
                                    <td class="account-table__muted">{{ $lot->earned_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y') ?? '—' }}</td>
                                    <td>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($lot->source)) }}</td>
                                    <td class="is-right account-table__amount">+{{ number_format($lot->amount, 0, ',', '.') }}</td>
                                    <td class="is-right">{{ number_format($lot->remaining, 0, ',', '.') }}</td>
                                    <td class="account-table__muted">{{ $lot->expires_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y') ?? '—' }}</td>
                                    <td>
                                        @if($isExhausted && $isExpired)
                                            <span class="account-status account-status--danger">Hangus</span>
                                        @elseif($isExhausted)
                                            <span class="account-status">Habis</span>
                                        @elseif($isExpired)
                                            <span class="account-status account-status--danger">Kedaluwarsa</span>
                                        @else
                                            <span class="account-status account-status--ok">Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding-top: 20px;">
                    {{ $coinHistory->links('partials.pagination') }}
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
