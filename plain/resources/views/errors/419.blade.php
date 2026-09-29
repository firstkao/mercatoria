@extends('layouts.app', ['title' => 'Sesi berakhir'])

@section('content')
    <section class="card center" style="max-width: 480px;">
        <p class="eyebrow">Error 419</p>
        <h1>Sesi kamu berakhir</h1>
        <p class="muted">Halaman ini sudah lama terbuka. Muat ulang untuk melanjutkan.</p>
        <p style="margin-top:20px;">
            <a href="{{ url()->current() }}" class="button">Muat ulang</a>
            <a href="{{ route('home') }}" class="button" style="background:#e5e7eb;color:#111;">Beranda</a>
        </p>
    </section>
@endsection