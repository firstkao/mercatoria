@extends('layouts.app', ['title' => 'Kuota habis'])

@section('content')
    <section class="card card--narrow center">
        <h1>Kuota lihat produk habis</h1>
        <p>Kamu sudah membuka {{ $viewQuota }} halaman produk.</p>
        @if ($hasCartItems)
            <p>Kamu masih bisa menyelesaikan belanja dari keranjang.</p>
        @else
            <p>Detail produk terkunci. Kalau ingin berbelanja, hubungi admin melalui chat untuk meminta reset kuota.</p>
        @endif
        <p><a href="{{ route('catalog.index') }}" class="button">Kembali ke katalog</a></p>
    </section>
@endsection