@extends('layouts.auth')

@section('title', 'Verifikasi Email')

@section('content')
<div class="w-full max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Email Dulu</h1>
    <p class="text-sm text-gray-600 mb-6">
        Kami udah kirim link verifikasi ke <strong>{{ auth()->user()->email }}</strong>.
        Cek inbox (dan folder spam), terus klik link-nya.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 rounded-lg border-l-4 border-green-600 bg-green-50 p-3 text-sm text-green-800">
            Link verifikasi baru udah dikirim.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border-l-4 border-red-600 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
                class="w-full rounded-lg bg-[#4FC3F7] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            Kirim Ulang Link Verifikasi
        </button>
    </form>

    <a href="{{ route('dashboard') }}" class="mt-4 block text-center text-sm text-[#1a4d8f] hover:underline">
        Nanti aja, ke dashboard
    </a>
</div>
@endsection