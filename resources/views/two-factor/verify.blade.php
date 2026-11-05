@extends('layouts.app')

@section('title', 'Verifikasi OTP — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }
        .tf-wrap { max-width: 480px; margin: 0 auto; }
    </style>

    <div class="au tf-wrap">
        <div class="au-card">
            <p class="au-eyebrow">Keamanan</p>
            <h1 class="au-title">Verifikasi OTP Admin</h1>
            <p class="au-lead">Kode 6 digit telah dikirim ke email kamu.</p>
            <p class="au-lead" style="margin-top:4px">
                Berlaku: <span id="otp-countdown" style="font-weight:700;color:#000">05:00</span>
            </p>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('2fa.verify.store') }}" class="au-form">
                @csrf
                <div>
                    <label for="code" class="au-label">Kode OTP</label>
                    <input type="text" name="code" id="code" inputmode="numeric" maxlength="6" required autofocus
                           autocomplete="one-time-code" class="au-input"
                           style="text-align:center;font-family:'Roboto',monospace;font-size:20px;letter-spacing:.35em;font-weight:700">
                    @error('code')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="au-btn">Verifikasi</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var el = document.getElementById('otp-countdown');
            if (!el) return;
            @isset($otpExpiresAt)
                var remaining = Math.max(0, Math.floor(({{ \Illuminate\Support\Carbon::parse($otpExpiresAt)->getTimestampMs() }} - Date.now()) / 1000));
            @else
                var remaining = 5 * 60;
            @endisset
            function render() {
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;
                el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }
            render();
            var t = setInterval(function () {
                if (remaining <= 0) { clearInterval(t); el.textContent = '00:00'; return; }
                remaining -= 1;
                render();
            }, 1000);
        })();
    </script>
@endsection
