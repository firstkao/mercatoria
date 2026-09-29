@extends('admin.layouts.app', ['title' => 'Referral'])

@section('tabs')
    <a href="{{ route('admin.referrals.index') }}" @class(['subtab', 'is-active' => ! $status])>Semua</a>
    <a href="{{ route('admin.referrals.index', ['status' => 'pending']) }}" @class(['subtab', 'is-active' => $status === 'pending'])>
        Menunggu <span class="subtab__count">{{ $stats['pending'] }}</span>
    </a>
    <a href="{{ route('admin.referrals.index', ['status' => 'rewarded']) }}" @class(['subtab', 'is-active' => $status === 'rewarded'])>
        Berhasil <span class="subtab__count">{{ $stats['rewarded'] }}</span>
    </a>
@endsection

@section('content')
    <div class="stats">
        <div class="stat">
            <span class="stat__label">Total Referral</span>
            <strong class="stat__value">{{ number_format($stats['total'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Berhasil</span>
            <strong class="stat__value">{{ number_format($stats['rewarded'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Menunggu</span>
            <strong class="stat__value">{{ number_format($stats['pending'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Koin Terbayar</span>
            <strong class="stat__value">{{ number_format($stats['coins_paid'], 0, ',', '.') }}</strong>
        </div>
    </div>

    @if ($referrals->isEmpty())
        <div class="empty"><p>Belum ada data referral.</p></div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pengundang</th>
                        <th>Diundang</th>
                        <th>Kode</th>
                        <th>Status</th>
                        <th>Reward</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($referrals as $ref)
                        <tr>
                            <td>
                                @if ($ref->referrer)
                                    <a href="{{ route('admin.users.show', $ref->referrer) }}" class="link">{{ $ref->referrer->displayName() }}</a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($ref->referee)
                                    <a href="{{ route('admin.users.show', $ref->referee) }}" class="link">{{ $ref->referee->displayName() }}</a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="mono small">{{ $ref->referral_code }}</td>
                            <td>
                                @if ($ref->status === 'rewarded')
                                    <span class="badge badge--on">Berhasil</span>
                                @elseif ($ref->status === 'pending')
                                    <span class="badge badge--muted">Menunggu</span>
                                @else
                                    <span class="badge badge--danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="small">
                                @if ($ref->status === 'rewarded')
                                    +{{ number_format($ref->referrer_reward, 0, ',', '.') }} / +{{ number_format($ref->referee_reward, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="muted small nowrap">{{ $ref->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $referrals])
    @endif
@endsection