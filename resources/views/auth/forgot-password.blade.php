@extends('layouts.guest')

@section('title', 'Lupa Password - MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au">
        <div class="au-card">
            <p class="au-eyebrow">Akun</p>
            <h1 class="au-title">Lupa Kata Sandi?</h1>
            <p class="au-lead">Masukkan email yang terdaftar dan sistem akan mengirimkan tautan untuk mengatur ulang kata sandimu.</p>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('password.email') }}" class="au-form">
                @csrf

                <div class="hidden" style="display:none" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    <label for="url">URL</label>
                    <input type="text" name="url" id="url" tabindex="-1" autocomplete="off">
                    <label for="phone2">Phone</label>
                    <input type="text" name="phone2" id="phone2" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label for="email" class="au-label">Email <i>*</i></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com" class="au-input">
                    @error('email')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <x-turnstile />

                <button type="submit" class="au-btn">Kirim Link Reset</button>

                <p class="au-center">
                    <a href="{{ route('login') }}" class="au-link">Kembali ke halaman Masuk</a>
                </p>
            </form>
        </div>
    </div>
@endsection