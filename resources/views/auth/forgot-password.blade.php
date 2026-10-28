@extends('layouts.guest')

@section('title', 'Lupa Password — Mercatoria')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-2xl font-semibold">Lupa Kata Sandi?</h1>
        <p class="mt-1 text-sm text-gray-500">
            Masukkan email yang terdaftar, dan kami akan mengirimkan tautan untuk mengatur ulang kata sandimu.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf

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
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white hover:bg-gray-800">
                KIRIM LINK RESET
            </button>

            <p class="text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Kembali ke halaman Masuk</a>
            </p>
        </form>
    </div>
@endsection
