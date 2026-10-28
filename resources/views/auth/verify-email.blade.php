@extends('layouts.guest')

@section('title', 'Verifikasi Email — Mercatoria')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-2xl font-semibold">Verifikasi Email</h1>
        <p class="mt-2 text-sm text-gray-600">
            Kami sudah mengirim link verifikasi ke <strong>{{ auth()->user()->email }}</strong>.
            Link hanya berlaku selama 24 jam sejak pendaftaran.
        </p>

        <form method="POST" action="{{ route('verification.resend') }}" class="mt-6">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white hover:bg-gray-800">
                KIRIM ULANG LINK VERIFIKASI
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg border border-gray-300 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                KELUAR
            </button>
        </form>
    </div>
@endsection
