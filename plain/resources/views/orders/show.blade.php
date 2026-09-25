@extends('layouts.app', ['title' => 'Detail Pesanan'])
@section('content')
<h1>{{ $order->order_number }}</h1>
@if (session('status')) <div class="notice">{{ session('status') }}</div> @endif
<div class="card"><p>Status: <strong>{{ $order->status }}</strong></p><p>Bayar sekarang: <strong>Rp{{ number_format($order->pay_now_idr, 0, ',', '.') }}</strong></p><p>Batas pembayaran: {{ $order->payment_deadline_at }}</p><ul>@foreach ($items as $item)<li>{{ $item->product_name_snapshot }} — {{ $item->variant_name_snapshot }} × {{ $item->quantity }}</li>@endforeach</ul></div>
@if ($order->status === 'menunggu_pembayaran')
<div class="card"><h2>Upload bukti pembayaran</h2><form method="POST" enctype="multipart/form-data" action="{{ route('orders.proof', $order->order_number) }}">@csrf
<label>Metode<select name="payment_method_id" required>@foreach ($methods as $method)<option value="{{ $method->id }}">{{ $method->label }}</option>@endforeach</select></label>
<label>Nominal<input type="number" name="amount_idr" value="{{ $order->pay_now_idr }}" min="1" required></label><label>File bukti<input type="file" name="proof" accept="image/*" required></label><button class="button">Upload bukti</button></form></div>
@endif
@endsection
