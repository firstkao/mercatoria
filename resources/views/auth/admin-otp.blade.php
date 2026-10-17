@extends('layouts.auth')

@section('title', 'Verifikasi Admin')

@section('content')
<div class="w-full max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Admin</h1>
    <p class="text-sm text-gray-600 mb-6">
        Kode 6 digit telah dikirim ke {{ $email }}. Kode berlaku selama 5 menit.
    </p>

    <form method="POST" action="{{ route('admin.otp.verify') }}" class="space-y-4">
        @csrf
        <div>
            <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">Kode OTP</label>
            <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}"
                maxlength="6" autocomplete="one-time-code" required autofocus
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-xl tracking-[0.4em]">
            @error('otp')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full rounded-lg bg-black py-2.5 font-medium text-white hover:bg-gray-800">
            Verifikasi dan masuk
        </button>
    </form>
</div>
@endsection
