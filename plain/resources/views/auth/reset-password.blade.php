@extends('layouts.app', ['title' => 'Reset Kata Sandi'])

@section('content')
    <section class="card card--narrow">
        <h1>Buat Kata Sandi Baru</h1>
        <p class="muted">Masukkan kata sandi baru untuk akunmu.</p>

        @if ($errors->any())
            <div class="alert alert--danger" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)
                    <p style="margin:0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="form" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required readonly>
                @include('partials.field-error', ['name' => 'email'])
            </label>

            <label class="field">
                <span>Kata sandi baru</span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
                <small class="hint">Minimal 8 karakter, harus ada huruf dan angka.</small>
                @include('partials.field-error', ['name' => 'password'])
            </label>

            <label class="field">
                <span>Ulangi kata sandi baru</span>
                <input type="password" name="password_confirmation" autocomplete="new-password" required>
            </label>

            <button type="submit" class="button button--block">Simpan Kata Sandi</button>
        </form>
    </section>
@endsection