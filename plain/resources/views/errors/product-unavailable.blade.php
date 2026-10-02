@extends('layouts.app', ['title' => $title ?? 'Produk sementara tidak tersedia'])

@section('content')
    {{-- Halaman fallback: dipakai ProductController::show() kalau render view
         utama gagal karena masalah data/relasi. Tujuannya supaya pengunjung
         tetap lihat halaman rapi (bukan 500 polos) DAN penyebab aslinya sudah
         tercatat di storage/logs/laravel.log untuk ditindak developer. --}}
    <div class="container" style="padding:64px 16px;text-align:center;">
        <div style="font-size:48px;line-height:1;">🛠️</div>
        <h1 style="margin:16px 0 8px;">Produk sementara tidak tersedia</h1>
        <p style="color:#666;max-width:480px;margin:0 auto 24px;">
            Halaman untuk <strong>{{ $productName ?? 'produk ini' }}</strong> sedang bermasalah
            dan sudah otomatis kami laporkan ke admin. Silakan coba lagi beberapa saat,
            atau jelajahi produk lain yang tersedia.
        </p>
        <a href="{{ route('catalog.index') }}" class="btn btn--primary">Lihat Katalog</a>
    </div>
@endsection
