@extends('layouts.app', ['title' => 'Masuk'])

@section('content')
    <section class="card card--narrow">
        <h1>Masuk</h1>
        <p class="muted">Katalog hanya bisa dilihat setelah masuk.</p>

        <form method="POST" action="{{ route('login') }}" class="form" novalidate>
            @csrf

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @include('partials.field-error', ['name' => 'email'])
            </label>

            <label class="field">
                <span>Kata sandi</span>
                <input type="password" name="password" autocomplete="current-password" required>
                @include('partials.field-error', ['name' => 'password'])
            </label>

            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat saya</span>
            </label>

            @include('partials.turnstile')

            <button type="submit" class="button button--block">Masuk</button>
        </form>

        <p class="muted center">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </section>
@endsection