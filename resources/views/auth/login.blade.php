@extends('layouts.auth')

@section('title', 'Masuk')
@section('subtitle', 'Katalog hanya bisa dilihat setelah masuk')

@section('content')

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    {{-- Honeypot --}}
    <div class="hidden" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
        <input type="text" name="url" tabindex="-1" autocomplete="off">
        <input type="text" name="phone2" tabindex="-1" autocomplete="off">
    </div>

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
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password <span class="text-red-500">*</span>
        </label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror">
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
            Lupa password?
        </a>
    </div>

    {{-- Submit --}}
    <button type="submit"
        class="w-full bg-black text-white py-2.5 rounded-lg font-medium hover:bg-gray-800 transition mt-2">
        Masuk
    </button>

    {{-- Info Banner --}}
    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded text-sm text-blue-700 mt-4">
        ℹ️ Katalog hanya bisa dilihat setelah masuk. Daftar dulu kalau belum punya akun.
    </div>

    {{-- Link Register --}}
    <p class="text-center text-sm text-gray-600 mt-4">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-primary-500 font-medium hover:underline">Daftar di sini</a>
    </p>

</form>

@endsection
