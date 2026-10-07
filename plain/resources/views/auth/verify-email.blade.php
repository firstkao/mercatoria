@extends('layouts.app', ['title' => 'Verifikasi Email'])

@section('main_class', 'main--narrow')

@section('content')
<div class="account-page account-page--auth">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Verifikasi Email</p>
            <h1 class="account-head__title">Cek Email Kamu</h1>
            <div class="account-head__meta">
                <span>Kami sudah mengirim <strong>kode 6 digit</strong> ke <strong>{{ $user->email }}</strong>. Masukkan kode di bawah untuk mengaktifkan akun.</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice" role="status">{{ session('status') }}</div>
        @endif

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

        <form method="POST" action="{{ route('verification.verify') }}" class="account-form" novalidate>
            @csrf

            <div class="account-section">
                <label class="field field--full">
                    <span>Kode Verifikasi</span>
                    <input type="text"
                           name="otp"
                           inputmode="numeric"
                           pattern="\d{6}"
                           maxlength="6"
                           autocomplete="one-time-code"
                           placeholder="······"
                           required
                           autofocus
                           class="otp-input">
                    @include('partials.field-error', ['name' => 'otp'])
                </label>
            </div>

            <div class="account-section account-section--actions">
                <button type="submit" class="account-btn account-btn--block">Verifikasi Sekarang</button>
            </div>
        </form>

        <div class="account-verify__resend">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <span class="muted small">Tidak dapat kode?</span>
                <button type="submit" class="link-button">Kirim ulang kode</button>
            </form>
        </div>

        <p class="account-verify__help">
            Kode berlaku 15 menit. Kalau tidak ketemu, cek folder <strong>Spam</strong> atau <strong>Promotions</strong>.<br>
            Salah email?
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-verify').submit();">
                Keluar &amp; daftar ulang
            </a>
        </p>

        <form id="logout-verify" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
    </div>
</div>
@endsection
