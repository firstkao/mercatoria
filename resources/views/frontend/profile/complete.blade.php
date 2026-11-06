@extends('layouts.app')

@section('title', 'Lengkapi Profil — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box is-narrow">
        <div class="ac-head">
            <div>
                <h1 class="ac-title">Lengkapi Profil Kamu</h1>
                <p class="ac-lead">Tanggal lahir wajib diisi sebelum membuat pesanan. Terkunci permanen setelah disimpan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.complete.store') }}" class="au-form" style="margin-top:28px">
            @csrf

            <div>
                <label class="au-label">Tanggal Lahir <i>*</i></label>
                <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required class="au-input">
                @error('tgl_lahir')<p class="au-err">{{ $message }}</p>@enderror
            </div>

            <div><button type="submit" class="ac-btn">Simpan</button></div>
        </form>
    </div>
@endsection
