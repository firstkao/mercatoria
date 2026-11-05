@extends('layouts.app')

@section('title', 'Pesanan Saya — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Daftar pesanan — CSS biasa (tidak bergantung build Tailwind), selaras dengan beranda & halaman produk. */
        .ol, .ol *, .ol *::before, .ol *::after { box-sizing: border-box; }
        .ol { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .ol a { text-decoration: none; }

        .ol-empty { margin-top: 24px; padding: 40px 24px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; text-align: center; font-size: 14px; line-height: 21px; color: #555; }

        .ol-table-wrap { margin-top: 16px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .ol-table { width: 100%; border-collapse: collapse; }
        .ol-table th { padding: 12px 16px; border-bottom: 1px solid #e1e1e1; text-align: left; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .ol-table td { padding: 14px 16px; border-bottom: 1px solid #e1e1e1; font-size: 14px; line-height: 21px; color: #333; }
        .ol-table tbody tr:last-child td { border-bottom: 0; }
        .ol-no { font-weight: 500; color: #000; }
        .ol-price { color: #02a1ea; }
        .ol-link { color: #02a1ea; font-size: 14px; }
        .ol-link:hover { text-decoration: underline; }
    </style>

    <div class="au ol">
        <p class="au-eyebrow">Pesanan</p>
        <h1 class="au-title">Pesanan Saya</h1>

        @if ($orders->isEmpty())
            <div class="ol-empty">
                Belum ada pesanan.
                <a href="/" class="ol-link">Mulai belanja</a>
            </div>
        @else
            <div class="ol-table-wrap">
                <table class="ol-table">
                    <thead>
                        <tr>
                            <th>No Order</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="ol-no">{{ $order->no_order }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="ol-price">{{ format_rp($order->total) }}</td>
                                <td><x-order-status-badge :status="$order->status" /></td>
                                <td style="text-align:right">
                                    <a href="{{ route('orders.show', $order->no_order) }}" class="ol-link">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
