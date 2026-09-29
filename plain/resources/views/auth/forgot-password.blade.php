@extends('layouts.app', ['title' => 'Lupa Kata Sandi'])

@section('content')
    <section class="card card--narrow">
        <h1>Lupa Kata Sandi</h1>
        <p class="muted">Masukkan email yang kamu pakai saat mendaftar. Kami akan kirim link untuk membuat kata sandi baru.</p>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="form" novalidate>
            @csrf

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @include('partials.field-error', ['name' => 'email'])
            </label>

            @include('partials.turnstile')

            <button type="submit" class="button button--block">Kirim Link Reset</button>
        </form>

        <p class="muted center" style="margin-top:16px;">
            Ingat kata sandi? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </section>
@endsection