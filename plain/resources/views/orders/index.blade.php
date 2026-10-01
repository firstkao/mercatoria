@extends('layouts.app', ['title' => 'Pesanan Saya'])

@section('content')
<section class="card" style="max-width: 1000px;">
    <h1>Pesanan Saya</h1>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    {{-- FASE 1: "Tagihan Menunggu" — pintasan aksi bayar/unggah bukti.
         Menggantikan menu global 'Konfirmasi Pembayaran': konfirmasi hanya
         relevan per-order, jadi CTA-nya muncul di sini untuk order yang
         benar-benar butuh pembayaran. --}}
    @if ($unpaid->isNotEmpty())
        <div class="panel stack" style="border-left: 4px solid var(--accent-strong); margin-bottom: 20px;">
            <h2 style="margin:0;">Tagihan Menunggu</h2>
            @foreach($unpaid as $u)
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:8px 0; border-top:1px solid #eee;">
                    <div>
                        <strong style="font-family:monospace;">{{ $u->order_number }}</strong>
                        <span class="muted"> · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }} ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})</span>
                        @if($u->payment_deadline_at)
                            <div class="muted" style="font-size:12px;">Batas bayar: {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</div>
                        @endif
                    </div>
                    <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti" class="button button--small">Bayar &amp; Unggah Bukti</a>
                </div>
            @endforeach
        </div>
    @endif

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
                        <th>Tagihan</th>
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
                                {{-- ✅ Rapikan: pakai helper model (label + badge warna
                                     dari OrderStatus enum) agar konsisten dengan halaman
                                     detail; sebelumnya manual str_replace/Str::title. --}}
                                <span class="badge {{ $order->statusBadgeClass() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('account.orders.show', $order->order_number) }}" class="button button--small">Detail & Bayar</a>
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