@extends('layouts.guest')

@section('title', 'Masuk - Mercatoria')

@section('content')
    <div class="rounded-xl border border-rule bg-white px-6 py-8 shadow-[0_1px_0_rgba(20,27,34,0.04)] sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">
            Masuk ke Akun
        </h1>

        <div class="mt-6 h-px bg-rule"></div>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
            @csrf

            {{-- Honeypot (PRD 8.2) --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                <label for="url">URL</label>
                <input type="text" name="url" id="url" tabindex="-1" autocomplete="off">
                <label for="phone2">Phone</label>
                <input type="text" name="phone2" id="phone2" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label for="email" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    Email
                    <span class="text-danger">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       placeholder="nama@email.com"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('email')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    Kata Sandi
                    <span class="text-danger">*</span>
                </label>
                <div class="relative mt-2">
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 pr-11 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <button type="button" onclick="const p=document.getElementById('password');const b=this;const s=p.type==='password';p.type=s?'text':'password';b.setAttribute('aria-label',s?'Sembunyikan kata sandi':'Tampilkan kata sandi');b.dataset.shown=s?'1':'0'"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-ink/40 transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/45"
                            aria-label="Tampilkan kata sandi">
                        <x-icons.eye-fill class="hidden [[data-shown='1']_&]:block" />
                        <x-icons.eye-slash-fill class="block [[data-shown='1']_&]:hidden" />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-ink/80">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-rule text-black focus:ring-accent/50">
                    Ingatkan saya
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">
                    Lupa Password?
                </a>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                MASUK
            </button>

            <p class="pt-1 text-center text-sm text-ink/65">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Daftar Sekarang</a>
            </p>
        </form>
    </div>
@endsection
