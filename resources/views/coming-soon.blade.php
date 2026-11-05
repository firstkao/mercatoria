@extends('layouts.app')

@section('title', 'Menyusul — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au" style="max-width:576px;margin:0 auto">
        <div class="au-card" style="padding:48px 32px 40px;text-align:center">
            <p class="au-eyebrow">Info</p>
            <h1 class="au-title">Menyusul</h1>
            <p class="au-lead">
                Halaman ini belum dibangun. Fitur sedang dalam antrean pengembangan —
                tidak ada yang perlu dikhawatirkan dari pesananmu.
            </p>
            <a href="{{ route('dashboard') }}" class="au-btn" style="display:inline-block;width:auto;margin-top:24px;padding:0 28px">Kembali ke Dashboard</a>
        </div>
    </div>
@endsection