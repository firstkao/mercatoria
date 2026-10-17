@extends('layouts.auth')

@section('title', 'Atur Password Baru')

@section('content')
<div class="w-full max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Atur Password Baru</h1>
    <p class="text-sm text-gray-600 mb-6">
        Bikin password baru buat akun kamu. Minimal 8 karakter.
    </p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                readonly
                autocomplete="email"
                class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed
                       {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400
                       {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Ulangi Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400"
            >
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            Simpan Password
        </button>
    </form>
</div>
@endsection