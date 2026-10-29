@extends('layouts.guest')

@section('title', 'Atur Ulang Kata Sandi â€” Mercatoria')

@section('content')
    <div class="rounded-xl border border-rule bg-white px-6 py-8 shadow-[0_1px_0_rgba(20,27,34,0.04)] sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Atur Ulang Kata Sandi</h1>

        <div class="mt-6 h-px bg-rule"></div>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    <span class="text-ink/40">01</span>
                    Email
                    <span class="text-danger">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('email')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    <span class="text-ink/40">02</span>
                    Kata Sandi Baru
                    <span class="text-danger">*</span>
                    <span class="font-sans text-[11px] font-normal tracking-normal normal-case text-ink/45">(Min 8 karakter)</span>
                </label>
                <input type="password" name="password" id="password" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('password')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    <span class="text-ink/40">03</span>
                    Ulangi Kata Sandi Baru
                    <span class="text-danger">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                SIMPAN KATA SANDI BARU
            </button>
        </form>
    </div>
@endsection
