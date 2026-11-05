@extends('layouts.guest')

@section('title', 'Masuk - MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au">
        <div class="au-card">
            <p class="au-eyebrow">Akun</p>
            <h1 class="au-title">Masuk ke Akun</h1>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('login.store') }}" class="au-form">
                @csrf

                {{-- Honeypot (PRD 8.2) --}}
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

                <div>
                    <label for="password" class="au-label">Kata Sandi <i>*</i></label>
                    <div class="au-pw">
                        <input type="password" name="password" id="password" required class="au-input">
                        <button type="button" class="au-eye" data-pw-toggle="password" data-shown="0" aria-label="Tampilkan kata sandi">
                            <svg class="off" width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>
                            <svg class="on" width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <div class="au-row">
                    <label class="au-check" style="align-items:center">
                        <input type="checkbox" name="remember" value="1" style="margin:0">
                        Ingatkan saya
                    </label>
                    <a href="{{ route('password.request') }}" class="au-link" style="font-size:13px">Lupa Password?</a>
                </div>

                <x-turnstile />

                <button type="submit" class="au-btn">Masuk</button>

                <p class="au-center">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="au-link">Daftar Sekarang</a>
                </p>
            </form>
        </div>
    </div>
@endsection