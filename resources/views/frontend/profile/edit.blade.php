@extends('layouts.app')

@section('title', 'Edit Profil — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Edit Profil</h1>

    @if (session('status'))
        <div class="mt-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">No HP (WA) <span class="text-red-500">*</span></label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('no_hp')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Tanggal Lahir <span class="text-xs text-gray-400">(Terkunci)</span></label>
                <input type="text" value="{{ $user->tgl_lahir?->format('d/m/Y') }}" disabled
                       class="mt-1 w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium">Provinsi <span class="text-red-500">*</span></label>
                <input type="text" name="provinsi" value="{{ old('provinsi', $user->provinsi) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Kota / Kabupaten <span class="text-red-500">*</span></label>
                <input type="text" name="kota" value="{{ old('kota', $user->kota) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Kecamatan <span class="text-red-500">*</span></label>
                <input type="text" name="kecamatan" value="{{ old('kecamatan', $user->kecamatan) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Pos <span class="text-red-500">*</span></label>
                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->kode_pos) }}" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Alamat Lengkap <span class="text-red-500">*</span></label>
            <textarea name="alamat" rows="2" required
                      class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">SIMPAN PROFIL</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf
        <h2 class="text-sm font-semibold">Ganti Kata Sandi</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium">Kata Sandi Lama <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('current_password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Kata Sandi Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Ulangi Kata Sandi Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
        </div>

        <button type="submit" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold hover:border-black">GANTI KATA SANDI</button>
    </form>
@endsection
