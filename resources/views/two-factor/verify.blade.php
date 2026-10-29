@extends('layouts.app')

@section('title', 'Verifikasi OTP — Mercatoria')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-xl font-semibold">Verifikasi OTP Admin</h1>
        <p class="mt-2 text-sm text-gray-600">Kode 6 digit telah dikirim ke email kamu. Berlaku 5 menit.</p>

        <form method="POST" action="{{ route('2fa.verify.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium">Kode OTP</label>
                <input type="text" name="code" id="code" inputmode="numeric" maxlength="6" required autofocus
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-center text-lg tracking-widest">
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white">VERIFIKASI</button>
        </form>
    </div>
@endsection
