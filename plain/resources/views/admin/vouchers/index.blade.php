@extends('admin.layouts.app', ['title' => 'Manajemen Voucher'])

@section('actions')
    <a href="{{ route('admin.vouchers.create') }}" class="btn btn--primary">Tambah Voucher</a>
@endsection

@section('content')
<div class="panel panel--flush">
    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe Diskon</th>
                <th>Minimal Belanja</th>
                <th>Masa Berlaku</th>
                <th>Dipakai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $voucher)
                <tr>
                    <td>
                        <strong class="mono">{{ $voucher->code }}</strong>
                        @if ($voucher->is_personal)
                            <div class="badge badge--warn small mt-1">Personal</div>
                        @endif
                        @if ($voucher->auto_type === 'birthday')
                            <div class="badge small mt-1">🎂 Ultah {{ $voucher->auto_year }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $voucher->name }}
                        @if ($voucher->user)
                            <div class="muted small">Untuk: {{ $voucher->user->displayName() }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $voucher->discount_type === 'nominal' ? \App\Support\PriceCalculator::formatRupiah($voucher->value) : $voucher->value . '%' }}
                        @if($voucher->max_discount_idr)
                            <div class="muted small">Maks: {{ \App\Support\PriceCalculator::formatRupiah($voucher->max_discount_idr) }}</div>
                        @endif
                    </td>
                    <td>{{ \App\Support\PriceCalculator::formatRupiah($voucher->min_purchase_idr) }}</td>
                    <td class="small muted">
                        {{ $voucher->starts_at ? $voucher->starts_at->format('d/m/Y') : 'Selamanya' }} -
                        {{ $voucher->ends_at ? $voucher->ends_at->format('d/m/Y') : 'Selamanya' }}
                    </td>
                    <td class="small">
                        {{ $voucher->redemptions_count ?? 0 }}×
                        @if($voucher->usage_limit)
                            <span class="muted">/ {{ $voucher->usage_limit }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $voucher->is_active ? 'badge--on' : 'badge--danger' }}">
                            {{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn--small">Edit</a>
                        <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn--small btn--danger-outline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
        <ul class="cards only-mobile">
            @foreach($vouchers as $voucher)
                <li>
                    <div class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title mono">{{ $voucher->code }}</span>
                            <span class="badge {{ $voucher->is_active ? 'badge--on' : 'badge--danger' }}">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="card-row__meta">{{ $voucher->name }} &middot; {{ $voucher->discount_type === 'nominal' ? \App\Support\PriceCalculator::formatRupiah($voucher->value) : $voucher->value . '%' }}</p>
                        <p class="card-row__meta">Dipakai {{ $voucher->redemptions_count ?? 0 }}&times; &middot; {{ $voucher->starts_at ? $voucher->starts_at->format('d/m/Y') : 'Selamanya' }}</p>
                        <div class="mt-2"><a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn--small">Edit</a></div>
                    </div>
                </li>
            @endforeach
        </ul>
    <div class="p-5">{{ $vouchers->links('admin.partials.pagination') }}</div>
</div>
@endsection