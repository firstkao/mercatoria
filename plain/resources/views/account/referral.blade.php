@extends('layouts.app', ['title' => 'Undang Teman'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Referral</p>
            <h1 class="account-head__title">Undang Teman</h1>
            <div class="account-head__meta">
                <span>
                    Dapat <strong>{{ number_format($rewardClick, 0, ',', '.') }} koin</strong> saat teman membuka link,
                    dan <strong>{{ number_format($rewardReferrer, 0, ',', '.') }} koin</strong> saat order pertamanya selesai.
                </span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        {{-- Kode undangan --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Kode Undangan</h2>
            </div>
            <div class="referral-code-block">
                <span class="referral-code" id="referral-code">{{ $user->referral_code }}</span>
                <div class="referral-code-block__actions">
                    <button type="button" class="account-btn account-btn--sm" onclick="copyReferral('code')">Copy Kode</button>
                    <button type="button" class="account-btn account-btn--ghost account-btn--sm" onclick="copyReferral('link')">Copy Link</button>
                </div>
            </div>
        </section>

        {{-- Reward --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Reward</h2>
            </div>
            <div class="referral-rewards-grid">
                <div class="referral-reward-item">
                    <span class="referral-reward-item__amount">+{{ number_format($rewardClick, 0, ',', '.') }}</span>
                    <span class="referral-reward-item__label">Teman buka linkmu</span>
                    <small class="referral-reward-item__note">Sekali per IP unik, selamanya (anti-spam)</small>
                </div>
                <div class="referral-reward-item">
                    <span class="referral-reward-item__amount">{{ number_format($rewardReferrer, 0, ',', '.') }}</span>
                    <span class="referral-reward-item__label">Teman daftar &amp; beli</span>
                    <small class="referral-reward-item__note">Sekali, saat order pertamanya selesai</small>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <div class="metrics metrics--five">
            <div class="metrics__item">
                <span class="metrics__label">Klik Link</span>
                <strong class="metrics__value">{{ $stats['clicks'] }}</strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Total Diundang</span>
                <strong class="metrics__value">{{ $stats['total'] }}</strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Berhasil</span>
                <strong class="metrics__value">{{ $stats['rewarded'] }}</strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Menunggu</span>
                <strong class="metrics__value">{{ $stats['pending'] }}</strong>
            </div>
            <div class="metrics__item">
                <span class="metrics__label">Koin Didapat</span>
                <strong class="metrics__value">{{ number_format($stats['coins'], 0, ',', '.') }}</strong>
            </div>
        </div>

        {{-- Riwayat undangan --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Riwayat Undangan</h2>
            </div>

            @if ($referrals->isEmpty())
                <div class="account-empty">
                    <h3 class="account-empty__title">Belum ada yang pakai kode undanganmu.</h3>
                    <p class="account-empty__sub">Bagikan kode ke teman-temanmu untuk mulai dapat koin.</p>
                </div>
            @else
                <div class="account-table-wrap">
                    <table class="account-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Bergabung</th>
                                <th>Status</th>
                                <th class="is-right">Reward</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $ref)
                                <tr>
                                    <td>{{ $ref->referee?->displayName() ?? 'Pengguna terhapus' }}</td>
                                    <td class="account-table__muted">{{ $ref->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                                    <td>
                                        @if ($ref->status === 'rewarded')
                                            <span class="account-status account-status--ok">Berhasil</span>
                                        @elseif ($ref->status === 'pending')
                                            <span class="account-status">Menunggu</span>
                                        @else
                                            <span class="account-status account-status--danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td class="is-right">
                                        @if ($ref->status === 'rewarded')
                                            <strong class="account-table__amount">+{{ number_format($ref->referrer_reward, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="account-table__muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.pagination', ['paginator' => $referrals])
            @endif
        </section>

    </div>
</div>

@push('scripts')
<script>
    function copyReferral(mode) {
        var text = mode === 'code'
            ? document.getElementById('referral-code').textContent.trim()
            : '{{ $user->referralUrl() }}';

        function showCopiedToast(msg) {
            var t = document.getElementById('copy-toast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'copy-toast';
                t.className = 'copy-toast';
                t.setAttribute('role', 'status');
                document.body.appendChild(t);
            }
            t.textContent = msg;
            t.classList.add('copy-toast--show');
            clearTimeout(t._hideTimer);
            t._hideTimer = setTimeout(function () {
                t.classList.remove('copy-toast--show');
            }, 2000);
        }

        function doneCopy() {
            showCopiedToast(mode === 'code' ? 'Kode disalin' : 'Link disalin');
        }

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(doneCopy);
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            doneCopy();
        }
    }
</script>
@endpush
@endsection
