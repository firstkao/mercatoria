@extends('layouts.app')

@section('title', 'Undang Teman — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Halaman Undang Teman — CSS biasa, scope .pf-* (tidak bergantung build Tailwind). */
        .pf-tabs { display: flex; flex-wrap: wrap; gap: 0; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .pf-tab { display: inline-block; padding: 11px 16px; margin-bottom: -1px; border-bottom: 2px solid transparent;
                  font: 400 13px/18px 'Roboto', system-ui, sans-serif; color: #888; text-decoration: none; transition: color .15s; }
        .pf-tab:hover { color: #000; }
        .pf-tab.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .pf-card { margin-top: 20px; padding: 24px 28px 28px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .pf-label { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .pf-code { margin: 12px 0 0; font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: .18em; color: #000; }
        .pf-link { margin: 10px 0 0; font-size: 13px; line-height: 19px; color: #555; word-break: break-all; }
        .pf-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }

        .pf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 20px; }
        .pf-grid.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pf-grid.is-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

        .pf-stat { padding: 16px 18px 18px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .pf-stat-value { margin: 8px 0 0; font-size: 22px; line-height: 28px; font-weight: 700; color: #000; }
        .pf-stat-value.is-sm { font-size: 15px; line-height: 21px; font-weight: 700; }
        .pf-stat-desc { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: #555; }

        .pf-sect-h { margin: 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .pf-empty { margin: 12px 0 0; font-size: 13px; line-height: 19px; color: #888; }

        .pf-table-wrap { margin-top: 14px; overflow-x: auto; }
        .pf-table { width: 100%; border-collapse: collapse; background: #fff; font: 400 13px/19px 'Roboto', system-ui, sans-serif; color: #333; }
        .pf-table th { padding: 10px 8px; text-align: left; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em;
                       text-transform: uppercase; color: #333; border-bottom: 1px solid #e1e1e1; }
        .pf-table td { padding: 10px 8px; border-bottom: 1px solid #e1e1e1; }
        .pf-table tbody tr:last-child td { border-bottom: 0; }
        .pf-chip { display: inline-block; padding: 3px 8px; font-size: 10px; line-height: 15px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .pf-chip.is-ok { background: #43A047; color: #fff; }
        .pf-chip.is-wait { background: #FFF3E0; color: #8A6D3B; }

        @media (max-width: 720px) { .pf-grid.is-3, .pf-grid.is-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) {
            .pf-grid, .pf-grid.is-3, .pf-grid.is-4 { grid-template-columns: 1fr; }
            .pf-card { padding: 20px 16px 22px; }
            .pf-code { font-size: 18px; line-height: 24px; letter-spacing: .12em; }
        }
    </style>

    <div class="au pf">
        <p class="au-eyebrow">Akun</p>
        <h1 class="au-title">Undang Teman</h1>
        <p class="au-lead">Ajak teman berbelanja dan dapatkan koin.</p>

        <div class="pf-tabs">
            <a href="{{ route('dashboard') }}" class="pf-tab">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}" class="pf-tab">Notifikasi</a>
            <a href="{{ route('profile.coins') }}" class="pf-tab">Koin</a>
            <a href="{{ route('profile.wallet') }}" class="pf-tab">Voucher</a>
            <a href="{{ route('profile.referral') }}" class="pf-tab is-on">Undang Teman</a>
            <a href="{{ route('profile.edit') }}" class="pf-tab">Edit Profil</a>
        </div>

        <div class="pf-card">
            <p class="pf-label">KODE UNDANGANMU</p>
            <p class="pf-code">{{ $user->referral_code }}</p>
            <p class="pf-link" id="referral-link">
                Link: {{ url('/register?ref='.$user->referral_code) }}
            </p>
            <div class="pf-actions">
                <button type="button" data-copy="{{ $user->referral_code }}" class="au-btn is-ghost is-sm">
                    <x-icons.clipboard width="14" height="14" />
                    Copy Kode
                </button>
                <button type="button" data-copy="{{ url('/register?ref='.$user->referral_code) }}" class="au-btn is-ghost is-sm">
                    <x-icons.link width="14" height="14" />
                    Copy Link Undangan
                </button>
            </div>
        </div>

        {{-- Reward Program (Design Spec 10.3 / PRD 9.2) --}}
        <div class="pf-grid">
            <div class="pf-stat">
                <p class="pf-label">UNDANG TEMAN</p>
                <p class="pf-stat-value is-sm">+3.000 Koin</p>
                <p class="pf-stat-desc">Saat temanmu menyelesaikan order pertama. Teman mendapat +1.000 koin saat pembayaran pertama terverifikasi.</p>
            </div>
            <div class="pf-stat">
                <p class="pf-label">SHARE PRODUK</p>
                <p class="pf-stat-value is-sm">+5 Koin / Klik</p>
                <p class="pf-stat-desc">Per IP unik Indonesia saat membuka link produk yang kamu bagikan.</p>
            </div>
        </div>

        {{-- Stats share (data BE menyusul) --}}
        @isset($shareStats)
            <div class="pf-grid is-3">
                <div class="pf-stat">
                    <p class="pf-label">LINK DIBUKA</p>
                    <p class="pf-stat-value">{{ $shareStats['opens'] ?? 0 }}</p>
                </div>
                <div class="pf-stat">
                    <p class="pf-label">KOIN DARI SHARE</p>
                    <p class="pf-stat-value">{{ number_format($shareStats['coins'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="pf-stat">
                    <p class="pf-label">REWARD</p>
                    <p class="pf-stat-value">5 / klik</p>
                </div>
            </div>
        @endisset

        {{-- Stats Undang Teman (DS 10.3) --}}
        @isset($referralStats)
            <div class="pf-grid is-4">
                <div class="pf-stat">
                    <p class="pf-label">Total Diundang</p>
                    <p class="pf-stat-value">{{ $referralStats['total_diundang'] ?? 0 }}</p>
                </div>
                <div class="pf-stat">
                    <p class="pf-label">Berhasil</p>
                    <p class="pf-stat-value">{{ $referralStats['berhasil'] ?? 0 }}</p>
                </div>
                <div class="pf-stat">
                    <p class="pf-label">Menunggu Order</p>
                    <p class="pf-stat-value">{{ $referralStats['menunggu_order'] ?? 0 }}</p>
                </div>
                <div class="pf-stat">
                    <p class="pf-label">Koin</p>
                    <p class="pf-stat-value">{{ number_format($referralStats['koin_didapat'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        @endisset

        <div class="pf-card">
            <h2 class="pf-sect-h">Riwayat Undangan</h2>
            @if ($referrals->isEmpty())
                <p class="pf-empty">Belum ada yang bergabung lewat kode kamu.</p>
            @else
                <div class="pf-table-wrap">
                    <table class="pf-table">
                        <thead>
                            <tr>
                                <th>Nama / Email</th>
                                <th>Tgl Daftar</th>
                                <th>Status</th>
                                <th>Koin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $referral)
                                @php
                                    $hasOrder = $referral->orders()->exists();
                                    $koin = $hasOrder ? 3000 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $referral->name }}</td>
                                    <td>{{ $referral->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($hasOrder)
                                            <span class="pf-chip is-ok">Order Pertama Lunas</span>
                                        @else
                                            <span class="pf-chip is-wait">Menunggu Order</span>
                                        @endif
                                    </td>
                                    <td>+{{ number_format($koin) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = btn.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value);
                    }
                });
            });
        })();
    </script>
@endsection
