@extends('layouts.app')

@section('title', 'Undang Teman — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">
        <div class="ac-head">
            <div>
                <h1 class="ac-title">Undang Teman</h1>
                <p class="ac-lead">Ajak teman berbelanja dan dapatkan koin.</p>
            </div>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'referral'])

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Kode Undanganmu</h2></div>
            <div class="ac-body">
                <p class="ac-code">{{ $user->referral_code }}</p>
                <p class="ac-codelink" id="referral-link">Link: {{ url('/register?ref='.$user->referral_code) }}</p>
                <div class="ac-actions">
                    <button type="button" data-copy="{{ $user->referral_code }}" class="ac-btn is-ghost is-sm">
                        <x-icons.clipboard width="14" height="14" />
                        <span data-label>Copy Kode</span>
                    </button>
                    <button type="button" data-copy="{{ url('/register?ref='.$user->referral_code) }}" class="ac-btn is-ghost is-sm">
                        <x-icons.link width="14" height="14" />
                        <span data-label>Copy Link Undangan</span>
                    </button>
                </div>
            </div>
        </section>

        <div class="ac-stats is-2">
            <div class="ac-stat">
                <p class="ac-stat-l">Undang Teman</p>
                <p class="ac-stat-v is-sm">+3.000 Koin</p>
                <p class="ac-stat-d">Saat temanmu menyelesaikan order pertama. Teman mendapat +1.000 koin saat pembayaran pertama terverifikasi.</p>
            </div>
            <div class="ac-stat">
                <p class="ac-stat-l">Share Produk</p>
                <p class="ac-stat-v is-sm">+5 Koin / Klik</p>
                <p class="ac-stat-d">Per IP unik Indonesia saat membuka link produk yang kamu bagikan.</p>
            </div>
        </div>

        @isset($shareStats)
            <div class="ac-stats is-3">
                <div class="ac-stat">
                    <p class="ac-stat-l">Link Dibuka</p>
                    <p class="ac-stat-v">{{ $shareStats['opens'] ?? 0 }}</p>
                </div>
                <div class="ac-stat">
                    <p class="ac-stat-l">Koin dari Share</p>
                    <p class="ac-stat-v">{{ number_format($shareStats['coins'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="ac-stat">
                    <p class="ac-stat-l">Reward</p>
                    <p class="ac-stat-v">5 / klik</p>
                </div>
            </div>
        @endisset

        @isset($referralStats)
            <div class="ac-stats">
                <div class="ac-stat">
                    <p class="ac-stat-l">Total Diundang</p>
                    <p class="ac-stat-v">{{ $referralStats['total_diundang'] ?? 0 }}</p>
                </div>
                <div class="ac-stat">
                    <p class="ac-stat-l">Berhasil</p>
                    <p class="ac-stat-v">{{ $referralStats['berhasil'] ?? 0 }}</p>
                </div>
                <div class="ac-stat">
                    <p class="ac-stat-l">Menunggu Order</p>
                    <p class="ac-stat-v">{{ $referralStats['menunggu_order'] ?? 0 }}</p>
                </div>
                <div class="ac-stat">
                    <p class="ac-stat-l">Koin</p>
                    <p class="ac-stat-v">{{ number_format($referralStats['koin_didapat'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        @endisset

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Riwayat Undangan</h2></div>
            @if ($referrals->isEmpty())
                <div class="ac-empty">Belum ada yang bergabung lewat kode kamu.</div>
            @else
                <div class="ac-table-wrap">
                    <table class="ac-table">
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
                                            <span class="ac-badge is-ok">Order Pertama Lunas</span>
                                        @else
                                            <span class="ac-badge is-wait">Menunggu Order</span>
                                        @endif
                                    </td>
                                    <td>+{{ number_format($koin) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = btn.getAttribute('data-copy');
                    if (!(navigator.clipboard && navigator.clipboard.writeText)) return;
                    navigator.clipboard.writeText(value);
                    var label = btn.querySelector('[data-label]');
                    if (!label) return;
                    var old = label.textContent;
                    label.textContent = 'Tersalin!';
                    setTimeout(function () { label.textContent = old; }, 2000);
                });
            });
        })();
    </script>
@endsection
