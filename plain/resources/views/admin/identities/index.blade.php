@extends('admin.layouts.app', ['title' => 'Blokir & banding'])

@section('tabs')
    @include('admin.identities.tabs')
@endsection

@section('filters')
    <form method="GET" action="{{ route('admin.identities.index') }}" class="filters">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari email atau nomor" aria-label="Cari email atau nomor">
        <select name="status" aria-label="Status">
            <option value="">Diblokir & pernah terhapus</option>
            <option value="diblokir" @selected($filters['status'] === 'diblokir')>Diblokir saja</option>
            <option value="diawasi" @selected($filters['status'] === 'diawasi')>Pernah terhapus, belum diblokir</option>
            <option value="semua" @selected($filters['status'] === 'semua')>Semua</option>
        </select>
        <button type="submit" class="btn">Terapkan</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.identities.index') }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')
    <p class="hint intro">
        Email dan nomor WhatsApp yang sama maksimal {{ $limit }}× mendaftar. Setelah akun Spammer ke-{{ $limit }} terhapus, keduanya otomatis diblokir.
        <strong>Reset</strong> dipakai kalau banding diterima: jatah daftar kembali penuh.
    </p>

    @if ($records->isEmpty())
        <div class="empty"><p>Tidak ada email atau nomor yang cocok.</p></div>
    @else
        <div class="panel panel--flush only-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email / WhatsApp</th>
                        <th>Daftar</th>
                        <th>Terhapus</th>
                        <th>Akun</th>
                        <th>Status</th>
                        <th>Pertama daftar</th>
                        <th>Terakhir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td class="table__name"><strong>{{ $record->kind === 'whatsapp' ? '+'.$record->value : $record->value }}</strong></td>
                            <td class="muted nowrap">{{ $record->registered_count }}×</td>
                            <td>@include('admin.identities.count-pill', ['record' => $record])</td>
                            <td class="nowrap">
                                @if ($record->account_id)
                                    <a href="{{ route('admin.users.show', $record->account_id) }}" class="status-dot status-dot--on">Aktif</a>
                                @else
                                    <span class="status-dot">Tidak ada</span>
                                @endif
                            </td>
                            <td>@include('admin.identities.status', ['record' => $record])</td>
                            <td class="muted nowrap small">{{ $record->first_registered_at ? \Illuminate\Support\Carbon::parse($record->first_registered_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '—' }}</td>
                            <td class="muted nowrap small">{{ $record->last_event_at ? \Illuminate\Support\Carbon::parse($record->last_event_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '—' }}</td>
                            <td class="nowrap">@include('admin.identities.actions', ['record' => $record])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="cards only-mobile">
            @foreach ($records as $record)
                <li class="card-row card-row--stack">
                    <span class="card-row__head">
                        <span class="card-row__title">{{ $record->kind === 'whatsapp' ? '+'.$record->value : $record->value }}</span>
                        @include('admin.identities.status', ['record' => $record])
                    </span>
                    <span class="card-row__meta">
                        Daftar {{ $record->registered_count }}× · Terhapus @include('admin.identities.count-pill', ['record' => $record])
                        · {{ $record->account_id ? 'Akun aktif' : 'Tanpa akun' }}
                    </span>
                    @include('admin.identities.actions', ['record' => $record])
                </li>
            @endforeach
        </ul>

        @include('admin.partials.pagination', ['paginator' => $records])
    @endif

    <section class="panel stack manual-block">
        <h2>Blokir manual</h2>
        <form method="POST" action="{{ route('admin.identities.store') }}" class="filters" onsubmit="return confirm('Blokir email/nomor ini?');">
            @csrf
            <select name="kind" aria-label="Jenis">
                <option value="email">Email</option>
                <option value="whatsapp">WhatsApp</option>
            </select>
            <input type="text" name="value" placeholder="email@contoh.com atau 08xxxx" aria-label="Email atau nomor" required>
            <button type="submit" class="btn btn--danger">Blokir</button>
        </form>
    </section>
@endsection