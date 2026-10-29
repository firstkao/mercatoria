@extends('layouts.guest')

@section('title', 'Daftar â€” Mercatoria')

@section('width', 'w-full max-w-2xl')

@section('content')
    <div class="rounded-xl border border-rule bg-white px-6 py-8 sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pendaftaran</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">
            Daftar Akun Baru
        </h1>

        @if (session('buyer_protection'))
            <div class="mt-5 rounded-xl border border-rule bg-paper px-4 py-4 sm:px-5">
                <p class="flex items-start gap-2.5 font-semibold text-ink">
                    <x-icons.shield-check class="mt-0.5 text-link" width="18" height="18" />
                    <span>Email atau Nomor WhatsApp ini sudah terdaftar!</span>
                </p>
                <p class="mt-2 pl-[26px] text-sm text-ink/70">
                    Demi keamanan riwayat pesanan &amp; koin kamu, pendaftaran ulang tidak diizinkan.
                    Silakan masuk menggunakan akun lama kamu.
                </p>
                <div class="mt-3 flex flex-wrap gap-3 pl-[26px]">
                    <a href="{{ route('login') }}" class="rounded-lg bg-black px-4 py-2 text-xs font-semibold tracking-wide text-white transition-colors hover:bg-ink">Masuk ke Akun Lama</a>
                    <a href="{{ route('password.request') }}" class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">Lupa Password? Reset Disini</a>
                </div>
            </div>
        @endif

        <div class="mt-5 rounded-xl border border-rule bg-paper px-4 py-4 sm:px-5">
            <p class="flex items-start gap-2.5 text-sm text-ink/80">
                <x-icons.exclamation-triangle-fill class="mt-0.5 text-ink/55" width="18" height="18" />
                <span>Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran.</span>
            </p>
        </div>

        <div class="mt-6 h-px bg-rule"></div>

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
                    <label for="name" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Nama Lengkap
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required minlength="2" maxlength="100"
                           placeholder="Min 2 karakter"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('name')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="tgl_lahir" class="flex items-center gap-1.5 font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Tanggal Lahir
                        <x-icons.lock-fill class="text-ink/45" width="11" height="11" />
                        <span class="text-danger">*</span>
                        <span class="font-sans text-[11px] font-normal tracking-normal normal-case text-ink/45">(Tidak bisa diubah)</span>
                    </label>
                    <div class="relative mt-2">
                        <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                               class="w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    </div>
                    @error('tgl_lahir')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_hp" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        No HP (WhatsApp)
                        <span class="text-danger">*</span>
                    </label>
                    <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" required
                           placeholder="08xxx / 628xxx"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('no_hp')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Email
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="email@domain.com"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('email')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="provinsi" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Provinsi
                        <span class="text-danger">*</span>
                    </label>
                    <select name="provinsi" id="provinsi" required
                            class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <option value="">Pilih Provinsi</option>
                        @foreach (['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'] as $provinsi)
                            <option value="{{ $provinsi }}" @selected(old('provinsi') === $provinsi)>{{ $provinsi }}</option>
                        @endforeach
                    </select>
                    @error('provinsi')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kota" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kota / Kabupaten
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="kota" id="kota" value="{{ old('kota') }}" required maxlength="100"
                           placeholder="Ketik Kota/Kab..."
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('kota')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kecamatan" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kecamatan
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan') }}" required maxlength="100"
                           placeholder="Ketik Kecamatan..."
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('kecamatan')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kode_pos" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kode Pos
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="kode_pos" id="kode_pos" value="{{ old('kode_pos') }}" required maxlength="10"
                           placeholder="11xxx"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @error('kode_pos')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="alamat" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    Alamat Lengkap
                    <span class="text-danger">*</span>
                </label>
                <textarea name="alamat" id="alamat" rows="3" required maxlength="500"
                          placeholder="Jalan, No Rumah, RT/RW, Patokan..."
                          class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">{{ old('alamat') }}</textarea>
                @error('alamat')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kata Sandi
                        <span class="text-danger">*</span>
                        <span class="font-sans text-[11px] font-normal tracking-normal normal-case text-ink/45">(Min 8 karakter)</span>
                    </label>
                    <div class="relative mt-2">
                        <input type="password" name="password" id="password" required minlength="8"
                               class="w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 pr-11 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <button type="button" onclick="const p=document.getElementById('password');const b=this;const s=p.type==='password';p.type=s?'text':'password';b.setAttribute('aria-label',s?'Sembunyikan kata sandi':'Tampilkan kata sandi');b.dataset.shown=s?'1':'0'"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-ink/40 transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/45"
                                aria-label="Tampilkan kata sandi">
                            <x-icons.eye-fill class="hidden [[data-shown='1']_&]:block" />
                            <x-icons.eye-slash-fill class="block [[data-shown='1']_&]:hidden" />
                        </button>
                    </div>
                    @error('password')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Ulangi Kata Sandi
                        <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                </div>
            </div>

            <div>
                <label for="referral_code" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    Kode Undangan
                    <span class="font-sans text-[11px] font-normal tracking-normal normal-case text-ink/45">(Opsional)</span>
                </label>
                <input type="text" name="referral_code" id="referral_code"
                       value="{{ old('referral_code', $referralCode) }}"
                       @if (request()->filled('ref')) readonly @endif
                       placeholder="MER-REF-8821"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 read-only:bg-paper read-only:text-ink/60">
                @error('referral_code')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-3 rounded-xl border border-rule bg-paper px-4 py-4">
                <label class="flex items-start gap-2.5 text-sm text-ink/85">
                    <input type="checkbox" name="agree_tnc" value="1" @checked(old('agree_tnc')) class="mt-0.5 h-4 w-4 rounded border-rule text-black focus:ring-accent/50">
                    <span>Saya menyetujui <a href="/tnc" class="font-medium text-link underline decoration-link/35 underline-offset-4 hover:text-ink">Syarat &amp; Ketentuan</a> <span class="text-danger">*</span></span>
                </label>
                @error('agree_tnc')<p class="text-sm text-danger">{{ $message }}</p>@enderror

                <label class="flex items-start gap-2.5 text-sm text-ink/85">
                    <input type="checkbox" name="agree_privacy" value="1" @checked(old('agree_privacy')) class="mt-0.5 h-4 w-4 rounded border-rule text-black focus:ring-accent/50">
                    <span>Saya menyetujui <a href="/kebijakan-privasi" class="font-medium text-link underline decoration-link/35 underline-offset-4 hover:text-ink">Kebijakan Privasi</a> <span class="text-danger">*</span></span>
                </label>
                @error('agree_privacy')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                DAFTAR SEKARANG
            </button>

            <p class="pt-1 text-center text-sm text-ink/65">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Masuk</a>
            </p>
        </form>
    </div>
@endsection
