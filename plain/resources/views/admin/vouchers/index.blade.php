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
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $voucher)
                <tr>
                    <td><strong style="font-family: monospace; font-size: 1.1em;">{{ $voucher->code }}</strong></td>
                    <td>{{ $voucher->name }}</td>
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
                    <td><span class="badge {{ $voucher->is_active ? 'badge--on' : 'badge--danger' }}">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td style="display: flex; gap: 8px;">
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
    <div style="padding: 20px;">{{ $vouchers->links('admin.partials.pagination') }}</div>
</div>
@endsection
