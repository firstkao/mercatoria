@extends('layouts.app', ['title' => 'Terjadi kesalahan'])

@section('content')
    <section class="card center" style="max-width: 480px;">
        <p class="eyebrow">Error 500</p>
        <h1>Terjadi kesalahan</h1>
        <p class="muted">Maaf, ada masalah di server kami. Coba lagi beberapa saat.</p>
        <p style="margin-top:20px;">
            <a href="{{ route('home') }}" class="button">Kembali ke beranda</a>
        </p>
    </section>
@endsection