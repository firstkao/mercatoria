@extends('layouts.auth')

@section('title', 'Verifikasi 2FA Admin')

@section('content')
<div class="mx-auto w-full max-w-md"
     x-data="{
        digits: ['', '', '', '', '', ''],
        remaining: {{ max(0, (int) now()->diffInSeconds($expiresAt, false)) }},
        init() {
            window.setInterval(() => { if (this.remaining > 0) this.remaining--; }, 1000);
        },
        get code() { return this.digits.join(''); },
        get timer() {
            return String(Math.floor(this.remaining / 60)).padStart(2, '0') + ':' + String(this.remaining % 60).padStart(2, '0');
        },
        type(index, event) {
            const value = event.target.value.replace(/\D/g, '').slice(-1);
            this.digits[index] = value;
            event.target.value = value;
            if (value && index < 5) this.$refs['d' + (index + 1)].focus();
        },
        back(index, event) {
            if (!this.digits[index] && index > 0) this.$refs['d' + (index - 1)].focus();
        },
        paste(event) {
            const text = (event.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
            if (!text) return;
            event.preventDefault();
            text.split('').forEach((char, i) => { this.digits[i] = char; this.$refs['d' + i].value = char; });
            this.$refs['d' + Math.min(text.length, 5)].focus();
        }
     }">
    <h1 class="mb-2 text-xl font-bold text-gray-900">🔐 VERIFIKASI 2FA ADMIN (OTP EMAIL)</h1>
    <p class="mb-6 text-sm text-gray-600">
        Kode 6 digit telah dikirim dari {{ $securityEmail }} ke email kamu ({{ $email }}).
    </p>

    <form method="POST" action="{{ route('admin.otp.verify') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="otp" :value="code">
        <div class="flex justify-center gap-2" role="group" aria-label="Kode OTP 6 digit" x-on:paste="paste($event)">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" inputmode="numeric" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                       aria-label="Digit {{ $i + 1 }}" x-ref="d{{ $i }}" @if ($i === 0) autofocus @endif
                       x-on:input="type({{ $i }}, $event)" x-on:keydown.backspace="back({{ $i }}, $event)"
                       class="h-12 w-11 rounded-lg border border-gray-300 text-center text-xl font-semibold focus:border-black focus:ring-black">
            @endfor
        </div>
        @error('otp')
            <p class="text-center text-xs text-red-600">{{ $message }}</p>
        @enderror
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-gray-600">Berlaku: <strong x-text="timer" :class="remaining === 0 ? 'text-red-600' : ''"></strong></span>
            <button type="submit" :disabled="code.length !== 6 || remaining === 0"
                    class="rounded-lg bg-black px-4 py-2.5 font-medium text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50">
                Verifikasi &amp; Masuk
            </button>
        </div>
        <p x-show="remaining === 0" x-cloak class="text-center text-xs text-red-600">
            Kode kedaluwarsa. <a href="{{ route('login') }}" class="underline">Login kembali</a> untuk meminta kode baru.
        </p>
    </form>
</div>
@endsection
