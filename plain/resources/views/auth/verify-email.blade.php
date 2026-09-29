@extends('layouts.app', ['title' => 'Verifikasi Email'])

@section('content')
    <section class="card card--narrow">
        <p class="eyebrow">Verifikasi Email</p>
        <h1>Cek Email Kamu 📬</h1>
        <p class="muted">Kami sudah mengirim <strong>kode 6 digit</strong> ke <strong>{{ $user->email }}</strong>. Masukkan kode di bawah untuk mengaktifkan akun.</p>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert--danger" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)
                    <p style="margin:0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('verification.verify') }}" class="form" novalidate>
            @csrf

            <label class="field">
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
                       style="font-family: ui-monospace, monospace; font-size: 32px; letter-spacing: 0.4em; text-align: center; padding: 16px;">
                @include('partials.field-error', ['name' => 'otp'])
            </label>

            <button type="submit" class="button button--block">Verifikasi Sekarang</button>
        </form>

        <div style="margin-top:20px;text-align:center;">
            <form method="POST" action="{{ route('verification.resend') }}" style="display:inline;">
                @csrf
                <span class="muted" style="font-size:14px;">Tidak dapat kode? </span>
                <button type="submit" class="link-button" style="font-weight:600;">Kirim ulang kode</button>
            </form>
        </div>

        <p class="muted" style="margin-top:24px;font-size:13px;text-align:center;">
            Kode berlaku 15 menit. Kalau tidak ketemu, cek folder <strong>Spam</strong> atau <strong>Promotions</strong>.<br>
            Salah email? <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-verify').submit();" class="link">Keluar & daftar ulang</a>
        </p>

        <form id="logout-verify" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
    </section>
@endsection