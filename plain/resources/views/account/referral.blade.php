@extends('layouts.app', ['title' => 'Undang Teman'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Referral</p>
            <h1 class="account-head__title">Undang Teman</h1>
            <div class="account-head__meta">
                <span>Dua cara dapat koin: undang teman sampai mereka beli atau bagikan link produk.</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        {{-- Kode undangan --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Kode Undanganmu</h2>
            </div>
            <div class="referral-code-block">
                <span class="referral-code" id="referral-code">{{ $user->referral_code }}</span>
                <div class="referral-code-block__actions">
                    <button type="button" class="account-btn account-btn--sm" onclick="copyReferral('code')">Copy Kode</button>
                    <button type="button" class="account-btn account-btn--ghost account-btn--sm" onclick="copyReferral('link')">Copy Link Undang</button>
                </div>
            </div>
        </section>

        {{-- Reward: 2 sistem --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Reward</h2>
            </div>
            <div class="referral-rewards-grid">
                <div class="referral-reward-item">
                    <span class="referral-reward-item__amount">+{{ number_format($rewardReferrer, 0, ',', '.') }}</span>
                    <span class="referral-reward-item__label">Teman daftar &amp; beli</span>
                    <small class="referral-reward-item__note">
                        Sekali, saat order pertamanya selesai. Klik link undang teman <strong>tidak</strong> memberi reward.
                    </small>
                </div>
                <div class="referral-reward-item">
                    <span class="referral-reward-item__amount">+{{ number_format($rewardClick, 0, ',', '.') }}</span>
                    <span class="referral-reward-item__label">Orang buka share produkmu</span>
                    <small class="referral-reward-item__note">
                        Sekali per IP × produk, seumur hidup. Bagikan dari halaman produk mana pun.
                    </small>
                </div>
            </div>
        </section>

        {{-- Stats: Undang Teman --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Undang Teman</h2>
                <span class="account-section__hint">Reward setelah order pertama</span>
            </div>
            <div class="metrics">
                <div class="metrics__item">
                    <span class="metrics__label">Total Diundang</span>
                    <strong class="metrics__value">{{ $stats['total'] }}</strong>
                </div>
                <div class="metrics__item">
                    <span class="metrics__label">Berhasil</span>
                    <strong class="metrics__value">{{ $stats['rewarded'] }}</strong>
                </div>
                <div class="metrics__item">
                    <span class="metrics__label">Menunggu Order</span>
                    <strong class="metrics__value">{{ $stats['pending'] }}</strong>
                </div>
                <div class="metrics__item">
                    <span class="metrics__label">Koin dari Undang</span>
                    <strong class="metrics__value">{{ number_format($stats['invite_coins'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </section>

        {{-- Stats: Share Produk --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Share Produk</h2>
                <span class="account-section__hint">Reward saat link dibuka</span>
            </div>
            <div class="metrics">
                <div class="metrics__item">
                    <span class="metrics__label">Link Dibuka</span>
                    <strong class="metrics__value">{{ $stats['share_clicks'] }}</strong>
                </div>
                <div class="metrics__item">
                    <span class="metrics__label">Koin dari Share</span>
                    <strong class="metrics__value">{{ number_format($stats['share_click_coins'], 0, ',', '.') }}</strong>
                </div>
                <div class="metrics__item">
                    <span class="metrics__label">Reward / Klik</span>
                    <strong class="metrics__value">+{{ number_format($rewardClick, 0, ',', '.') }}</strong>
                </div>
            </div>
            <p class="cart-summary__note" style="margin-top:16px;">
                Buka halaman produk apa pun, klik tombol <strong>Bagikan</strong>. Link yang tersalin otomatis
                berisi kode referralmu. Setiap orang yang membuka dari IP baru = koin untuk kamu.
            </p>
        </section>

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
                                    <td class="account-table__muted">
                                        {{ $ref->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}
                                    </td>
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
@endsection

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
            showCopiedToast(mode === 'code' ? 'Kode disalin' : 'Link undang disalin');
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
