@extends('layouts.app')

@section('title', 'Verifikasi OTP — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au" style="max-width:448px;margin:0 auto">
        <div class="au-card">
            <p class="au-eyebrow">Keamanan</p>
            <h1 class="au-title" style="font-size:24px;line-height:30px">Verifikasi OTP Admin</h1>
            <p class="au-lead">Kode 6 digit telah dikirim ke email kamu.</p>
            <p class="au-lead" style="margin-top:4px;font-size:13px;color:#888">
                Berlaku: <span id="otp-countdown" style="font-variant-numeric:tabular-nums;color:#000;font-weight:700">05:00</span>
            </p>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('2fa.verify.store') }}" class="au-form">
                @csrf
                <div>
                    <label for="code" class="au-label">Kode OTP</label>
                    <input type="text" name="code" id="code" inputmode="numeric" maxlength="6" required autofocus
                           autocomplete="one-time-code" class="au-input"
                           style="height:52px;text-align:center;font-size:22px;font-weight:500;letter-spacing:.4em;padding-left:calc(14px + .4em);font-variant-numeric:tabular-nums">
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