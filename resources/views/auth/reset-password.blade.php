@extends('layouts.guest')

@section('title', 'Atur Ulang Kata Sandi — Mercatoria')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-2xl font-semibold">Atur Ulang Kata Sandi</h1>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium">Kata Sandi Baru <span class="text-red-500">*</span>
                    <span class="text-xs text-gray-400">(Min 8 karakter)</span>
                </label>
                <input type="password" name="password" id="password" required minlength="8"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium">Ulangi Kata Sandi Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white hover:bg-gray-800">
                SIMPAN KATA SANDI BARU
            </button>
        </form>
    </div>
@endsection
