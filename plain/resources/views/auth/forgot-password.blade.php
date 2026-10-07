@extends('layouts.app', ['title' => 'Lupa Kata Sandi'])

@section('main_class', 'main--narrow')

@section('content')
<div class="account-page account-page--auth">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Lupa Kata Sandi</h1>
            <div class="account-head__meta">
                <span>Masukkan email yang kamu pakai saat mendaftar. Kami akan kirim link untuk membuat kata sandi baru.</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="account-form" novalidate>
            @csrf

            <div class="account-section">
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @include('partials.field-error', ['name' => 'email'])
                    </label>
                </div>

                <div class="account-form__turnstile">
                    @include('partials.turnstile')
                </div>
            </div>

            <div class="account-section account-section--actions">
                <button type="submit" class="account-btn account-btn--block">Kirim Link Reset</button>
            </div>
        </form>

        <div class="account-auth-footer">
            Ingat kata sandi? <a href="{{ route('login') }}">Masuk</a>
        </div>
    </div>
</div>
@endsection
