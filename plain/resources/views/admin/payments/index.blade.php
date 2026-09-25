@extends('admin.layouts.app', ['title' => 'Pembayaran'])

@section('filters')
    <form method="GET" action="{{ route('admin.payments.index') }}" class="filters">
        <select name="status" aria-label="Status">
            <option value="all" @selected($status === 'all')>Semua status</option>
            <option value="pending" @selected($status === 'pending')>Menunggu verifikasi</option>
            <option value="approved" @selected($status === 'approved')>Disetujui</option>
            <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
        </select>
    </form>
@endsection

@section('content')
    @if ($proofs->isEmpty())
        <div class="panel">
            <p class="muted text-center">Tidak ada bukti pembayaran.</p>
        </div>
    @else
        <div class="panel panel--flush only-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Pembeli</th>
                        <th>Metode</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Diunggah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proofs as $proof)
                        <tr>
                            <td class="table__name">
                                <a href="{{ route('admin.payments.show', $proof->id) }}" class="table__title">
                                    {{ $proof->order_number }}
                                </a>
                            </td>
                            <td class="muted">
                                {{ $proof->full_name }}<br>
                                <small>{{ $proof->email }}</small>
                            </td>
                            <td class="muted">{{ $proof->payment_method_label }}</td>
                            <td class="nowrap">Rp {{ number_format($proof->amount_idr, 0, ',', '.') }}</td>
                            <td>
                                <span @class(['badge', 'badge--on' => $proof->status === 'approved', 'badge--off' => $proof->status === 'rejected'])>
                                    @match($proof->status)
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    @endmatch
                                </span>
                            </td>
                            <td class="muted nowrap">
                                {{ $proof->uploaded_at ? $proof->uploaded_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') : '—' }} WIB
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <ul class="cards only-mobile">
            @foreach ($proofs as $proof)
                <li>
                    <a href="{{ route('admin.payments.show', $proof->id) }}" class="card-row">
                        <div class="card-row__body">
                            <p class="card-row__title">{{ $proof->order_number }}</p>
                            <p class="card-row__subtitle">{{ $proof->full_name }}</p>
                            <p class="card-row__meta">
                                Rp {{ number_format($proof->amount_idr, 0, ',', '.') }} · 
                                <span @class(['badge-small', 'badge-small--on' => $proof->status === 'approved', 'badge-small--off' => $proof->status === 'rejected'])>
                                    @match($proof->status)
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    @endmatch
                                </span>
                            </p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="pagination">
            {{ $proofs->links() }}
        </div>
    @endif
@endsection
