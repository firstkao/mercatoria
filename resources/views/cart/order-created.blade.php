@extends('layouts.app')

@section('title', 'Pesanan Dibuat')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-12">
    <div class="rounded-xl border border-border bg-white p-6 text-center">
        <h1 class="text-2xl font-bold text-navy-500">Pesanan berhasil dibuat</h1>
        <p class="mt-3 text-text-muted">Nomor pesanan: <strong>{{ $order->order_no }}</strong></p>
        <p class="mt-2 text-lg font-semibold">{{ format_rupiah($order->total) }}</p>
        <a href="{{ route('cart.index') }}" class="mt-6 inline-block font-semibold text-navy-500 hover:underline">Kembali ke keranjang</a>
    </div>
</div>
@endsection
