@extends('layouts.app')

@section('title', 'Pesanan Saya — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        .ol, .ol *, .ol *::before, .ol *::after { box-sizing: border-box; }
        .ol {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: min(1300px, calc(100vw - 48px));
            margin: 32px 0 28px;
            font-family: 'Roboto', system-ui, sans-serif;
            color: #3d3439;
            -webkit-font-smoothing: antialiased;
        }
        .ol a { text-decoration: none; }

        .ol-head { padding: 36px 40px 28px; background: #fff; }
        .ol-title { margin: 0; font-size: 32px; line-height: 40px; font-weight: 500; color: #26222f; }

        .ol-empty { margin-top: 24px; padding: 48px 24px; background: #fff; text-align: center; font-size: 15px; color: #6f6a72; }
        .ol-link { color: #02a1ea; }
        .ol-link:hover { text-decoration: underline; }

        .ol-table-wrap { margin-top: 20px; background: #fff; }
        .ol-table { width: 100%; border-collapse: collapse; }
        .ol-table th { padding: 14px 20px; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #888; }
        .ol-table td { padding: 16px 20px; border-bottom: 1px solid #f5f5f5; font-size: 14px; color: #333; }
        .ol-table tbody tr:last-child td { border-bottom: 0; }
        .ol-no { font-weight: 600; color: #26222f; }
        .ol-price { color: #02a1ea; font-weight: 500; }

        @media (max-width: 767px) {
            .ol-head { padding: 24px 16px; }
            .ol-title { font-size: 26px; }
            .ol-table th, .ol-table td { padding: 12px 14px; }
        }
    </style>

    <div class="ol">
        <div class="ol-head">
            <h1 class="ol-title">Pesanan Saya</h1>
        </div>

        @if ($orders->isEmpty())
            <div class="ol-empty">
                Belum ada pesanan. <a href="/" class="ol-link">Mulai belanja</a>
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

            <div style="margin-top:20px">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
