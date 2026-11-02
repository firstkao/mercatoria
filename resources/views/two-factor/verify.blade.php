@extends('layouts.app')

@section('title', 'Verifikasi OTP — MERCATORIA')

@section('content')
    <div class="mx-auto max-w-md rounded-xl border border-rule bg-white p-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Keamanan</p>
        <h1 class="mt-2 font-display text-xl font-semibold text-ink">Verifikasi OTP Admin</h1>
        <p class="mt-2 text-sm text-ink/65">Kode 6 digit telah dikirim ke email kamu.</p>
        <p class="mt-1 font-mono text-xs text-ink/55">
            Berlaku: <span id="otp-countdown">05:00</span>
        </p>

        <div class="mt-5 h-px bg-rule"></div>

        <form method="POST" action="{{ route('2fa.verify.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="code" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode OTP</label>
                <input type="text" name="code" id="code" inputmode="numeric" maxlength="6" required autofocus
                       autocomplete="one-time-code"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-3 text-center font-mono text-lg tracking-[0.35em] text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('code')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                VERIFIKASI
            </button>
        </form>
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
