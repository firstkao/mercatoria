@extends('layouts.guest')

@section('title', 'Masuk — Mercatoria')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-2xl font-semibold">Masuk ke Akun Mercatoria Kamu</h1>
        <p class="mt-1 text-sm text-gray-500">Katalog hanya bisa dilihat setelah masuk</p>

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
                <label for="email" class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       placeholder="nama@email.com"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium">Kata Sandi <span class="text-red-500">*</span></label>
                <div class="relative mt-1">
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    <button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400" aria-label="Tampilkan kata sandi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-gray-600 underline hover:text-black">
                    Lupa Password?
                </a>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white hover:bg-gray-800">
                MASUK
            </button>

            <p class="text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-black underline">Daftar Sekarang</a>
            </p>
        </form>
    </div>
@endsection
