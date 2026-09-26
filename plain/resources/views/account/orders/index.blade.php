@extends('layouts.app', ['title' => 'Order Saya'])

@section('content')
<section class="card" style="max-width: 1000px;">
    <h1>Order Saya</h1>
    
    @if($orders->isEmpty())
        <div class="empty">
            <p>Kamu belum memiliki pesanan.</p>
            <a href="{{ route('catalog.index') }}" class="button">Mulai Belanja</a>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total Tagihan</th>
                        <th>Skema</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td style="font-family: monospace; font-weight: bold;">{{ $order->order_number }}</td>
                            <td class="muted">{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}</td>
                            <td style="color: var(--accent-strong); font-weight: bold;">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</td>
                            <td>{{ $order->payment_scheme }}</td>
                            <td>
                                <span class="badge 
                                    {{ in_array($order->status, ['dibatalkan', 'pembayaran_gagal']) ? 'badge--danger' : '' }}
                                    {{ $order->status === 'selesai' ? 'badge--on' : '' }}
                                ">
                                    {{ str_replace('_', ' ', Str::title($order->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('account.orders.show', $order) }}" class="button button--small">Detail & Bayar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px;">
            {{ $orders->links('partials.pagination') }}
        </div>
    @endif
</section>
@endsection
