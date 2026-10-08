@extends('admin.layouts.app', ['title' => 'Manajemen Pesanan'])

@section('content')
<x-admin.card padding="flush">
    <div class="panel__head p-5">
        <h2>Daftar Pesanan</h2>
        <form method="GET" action="{{ route('admin.orders.index') }}" class="actions">
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
                        <td class="mono"><strong>{{ $order->order_number }}</strong></td>
                        <td>
                            <strong>{{ $order->user->full_name ?? 'Data Dihapus' }}</strong>
                            <div class="muted small">{{ $order->user->email ?? '' }}</div>
                        </td>
                        <td class="text-price">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</td>
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
        <ul class="cards only-mobile">
            @foreach($orders as $order)
                <li>
                    <a href="{{ route('admin.orders.show', $order) }}" class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title mono">{{ $order->order_number }}</span>
                            <span class="badge {{ $order->status === 'ditahan' ? 'badge--warn' : '' }} {{ $order->status === 'selesai' ? 'badge--on' : '' }}">{{ str_replace('_', ' ', Str::title($order->status)) }}</span>
                        </div>
                        <p class="card-row__meta">{{ $order->user->full_name ?? 'Data Dihapus' }}</p>
                        <p class="card-row__meta">Bayar sekarang: {{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }} · Skema {{ $order->payment_scheme }}</p>
                        <p class="card-row__meta">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="p-5">
            {{ $orders->links('admin.partials.pagination') }}
        </div>
    @endif
</x-admin.card>
@endsection
