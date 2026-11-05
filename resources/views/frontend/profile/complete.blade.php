@extends('layouts.app')

@section('title', 'Lengkapi Profil — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au">
        <div class="au-card">
            <p class="au-eyebrow">Akun</p>
            <h1 class="au-title">Lengkapi Profil Kamu</h1>
            <p class="au-lead">Tanggal lahir wajib diisi sebelum membuat pesanan. Terkunci permanen setelah disimpan.</p>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('profile.complete.store') }}" class="au-form">
                @csrf

                <div>
                    <label class="au-label">Tanggal Lahir <i>*</i></label>
                    <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required class="au-input">
                    @error('tgl_lahir')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="au-btn">SIMPAN</button>
            </form>
        </div>
    </div>
@endsection
