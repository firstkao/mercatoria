@extends('admin.layouts.app', ['title' => 'Metode Pembayaran'])

@section('actions')
    <a href="{{ route('admin.payment-methods.create') }}" class="btn btn--primary">+ Tambah Metode</a>
@endsection

@section('content')
    @if ($methods->isEmpty())
        <div class="empty">
            <p>Belum ada metode pembayaran.</p>
            <a href="{{ route('admin.payment-methods.create') }}" class="btn btn--primary">Tambah metode</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Nomor Rekening</th>
                        <th>Atas Nama</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($methods as $method)
                        <tr>
                            <td><strong>{{ $method->label }}</strong></td>
                            <td class="mono">{{ $method->account_number ?: '—' }}</td>
                            <td class="muted">{{ $method->account_name ?: '—' }}</td>
                            <td class="muted">{{ $method->sort_order }}</td>
                            <td>
                                <span @class(['badge', 'badge--on' => $method->is_active, 'badge--muted' => ! $method->is_active])>
                                    {{ $method->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="nowrap">
                                <a href="{{ route('admin.payment-methods.edit', $method) }}" class="link">Edit</a>
                                <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}"
                                      onsubmit="return confirm('Hapus metode {{ $method->label }}?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link" style="color:var(--danger);">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        <ul class="cards only-mobile">
            @foreach($methods as $method)
                <li>
                    <div class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title">{{ $method->label }}</span>
                            <span @class(['badge', 'badge--on' => $method->is_active, 'badge--muted' => !$method->is_active])>{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="card-row__meta mono">{{ $method->account_number ?: '&mdash;' }} a.n {{ $method->account_name ?: '&mdash;' }}</p>
                        <div style="margin-top:6px"><a href="{{ route('admin.payment-methods.edit', $method) }}" class="link">Edit</a></div>
                    </div>
                </li>
            @endforeach
        </ul>
        </div>
    @endif
@endsection