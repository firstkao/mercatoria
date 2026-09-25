@extends('layouts.app', ['title' => 'Pesanan'])
@section('content')
<h1>Pesanan saya</h1>
<div class="card"><ul>@forelse ($orders as $order)<li><a href="{{ route('orders.show', $order->order_number) }}">{{ $order->order_number }}</a> — {{ $order->status }} — Rp{{ number_format($order->pay_now_idr, 0, ',', '.') }}</li>@empty<li>Belum ada pesanan.</li>@endforelse</ul>{{ $orders->links('partials.pagination') }}</div>
@endsection
