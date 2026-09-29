@extends('layouts.app', ['title' => 'Undang Teman'])

@section('content')
<section class="card" style="max-width: 900px;">
    <p class="eyebrow">Referral</p>
    <h1>Undang Teman, Dapat Koin 🎁</h1>
    <p class="muted">Bagikan kode undanganmu. Setiap kali teman mendaftar pakai kode dan menyelesaikan order pertamanya, kalian berdua dapat bonus koin.</p>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    {{-- Kartu kode referral --}}
    <div class="referral-card">
        <div>
            <span class="muted small">Kode undanganmu</span>
            <div class="referral-code" id="referral-code">{{ $user->referral_code }}</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="button button--small" onclick="copyReferral('code')">Copy Kode</button>
            <button type="button" class="button button--small" style="background:#e5e7eb;color:#111;" onclick="copyReferral('link')">Copy Link</button>
        </div>
    </div>

    <div class="referral-rewards">
        <div class="referral-reward">
            <span class="referral-reward__icon">👥</span>
            <strong>Kamu dapat</strong>
            <span class="referral-reward__amount">{{ number_format($rewardReferrer, 0, ',', '.') }} koin</span>
            <small class="muted">per teman yang berhasil</small>
        </div>
        <div class="referral-reward">
            <span class="referral-reward__icon">🎉</span>
            <strong>Temanmu dapat</strong>
            <span class="referral-reward__amount">{{ number_format($rewardReferee, 0, ',', '.') }} koin</span>
            <small class="muted">setelah order pertama selesai</small>
        </div>
    </div>

    {{-- Stats --}}
    <div class="account-stats" style="margin-top:24px;">
        <div class="account-stat">
            <span class="account-stat__icon">👥</span>
            <div>
                <span class="account-stat__label">Total Diundang</span>
                <strong class="account-stat__value">{{ $stats['total'] }}</strong>
            </div>
        </div>
        <div class="account-stat">
            <span class="account-stat__icon">✅</span>
            <div>
                <span class="account-stat__label">Berhasil</span>
                <strong class="account-stat__value">{{ $stats['rewarded'] }}</strong>
            </div>
        </div>
        <div class="account-stat">
            <span class="account-stat__icon">⏳</span>
            <div>
                <span class="account-stat__label">Menunggu</span>
                <strong class="account-stat__value">{{ $stats['pending'] }}</strong>
            </div>
        </div>
        <div class="account-stat">
            <span class="account-stat__icon">🪙</span>
            <div>
                <span class="account-stat__label">Koin Didapat</span>
                <strong class="account-stat__value">{{ number_format($stats['coins'], 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    {{-- Riwayat undangan --}}
    <h2 style="margin-top:32px;">Riwayat Undangan</h2>
    @if ($referrals->isEmpty())
        <div class="empty">
            <p>Belum ada yang pakai kode undanganmu.</p>
            <p class="muted">Bagikan kode ke teman-temanmu untuk mulai dapat koin.</p>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Bergabung</th>
                        <th>Status</th>
                        <th>Reward</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($referrals as $ref)
                        <tr>
                            <td>{{ $ref->referee?->displayName() ?? 'Pengguna terhapus' }}</td>
                            <td class="muted small">{{ $ref->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                            <td>
                                @if ($ref->status === 'rewarded')
                                    <span class="badge badge--on">Berhasil</span>
                                @elseif ($ref->status === 'pending')
                                    <span class="badge badge--muted">Menunggu order pertama</span>
                                @else
                                    <span class="badge badge--danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td>
                                @if ($ref->status === 'rewarded')
                                    <strong style="color:var(--success);">+{{ number_format($ref->referrer_reward, 0, ',', '.') }}</strong>
                                @else
                                    <span class="muted">—</span>
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

@push('scripts')
<script>
    function copyReferral(mode) {
        var text = mode === 'code'
            ? document.getElementById('referral-code').textContent.trim()
            : '{{ $user->referralUrl() }}';

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                alert(mode === 'code' ? 'Kode disalin!' : 'Link disalin!');
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert(mode === 'code' ? 'Kode disalin!' : 'Link disalin!');
        }
    }
</script>
@endpush
@endsection