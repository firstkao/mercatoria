@extends('admin.layouts.app', ['title' => 'Pengguna'])

@section('tabs')
    @foreach (['' => 'Semua', 'spammer' => 'Spammer', 'customer' => 'Customer'] as $value => $label)
        <a href="{{ route('admin.users.index', array_filter(['peran' => $value ?: null, 'q' => $filters['q'] ?: null])) }}"
           @class(['subtab', 'is-active' => ($filters['peran'] ?? '') === $value])>
            {{ $label }} <span class="subtab__count">{{ number_format($counts[$value ?: 'semua'], 0, ',', '.') }}</span>
        </a>
    @endforeach
@endsection

@section('filters')
    <form method="GET" action="{{ route('admin.users.index') }}" class="filters">
        @if ($filters['peran'])
            <input type="hidden" name="peran" value="{{ $filters['peran'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama, email, atau WhatsApp" aria-label="Cari pengguna">
        <button type="submit" class="btn">Cari</button>
        @if ($filters['q'])
            <a href="{{ route('admin.users.index', array_filter(['peran' => $filters['peran']])) }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')
    @if ($users->isEmpty())
        <div class="empty"><p>Belum ada pengguna{{ $filters['q'] ? ' yang cocok' : '' }}.</p></div>
    @else
        <div class="panel panel--flush only-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>WhatsApp</th>
                        <th>Peran</th>
                        <th>Pelacakan</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="table__name">
                                <a href="{{ route('admin.users.show', $user) }}" class="table__title">{{ $user->displayName() }}</a>
                                <div class="muted small">{{ $user->email ?? '—' }}</div>
                            </td>
                            <td class="muted nowrap">{{ $user->whatsapp ? '+'.$user->whatsapp : '—' }}</td>
                            <td>@include('admin.users.role-badge', ['user' => $user])</td>
                            <td>@include('admin.users.tracking', ['user' => $user])</td>
                            <td class="muted nowrap">{{ $user->registered_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="cards only-mobile">
            @foreach ($users as $user)
                <li>
                    <a href="{{ route('admin.users.show', $user) }}" class="card-row card-row--stack">
                        <span class="card-row__head">
                            <span class="card-row__title">{{ $user->displayName() }}</span>
                            @include('admin.users.role-badge', ['user' => $user])
                        </span>
                        <span class="card-row__meta">{{ $user->email ?? '—' }}</span>
                        @include('admin.users.tracking', ['user' => $user])
                    </a>
                </li>
            @endforeach
        </ul>

        @include('admin.partials.pagination', ['paginator' => $users])
    @endif
@endsection