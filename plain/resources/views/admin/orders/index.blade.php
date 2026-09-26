@extends('admin.layouts.app', ['title' => 'Manajemen Pesanan'])

@section('content')
<section class="panel panel--flush">
    <div class="panel__head" style="padding: 20px;">
        <h2>Daftar Pesanan</h2>
        <form method="GET" action="{{ route('admin.orders.index') }}" style="display: flex; gap: 10px;">
            <select name="status" onchange="this.form.submit()" class="input-compact">
                <option value="">Semua Status</option>
                <option value="menunggu_pembayaran" @selected($status === 'menunggu_pembayaran')>Menunggu Pembayaran</option>
                <option value="ditahan" @selected($status === 'ditahan')>Ditahan (Cek Bukti)</option>
                <option value="pembayaran_diterima" @selected($status === 'pembayaran_diterima')>Pembayaran Diterima</option>
                <option value="sedang_diproses" @selected($status === 'sedang_diproses')>Sedang Diproses</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
            </select>
        </form>
    </div>

    @if($orders->isEmpty())
        <div class="empty"><p>Belum ada pesanan yang sesuai.</p></div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Tagihan Saat Ini</th>
                    <th>Skema</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td style="font-family: monospace; font-weight: bold;">{{ $order->order_number }}</td>
                        <td>
                            <strong>{{ $order->user->full_name ?? 'Data Dihapus' }}</strong>
                            <div class="muted small">{{ $order->user->email ?? '' }}</div>
                        </td>
                        <td style="color: var(--accent-strong); font-weight: bold;">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</td>
                        <td>{{ $order->payment_scheme }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'ditahan' ? 'badge--warn' : '' }} {{ $order->status === 'selesai' ? 'badge--on' : '' }}">
                                {{ str_replace('_', ' ', Str::title($order->status)) }}
                            </span>
                        </td>
                        <td class="muted small">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn--small">Detail & Verifikasi</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 20px;">
            {{ $orders->links('admin.partials.pagination') }}
        </div>
    @endif
</section>
@endsection
