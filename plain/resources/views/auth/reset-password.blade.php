@extends('layouts.app', ['title' => 'Reset Kata Sandi'])

@section('main_class', 'main--narrow')

@section('content')
<div class="account-page account-page--auth">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Buat Kata Sandi Baru</h1>
            <div class="account-head__meta">
                <span>Masukkan kata sandi baru untuk akunmu.</span>
            </div>
        </header>

        @if ($errors->any())
            <div class="account-alert account-alert--danger" role="alert">
                <strong class="account-alert__title">Periksa kembali</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="account-form" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="account-section">
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required readonly class="is-locked">
                        @include('partials.field-error', ['name' => 'email'])
                    </label>

                    <label class="field field--full">
                        <span>Kata Sandi Baru</span>
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
                        <small class="hint">Minimal 8 karakter, harus ada huruf dan angka.</small>
                        @include('partials.field-error', ['name' => 'password'])
                    </label>

                    <label class="field field--full">
                        <span>Ulangi Kata Sandi Baru</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                </div>
            </div>

            <div class="account-section account-section--actions">
                <button type="submit" class="account-btn account-btn--block">Simpan Kata Sandi</button>
            </div>
        </form>
    </div>
</div>
@endsection
