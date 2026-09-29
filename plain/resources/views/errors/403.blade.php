@extends('layouts.app', ['title' => 'Akses ditolak'])

@section('content')
    <section class="card center" style="max-width: 480px;">
        <p class="eyebrow">Error 403</p>
        <h1>Akses ditolak</h1>
        <p class="muted">Kamu tidak punya izin untuk membuka halaman ini.</p>
        <p style="margin-top:20px;">
            <a href="{{ route('home') }}" class="button">Kembali ke beranda</a>
        </p>
    </section>
@endsection