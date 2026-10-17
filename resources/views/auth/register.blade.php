@extends('layouts.auth')

@section('title', 'Daftar')
@section('subtitle', 'Buat akun baru untuk mulai belanja')

@section('content')

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    {{-- Honeypot (tersembunyi) --}}
    <div class="hidden" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
        <input type="text" name="url" tabindex="-1" autocomplete="off">
        <input type="text" name="phone2" tabindex="-1" autocomplete="off">
    </div>

    {{-- Referral code (auto dari URL ?ref=XXX) --}}
    <input type="hidden" name="referral_code" value="{{ request('ref') }}">

    {{-- Nama --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Lengkap <span class="text-red-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name') }}"
            required minlength="2" maxlength="100"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror">
        @error('name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-red-500">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
            required maxlength="150"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror">
        @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- No HP --}}
    <div>
        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">
            No HP (WhatsApp) <span class="text-red-500">*</span>
        </label>
        <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
            required placeholder="08xxxxxxxxxx"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('no_hp') border-red-500 @enderror">
        <p class="text-xs text-gray-500 mt-1">Format: 08xxx atau 628xxx</p>
        @error('no_hp')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tgl Lahir --}}
    <div>
        <label for="tgl_lahir" class="block text-sm font-medium text-gray-700 mb-1">
            Tanggal Lahir <span class="text-red-500">*</span>
        </label>
        <input type="date" id="tgl_lahir" name="tgl_lahir" value="{{ old('tgl_lahir') }}"
            required max="{{ date('Y-m-d', strtotime('-1 day')) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('tgl_lahir') border-red-500 @enderror">
        <p class="text-xs text-gray-500 mt-1">⚠️ Tanggal lahir tidak dapat diubah setelah didaftarkan.</p>
        @error('tgl_lahir')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Divider --}}
    <div class="border-t border-gray-200 pt-4 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Alamat Pengiriman</h3>
    </div>

    {{-- Provinsi --}}
    <div>
        <label for="provinsi" class="block text-sm font-medium text-gray-700 mb-1">
            Provinsi <span class="text-red-500">*</span>
        </label>
        <select id="provinsi" name="provinsi" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('provinsi') border-red-500 @enderror">
            <option value="">-- Pilih Provinsi --</option>
            @php
                $provinces = ['Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Bengkulu', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Lampung', 'Banten', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'];
            @endphp
            @foreach ($provinces as $prov)
                <option value="{{ $prov }}" {{ old('provinsi') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
            @endforeach
        </select>
        @error('provinsi')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kota --}}
    <div>
        <label for="kota" class="block text-sm font-medium text-gray-700 mb-1">
            Kota / Kabupaten <span class="text-red-500">*</span>
        </label>
        <input type="text" id="kota" name="kota" value="{{ old('kota') }}"
            required maxlength="100"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('kota') border-red-500 @enderror">
        @error('kota')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kecamatan --}}
    <div>
        <label for="kecamatan" class="block text-sm font-medium text-gray-700 mb-1">
            Kecamatan <span class="text-red-500">*</span>
        </label>
        <input type="text" id="kecamatan" name="kecamatan" value="{{ old('kecamatan') }}"
            required maxlength="100"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('kecamatan') border-red-500 @enderror">
        @error('kecamatan')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Alamat Lengkap --}}
    <div>
        <label for="alamat_lengkap" class="block text-sm font-medium text-gray-700 mb-1">
            Alamat Lengkap <span class="text-red-500">*</span>
        </label>
        <textarea id="alamat_lengkap" name="alamat_lengkap" required rows="3"
            minlength="10" maxlength="500"
            placeholder="Nama jalan, nomor rumah, RT/RW, patokan..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('alamat_lengkap') border-red-500 @enderror">{{ old('alamat_lengkap') }}</textarea>
        @error('alamat_lengkap')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kode Pos --}}
    <div>
        <label for="kode_pos" class="block text-sm font-medium text-gray-700 mb-1">
            Kode Pos <span class="text-red-500">*</span>
        </label>
        <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos') }}"
            required maxlength="10" inputmode="numeric" pattern="[0-9]{4,10}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('kode_pos') border-red-500 @enderror">
        @error('kode_pos')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Divider --}}
    <div class="border-t border-gray-200 pt-4 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Keamanan Akun</h3>
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password <span class="text-red-500">*</span>
        </label>
        <input type="password" id="password" name="password" required minlength="8"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror">
        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p>
        @error('password')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Konfirmasi Password --}}
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
            Ulangi Password <span class="text-red-500">*</span>
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    {{-- TnC --}}
    <div class="flex items-start gap-2 mt-4">
        <input type="checkbox" id="tnc" name="tnc" value="1" required
            class="mt-1 w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 @error('tnc') border-red-500 @enderror">
        <label for="tnc" class="text-sm text-gray-600">
            Saya menyetujui <a href="/tnc" target="_blank" class="text-primary-500 hover:underline">Syarat &amp; Ketentuan</a> Mercatoria.
        </label>
    </div>
    @error('tnc')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

    {{-- Privacy --}}
    <div class="flex items-start gap-2">
        <input type="checkbox" id="privacy" name="privacy" value="1" required
            class="mt-1 w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 @error('privacy') border-red-500 @enderror">
        <label for="privacy" class="text-sm text-gray-600">
            Saya menyetujui <a href="/kebijakan-privasi" target="_blank" class="text-primary-500 hover:underline">Kebijakan Privasi</a>.
        </label>
    </div>
    @error('privacy')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

    {{-- Submit --}}
    <button type="submit"
        class="w-full bg-black text-white py-2.5 rounded-lg font-medium hover:bg-gray-800 transition mt-6">
        Daftar Sekarang
    </button>

    {{-- Link Login --}}
    <p class="text-center text-sm text-gray-600 mt-4">
        Udah punya akun?
        <a href="{{ route('login') }}" class="text-primary-500 font-medium hover:underline">Masuk di sini</a>
    </p>

</form>

@endsection