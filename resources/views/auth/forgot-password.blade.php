@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<div class="w-full max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Lupa Password?</h1>
    <p class="text-sm text-gray-600 mb-6">
        Masukin email kamu, nanti kami kirim link buat atur password baru.
    </p>

    @if (session('status'))
        <div class="mb-4 rounded-lg border-l-4 border-green-600 bg-green-50 p-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-honeypot />
        <x-turnstile />

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400
                       {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            Kirim Link Reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Udah ingat?
        <a href="{{ route('login') }}" class="font-medium text-[#1a4d8f] hover:underline">Kembali ke login</a>
    </p>
</div>
@endsection