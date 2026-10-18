@extends('layouts.admin')

@section('title', 'Migrasi Customer Lama')

@section('content')
<div>
    <a href="{{ route('office.users.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Customer</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">Tambah User Baru</h1>
</div>

<div class="mt-4 rounded-lg border-l-4 border-info-500 bg-info-50 p-4 text-sm text-info-700">
    Form ini digunakan untuk migrasi customer lama dari sistem sebelumnya. Password dan tanggal lahir diisi sendiri oleh customer setelah aktivasi.
</div>

<form method="POST" action="{{ route('office.users.store') }}" class="mt-5 space-y-5">
    @csrf
    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Data Dasar</h2>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="mb-1 block text-sm font-medium">Nama Lengkap *</label>
                <input id="name" name="name" required maxlength="255" value="{{ old('name') }}" autocomplete="name"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('name')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email *</label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('email')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="no_hp" class="mb-1 block text-sm font-medium">No. HP (format 62xxx) *</label>
                <input id="no_hp" name="no_hp" type="tel" required maxlength="15" pattern="62[0-9]{8,13}" value="{{ old('no_hp') }}" autocomplete="tel"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                <p class="mt-1 text-xs text-text-muted">Masukkan nomor Indonesia dengan awalan 62.</p>
                @error('no_hp')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-border bg-white p-5 md:p-6">
        <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">Alamat Pengiriman</h2>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div>
                <label for="provinsi" class="mb-1 block text-sm font-medium">Provinsi *</label>
                <input id="provinsi" name="provinsi" required maxlength="255" value="{{ old('provinsi') }}" autocomplete="address-level1"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('provinsi')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kota" class="mb-1 block text-sm font-medium">Kota / Kabupaten *</label>
                <input id="kota" name="kota" required maxlength="255" value="{{ old('kota') }}" autocomplete="address-level2"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('kota')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kecamatan" class="mb-1 block text-sm font-medium">Kecamatan *</label>
                <input id="kecamatan" name="kecamatan" required maxlength="255" value="{{ old('kecamatan') }}"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('kecamatan')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kode_pos" class="mb-1 block text-sm font-medium">Kode Pos *</label>
                <input id="kode_pos" name="kode_pos" required maxlength="10" value="{{ old('kode_pos') }}" autocomplete="postal-code"
                       class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">
                @error('kode_pos')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="alamat_lengkap" class="mb-1 block text-sm font-medium">Alamat Lengkap *</label>
                <textarea id="alamat_lengkap" name="alamat_lengkap" rows="4" required maxlength="2000" autocomplete="street-address"
                          class="w-full rounded-lg border-border focus:border-primary-400 focus:ring-primary-400">{{ old('alamat_lengkap') }}</textarea>
                @error('alamat_lengkap')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="rounded-lg border-l-4 border-warning-500 bg-warning-50 p-4 text-sm text-warning-700">
        <h2 class="font-semibold">⚠️ Setelah simpan</h2>
        <p class="mt-1">Password dan tanggal lahir tidak diisi di sini. Kamu akan diarahkan untuk memasukkan order lama. Beri tahu customer agar login melalui <strong>Lupa Password</strong>.</p>
    </div>
    <div class="flex justify-end gap-3">
        <x-button variant="ghost" href="{{ route('office.users.index') }}">Batal</x-button>
        <x-button type="submit">Simpan &amp; Lanjut Order</x-button>
    </div>
</form>
@endsection
