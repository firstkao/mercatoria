@extends('layouts.guest')

@section('title', 'Daftar Akun Baru — Mercatoria')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-2xl font-semibold">Daftar Akun Baru</h1>

        @if (session('buyer_protection'))
            <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="flex items-start gap-2 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="mt-0.5 shrink-0">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    Email atau Nomor WhatsApp ini sudah terdaftar sebagai Pelanggan!
                </p>
                <p class="mt-2">
                    Demi keamanan riwayat pesanan &amp; koin kamu, pendaftaran ulang tidak diizinkan.
                    Silakan masuk menggunakan akun lama kamu.
                </p>
                <div class="mt-3 flex gap-3">
                    <a href="{{ route('login') }}" class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white">Masuk ke Akun Lama</a>
                    <a href="{{ route('password.request') }}" class="rounded-lg border border-amber-400 px-4 py-2 text-xs font-semibold text-amber-900">Lupa Password? Reset Disini</a>
                </div>
            </div>
        @endif

        <div class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="mt-0.5 shrink-0">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran. Email dan nomor WhatsApp yang sama maksimal 3 kali mendaftar.
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf

            {{-- Honeypot (PRD 8.2) --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                <label for="url">URL</label>
                <input type="text" name="url" id="url" tabindex="-1" autocomplete="off">
                <label for="phone2">Phone</label>
                <input type="text" name="phone2" id="phone2" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required minlength="2" maxlength="100"
                           placeholder="Min 2 karakter"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="tgl_lahir" class="block text-sm font-medium">Tanggal Lahir <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-400">(Terkunci 1x seumur hidup)</span>
                    </label>
                    <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('tgl_lahir')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_hp" class="block text-sm font-medium">No HP (WhatsApp) <span class="text-red-500">*</span></label>
                    <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" required
                           placeholder="08xxx / 628xxx"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('no_hp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="email@domain.com"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="provinsi" class="block text-sm font-medium">Provinsi <span class="text-red-500">*</span></label>
                    <select name="provinsi" id="provinsi" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="">Pilih Provinsi</option>
                        @foreach (['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'] as $provinsi)
                            <option value="{{ $provinsi }}" @selected(old('provinsi') === $provinsi)>{{ $provinsi }}</option>
                        @endforeach
                    </select>
                    @error('provinsi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kota" class="block text-sm font-medium">Kota / Kabupaten <span class="text-red-500">*</span></label>
                    <input type="text" name="kota" id="kota" value="{{ old('kota') }}" required maxlength="100"
                           placeholder="Ketik Kota/Kab..."
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('kota')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kecamatan" class="block text-sm font-medium">Kecamatan <span class="text-red-500">*</span></label>
                    <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan') }}" required maxlength="100"
                           placeholder="Ketik Kecamatan..."
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('kecamatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kode_pos" class="block text-sm font-medium">Kode Pos <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_pos" id="kode_pos" value="{{ old('kode_pos') }}" required maxlength="10"
                           placeholder="11xxx"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('kode_pos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="alamat" class="block text-sm font-medium">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea name="alamat" id="alamat" rows="3" required maxlength="500"
                          placeholder="Jalan, No Rumah, RT/RW, Patokan..."
                          class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">{{ old('alamat') }}</textarea>
                @error('alamat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-medium">Kata Sandi <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-400">(Min 8 karakter)</span>
                    </label>
                    <input type="password" name="password" id="password" required minlength="8"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium">Ulangi Kata Sandi <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                </div>
            </div>

            <div>
                <label for="referral_code" class="block text-sm font-medium">Kode Undangan
                    <span class="text-xs text-gray-400">(Opsional — Auto-lock dari ?ref=)</span>
                </label>
                <input type="text" name="referral_code" id="referral_code"
                       value="{{ old('referral_code', $referralCode) }}"
                       @if (request()->filled('ref')) readonly @endif
                       placeholder="MER-REF-8821"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('referral_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-3">
                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" name="agree_tnc" value="1" @checked(old('agree_tnc')) class="mt-1 rounded border-gray-300">
                    <span>Saya menyetujui <a href="/tnc" class="underline">Syarat &amp; Ketentuan</a> <span class="text-red-500">*</span></span>
                </label>
                @error('agree_tnc')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" name="agree_privacy" value="1" @checked(old('agree_privacy')) class="mt-1 rounded border-gray-300">
                    <span>Saya menyetujui <a href="/kebijakan-privasi" class="underline">Kebijakan Privasi</a> <span class="text-red-500">*</span></span>
                </label>
                @error('agree_privacy')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3 text-sm font-semibold text-white hover:bg-gray-800">
                DAFTAR SEKARANG
            </button>

            <p class="text-center text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-black underline">Masuk</a>
            </p>
        </form>
    </div>
@endsection
