@extends('layouts.auth')

@section('title', 'Masuk')
@section('heading', 'Masuk ke Akun Mercatoria Kamu')
@section('subtitle', 'Katalog hanya bisa dilihat setelah masuk')

@section('content')

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <x-honeypot />

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-red-500">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
            required autofocus autocomplete="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror">
        @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div x-data="{ show: false }">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Kata Sandi <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input :type="show ? 'text' : 'password'" type="password" id="password" name="password" required autocomplete="current-password"
                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror">
            <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                class="absolute inset-y-0 right-0 px-3 text-base">👁️</button>
        </div>
        @error('password')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Remember + Lupa Password --}}
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" value="1"
                class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500">
            <span class="text-sm text-gray-600">Ingat saya</span>
        </label>

        <a href="{{ route('password.request') }}" class="text-sm text-[#1a4d8f] hover:underline">
            Lupa Password? →
        </a>
    </div>

    <x-turnstile />

    {{-- Submit --}}
    <x-button type="submit" variant="auth" class="w-full py-2.5">Masuk</x-button>

    {{-- Link Register --}}
    <p class="text-center text-sm text-gray-600 mt-4">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-primary-500 font-medium hover:underline">Daftar Sekarang →</a>
    </p>

</form>

@endsection
