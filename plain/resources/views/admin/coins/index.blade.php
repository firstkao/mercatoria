@extends('admin.layouts.app', ['title' => 'Koin Pengguna'])

@section('filters')
    <form method="GET" action="{{ route('admin.coins.index') }}" class="filters">
        <input type="search" name="q" value="{{ $q }}"
               placeholder="Cari nama, email, atau WhatsApp" aria-label="Cari pengguna">
        <button type="submit" class="btn">Cari</button>
        @if ($q)
            <a href="{{ route('admin.coins.index') }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')
    @if ($users->isEmpty())
        <div class="empty"><p>Belum ada pengguna yang punya koin{{ $q ? ' yang cocok' : '' }}.</p></div>
    @else
        <div class="panel panel--flush only-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th class="is-right">Koin Aktif</th>
                        <th class="is-right">Segera Hangus</th>
                        <th class="is-right">Sudah Hangus</th>
                        <th class="is-right">Total Didapat</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php($s = $summaries[$u->id])
                        <tr>
                            <td class="table__name">
                                <a href="{{ route('admin.coins.show', $u) }}" class="table__title">{{ $u->displayName() }}</a>
                                <div class="muted small">{{ $u->email ?? '—' }}</div>
                            </td>
                            <td class="is-right text-price">{{ number_format($s['active'], 0, ',', '.') }}</td>
                            <td class="is-right">
                                @if ($s['expiring_soon'] > 0)
                                    <span class="badge badge--warn">{{ number_format($s['expiring_soon'], 0, ',', '.') }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="is-right">
                                @if ($s['expired'] > 0)
                                    <span class="muted">{{ number_format($s['expired'], 0, ',', '.') }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="is-right muted">{{ number_format($s['total_earned'], 0, ',', '.') }}</td>
                            <td class="is-right nowrap">
                                <a href="{{ route('admin.coins.show', $u) }}" class="link">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="cards only-mobile">
            @foreach ($users as $u)
                @php($s = $summaries[$u->id])
                <li>
                    <a href="{{ route('admin.coins.show', $u) }}" class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title">{{ $u->displayName() }}</span>
                            <span class="badge badge--on">{{ number_format($s['active'], 0, ',', '.') }} koin</span>
                        </div>
                        <p class="card-row__meta">{{ $u->email ?? '—' }}</p>
                        @if ($s['expiring_soon'] > 0)
                            <p class="card-row__meta text-warning">⚠️ {{ number_format($s['expiring_soon'], 0, ',', '.') }} koin segera hangus</p>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        @include('admin.partials.pagination', ['paginator' => $users])
    @endif
@endsection
