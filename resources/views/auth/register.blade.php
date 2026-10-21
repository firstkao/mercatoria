@extends('layouts.auth')

@section('title', 'Daftar')
@section('heading', 'Daftar Akun Baru')
@section('width', 'max-w-3xl')

@php
    $input = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
    $label = 'block text-sm font-medium text-gray-700 mb-1';
    $refCode = old('referral_code', request('ref'));
    $provinces = ['Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Bengkulu', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Lampung', 'Banten', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'];
@endphp

@section('content')

@if (session('buyer_protection'))
    <div id="buyer-protection-alert" role="alert" class="mb-4 rounded-lg border-l-4 border-danger-500 bg-danger-50 p-4 text-sm text-text">
        <p class="font-semibold">🛡️ Email atau Nomor WhatsApp ini sudah terdaftar sebagai Pelanggan!</p>
        <p class="mt-1">Demi keamanan riwayat pesanan &amp; koin kamu, pendaftaran ulang tidak diizinkan. Silakan masuk menggunakan akun lama kamu.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <x-button variant="auth" href="{{ route('login') }}">Masuk ke Akun Lama</x-button>
            <x-button variant="secondary" href="{{ route('password.request') }}">Lupa Password? Reset Disini</x-button>
        </div>
    </div>
@endif

<div class="mb-4 rounded-lg border-l-4 border-warning-500 bg-warning-50 p-3 text-sm text-text">
    ⚠️ Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran. Email &amp; nomor WA yang sama maksimal 3 kali mendaftar.
</div>

<form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @csrf

    <x-honeypot />

    <div>
        <label for="name" class="{{ $label }}">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required minlength="2" maxlength="100" placeholder="Min 2 karakter"
            class="{{ $input }} @error('name') border-red-500 @enderror">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tgl_lahir" class="{{ $label }}">Tanggal Lahir <span class="text-red-500">*</span> 🔒</label>
        <input type="date" id="tgl_lahir" name="tgl_lahir" value="{{ old('tgl_lahir') }}"
            required max="{{ date('Y-m-d', strtotime('-1 day')) }}"
            class="{{ $input }} @error('tgl_lahir') border-red-500 @enderror">
        <p class="text-xs text-gray-500 mt-1">Locked 1x seumur hidup — tidak dapat diubah setelah didaftarkan.</p>
        @error('tgl_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="no_hp" class="{{ $label }}">No HP (WhatsApp) <span class="text-red-500">*</span></label>
        <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08xxx / 628xxx"
            class="{{ $input }} @error('no_hp') border-red-500 @enderror">
        @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $label }}">Email <span class="text-red-500">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="150" placeholder="email@domain.com"
            class="{{ $input }} @error('email') border-red-500 @enderror">
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="provinsi" class="{{ $label }}">Provinsi <span class="text-red-500">*</span></label>
        <select id="provinsi" name="provinsi" required class="{{ $input }} @error('provinsi') border-red-500 @enderror">
            <option value="">Pilih Provinsi</option>
            @foreach ($provinces as $prov)
                <option value="{{ $prov }}" @selected(old('provinsi') === $prov)>{{ $prov }}</option>
            @endforeach
        </select>
        @error('provinsi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="kota" class="{{ $label }}">Kota / Kabupaten <span class="text-red-500">*</span></label>
        <input type="text" id="kota" name="kota" value="{{ old('kota') }}" required maxlength="100" placeholder="Ketik Kota/Kab..."
            class="{{ $input }} @error('kota') border-red-500 @enderror">
        @error('kota') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="kecamatan" class="{{ $label }}">Kecamatan <span class="text-red-500">*</span></label>
        <input type="text" id="kecamatan" name="kecamatan" value="{{ old('kecamatan') }}" required maxlength="100" placeholder="Ketik Kecamatan..."
            class="{{ $input }} @error('kecamatan') border-red-500 @enderror">
        @error('kecamatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="kode_pos" class="{{ $label }}">Kode Pos <span class="text-red-500">*</span></label>
        <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos') }}"
            required maxlength="10" inputmode="numeric" pattern="[0-9]{4,10}" placeholder="11xxx"
            class="{{ $input }} @error('kode_pos') border-red-500 @enderror">
        @error('kode_pos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="alamat_lengkap" class="{{ $label }}">Alamat Lengkap <span class="text-red-500">*</span></label>
        <textarea id="alamat_lengkap" name="alamat_lengkap" required rows="3" minlength="10" maxlength="500"
            placeholder="Jalan, No Rumah, RT/RW, Patokan..."
            class="{{ $input }} @error('alamat_lengkap') border-red-500 @enderror">{{ old('alamat_lengkap') }}</textarea>
        @error('alamat_lengkap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="{{ $label }}">Kata Sandi <span class="text-red-500">*</span> (Min 8 char)</label>
        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
            class="{{ $input }} @error('password') border-red-500 @enderror">
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="{{ $label }}">Ulangi Kata Sandi <span class="text-red-500">*</span></label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
            class="{{ $input }}">
    </div>

    <div class="md:col-span-2">
        <label for="referral_code" class="{{ $label }}">Kode Undangan (Opsional{{ request('ref') ? ' — Auto-lock dari link' : '' }})</label>
        <input type="text" id="referral_code" name="referral_code" value="{{ $refCode }}" maxlength="16" placeholder="MER-REF-XXXX"
            @readonly(filled(request('ref')))
            class="{{ $input }} @if (filled(request('ref'))) bg-gray-100 @endif @error('referral_code') border-red-500 @enderror">
        @error('referral_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="space-y-2 md:col-span-2">
        <div class="flex items-start gap-2">
            <input type="checkbox" id="tnc" name="tnc" value="1" required
                class="mt-1 w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 @error('tnc') border-red-500 @enderror">
            <label for="tnc" class="text-sm text-gray-600">
                Saya menyetujui <a href="/tnc" target="_blank" class="text-primary-500 hover:underline">Syarat &amp; Ketentuan</a>
            </label>
        </div>
        @error('tnc') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="flex items-start gap-2">
            <input type="checkbox" id="privacy" name="privacy" value="1" required
                class="mt-1 w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 @error('privacy') border-red-500 @enderror">
            <label for="privacy" class="text-sm text-gray-600">
                Saya menyetujui <a href="/kebijakan-privasi" target="_blank" class="text-primary-500 hover:underline">Kebijakan Privasi</a>
            </label>
        </div>
        @error('privacy') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2"><x-turnstile /></div>

    <div class="md:col-span-2">
        <x-button type="submit" variant="auth" class="w-full py-2.5">Daftar Sekarang</x-button>
        <p class="mt-4 text-center text-sm text-gray-600">
            Udah punya akun?
            <a href="{{ route('login') }}" class="text-primary-500 font-medium hover:underline">Masuk di sini</a>
        </p>
    </div>
</form>

@endsection
