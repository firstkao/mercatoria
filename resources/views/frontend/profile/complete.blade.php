@extends('layouts.app')

@section('title', 'Lengkapi Profil — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Lengkapi Profil Kamu</h1>
    <p class="mt-1 text-sm text-gray-500">Tanggal lahir wajib diisi sebelum membuat pesanan. Terkunci permanen setelah disimpan.</p>

    <form method="POST" action="{{ route('profile.complete.store') }}" class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf

        <div>
            <label class="block text-sm font-medium">Tanggal Lahir <span class="text-red-500">*</span></label>
            <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            @error('tgl_lahir')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">SIMPAN</button>
    </form>
@endsection
