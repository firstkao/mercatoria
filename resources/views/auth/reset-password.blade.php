@extends('layouts.guest')

@section('title', 'Atur Ulang Kata Sandi — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au">
        <div class="au-card">
            <p class="au-eyebrow">Akun</p>
            <h1 class="au-title">Atur Ulang Kata Sandi</h1>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('password.update') }}" class="au-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="au-label">Email <i>*</i></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="au-input">
                    @error('email')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="au-label">Kata Sandi Baru <i>*</i> <small>(Min 8 karakter)</small></label>
                    <input type="password" name="password" id="password" required minlength="8" class="au-input">
                    @error('password')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="au-label">Ulangi Kata Sandi Baru <i>*</i></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" class="au-input">
                </div>

                <x-turnstile />

                <button type="submit" class="au-btn">Simpan Kata Sandi Baru</button>
            </form>
        </div>
    </div>
@endsection