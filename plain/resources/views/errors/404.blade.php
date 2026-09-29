@extends('layouts.app', ['title' => 'Halaman tidak ditemukan'])

@section('content')
    <section class="card center" style="max-width: 480px;">
        <p class="eyebrow">Error 404</p>
        <h1>Halaman tidak ditemukan</h1>
        <p class="muted">Alamat yang kamu buka tidak ada atau sudah dipindahkan.</p>
        <p style="margin-top:20px;">
            <a href="{{ route('home') }}" class="button">Kembali ke beranda</a>
        </p>
    </section>
@endsection