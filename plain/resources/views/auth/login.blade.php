@extends('layouts.app', ['title' => 'Masuk'])

@section('main_class', 'main--narrow')

@section('content')
<div class="account-page account-page--auth">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Masuk</h1>
            <div class="account-head__meta">
                <span>Katalog hanya bisa dilihat setelah masuk.</span>
            </div>
        </header>

        <form method="POST" action="{{ route('login') }}" class="account-form" novalidate>
            @csrf

            <div class="account-section">
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @include('partials.field-error', ['name' => 'email'])
                    </label>

                    <label class="field field--full">
                        <span>Kata Sandi</span>
                        <input type="password" name="password" autocomplete="current-password" required>
                        @include('partials.field-error', ['name' => 'password'])
                    </label>

                    <label class="check field--full">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <div class="account-form__turnstile">
                    @include('partials.turnstile')
                </div>
            </div>

            <div class="account-section account-section--actions">
                <button type="submit" class="account-btn account-btn--block">Masuk</button>
            </div>
        </form>

        <div class="account-auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </div>
    </div>
</div>
@endsection
