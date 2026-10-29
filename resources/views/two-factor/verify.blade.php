@extends('layouts.app')

@section('title', 'Verifikasi OTP — Mercatoria')

@section('content')
    <div class="mx-auto max-w-md rounded-xl border border-rule bg-white p-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Keamanan</p>
        <h1 class="mt-2 font-display text-xl font-semibold text-ink">Verifikasi OTP Admin</h1>
        <p class="mt-2 text-sm text-ink/65">Kode 6 digit telah dikirim ke email kamu. Berlaku 5 menit.</p>

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
@endsection
