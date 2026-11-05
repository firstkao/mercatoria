@extends('layouts.guest')

@section('title', 'Daftar — MERCATORIA')

@section('width', 'w-full max-w-2xl')

@section('content')
    @include('frontend.partials._auth-style')

    <div class="au">
        <div class="au-card">
            <p class="au-eyebrow">Pendaftaran</p>
            <h1 class="au-title">Daftar Akun Baru</h1>

            @if (session('buyer_protection'))
                <div class="au-note" style="flex-direction:column;gap:0">
                    <p style="display:flex;align-items:flex-start;gap:10px;margin:0;font-weight:700;color:#000">
                        <x-icons.shield-check class="au-ico" style="color:#02a1ea" width="18" height="18" />
                        <span>Email atau Nomor WhatsApp ini sudah terdaftar!</span>
                    </p>
                    <p style="margin:8px 0 0 28px">
                        Demi keamanan riwayat pesanan &amp; koin kamu, pendaftaran ulang tidak diizinkan.
                        Silakan masuk menggunakan akun lama kamu.
                    </p>
                    <div class="au-note-actions" style="margin-left:28px">
                        <a href="{{ route('login') }}" class="au-btn is-sm">Masuk ke Akun Lama</a>
                        <a href="{{ route('password.request') }}" class="au-btn is-sm is-ghost">Lupa Password? Reset Disini</a>
                    </div>
                </div>
            @endif

            <div class="au-note">
                <x-icons.exclamation-triangle-fill class="au-ico" width="18" height="18" />
                <span>Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran.</span>
            </div>

            <div class="au-hr"></div>

            <form method="POST" action="{{ route('register.store') }}" class="au-form">
                @csrf

                {{-- Honeypot (PRD 8.2) --}}
                <div class="hidden" style="display:none" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    <label for="url">URL</label>
                    <input type="text" name="url" id="url" tabindex="-1" autocomplete="off">
                    <label for="phone2">Phone</label>
                    <input type="text" name="phone2" id="phone2" tabindex="-1" autocomplete="off">
                </div>

                <div class="au-grid">
                    <div>
                        <label for="name" class="au-label">Nama Lengkap <i>*</i></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required minlength="2" maxlength="100"
                               placeholder="Min 2 karakter" class="au-input">
                        @error('name')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="tgl_lahir" class="au-label">Tanggal Lahir <i>*</i> <small>(Tidak bisa diubah)</small></label>
                        <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" required class="au-input">
                        @error('tgl_lahir')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="no_hp" class="au-label">No HP (WhatsApp) <i>*</i></label>
                        <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" required
                               placeholder="08xxx / 628xxx" class="au-input">
                        @error('no_hp')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="au-label">Email <i>*</i></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               placeholder="email@domain.com" class="au-input">
                        @error('email')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="provinsi" class="au-label">Provinsi <i>*</i></label>
                        <select name="provinsi" id="provinsi" required data-region="province" class="au-input">
                            <option value="">Pilih Provinsi</option>
                        </select>
                        <input type="hidden" name="provinsi_id" id="provinsi_id" value="{{ old('provinsi_id') }}">
                        @error('provinsi')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="kota" class="au-label">Kota / Kabupaten <i>*</i></label>
                        <select name="kota" id="kota" required data-region="regency" disabled class="au-input">
                            <option value="">Pilih Provinsi dulu</option>
                        </select>
                        <input type="hidden" name="kota_id" id="kota_id" value="{{ old('kota_id') }}">
                        @error('kota')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="kecamatan" class="au-label">Kecamatan <i>*</i></label>
                        <select name="kecamatan" id="kecamatan" required data-region="district" disabled class="au-input">
                            <option value="">Pilih Kota dulu</option>
                        </select>
                        <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="{{ old('kecamatan_id') }}">
                        @error('kecamatan')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="kode_pos" class="au-label">Kode Pos <i>*</i></label>
                        <select name="kode_pos" id="kode_pos" required disabled class="au-input">
                            <option value="">Pilih Kecamatan dulu</option>
                        </select>
                        @error('kode_pos')<p class="au-err">{{ $message }}</p>@enderror
                    </div>
                </div>

            <script>
                (function () {
                    var provSel = document.getElementById('provinsi');
                    var kotaSel = document.getElementById('kota');
                    var kecSel = document.getElementById('kecamatan');
                    var posSel = document.getElementById('kode_pos');
                    var provId = document.getElementById('provinsi_id');
                    var kotaId = document.getElementById('kota_id');
                    var kecId = document.getElementById('kecamatan_id');
                    if (!provSel) return;

                    function fetchJson(url) {
                        return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) {
                            if (!r.ok) throw new Error('fetch');
                            return r.json();
                        });
                    }

                    function fillSelect(sel, items, placeholder, valueKey, labelKey) {
                        sel.innerHTML = '<option value="">' + placeholder + '</option>';
                        (items || []).forEach(function (item) {
                            var opt = document.createElement('option');
                            opt.value = item[valueKey] || item.nama || '';
                            opt.textContent = item[labelKey] || item.nama || '';
                            opt.dataset.id = item.id || '';
                            sel.appendChild(opt);
                        });
                    }

                    function fallbackInput(sel, name, val) {
                        sel.outerHTML = '<input type="text" name="' + name + '" id="' + name + '" value="' + (val || '') + '" required maxlength="100" class="au-input">';
                    }

                    // Load provinces
                    fetchJson('{{ route("api.regions.provinces") }}')
                        .then(function (data) {
                            fillSelect(provSel, data, 'Pilih Provinsi', 'nama', 'nama');
                        })
                        .catch(function () {
                            fallbackInput(provSel, 'provinsi', '{{ old("provinsi") }}');
                        });

                    provSel.addEventListener('change', function () {
                        var opt = provSel.options[provSel.selectedIndex];
                        provId.value = opt ? (opt.dataset.id || '') : '';
                        kotaSel.disabled = true;
                        kecSel.disabled = true;
                        posSel.disabled = true;
                        kotaSel.innerHTML = '<option value="">Memuat...</option>';
                        kecSel.innerHTML = '<option value="">Pilih Kota dulu</option>';
                        posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                        if (!provSel.value) {
                            kotaSel.innerHTML = '<option value="">Pilih Provinsi dulu</option>';
                            return;
                        }
                        fetchJson('{{ route("api.regions.regencies", ["province" => "__ID__"]) }}'.replace('__ID__', provId.value))
                            .then(function (data) {
                                fillSelect(kotaSel, data, 'Pilih Kota/Kabupaten', 'nama', 'nama');
                                kotaSel.disabled = false;
                            })
                            .catch(function () {
                                kotaSel.disabled = false;
                                fallbackInput(kotaSel, 'kota', '{{ old("kota") }}');
                            });
                    });

                    kotaSel.addEventListener('change', function () {
                        var opt = kotaSel.options[kotaSel.selectedIndex];
                        kotaId.value = opt ? (opt.dataset.id || '') : '';
                        kecSel.disabled = true;
                        posSel.disabled = true;
                        kecSel.innerHTML = '<option value="">Memuat...</option>';
                        posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                        if (!kotaSel.value) {
                            kecSel.innerHTML = '<option value="">Pilih Kota dulu</option>';
                            return;
                        }
                        fetchJson('{{ route("api.regions.districts", ["regency" => "__ID__"]) }}'.replace('__ID__', kotaId.value))
                            .then(function (data) {
                                fillSelect(kecSel, data, 'Pilih Kecamatan', 'nama', 'nama');
                                kecSel.disabled = false;
                            })
                            .catch(function () {
                                kecSel.disabled = false;
                                fallbackInput(kecSel, 'kecamatan', '{{ old("kecamatan") }}');
                            });
                    });

                    kecSel.addEventListener('change', function () {
                        var opt = kecSel.options[kecSel.selectedIndex];
                        kecId.value = opt ? (opt.dataset.id || '') : '';
                        posSel.disabled = true;
                        posSel.innerHTML = '<option value="">Memuat...</option>';
                        if (!kecSel.value) {
                            posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                            return;
                        }
                        fetchJson('{{ route("api.regions.postalCodes", ["district" => "__ID__"]) }}'.replace('__ID__', kecId.value))
                            .then(function (data) {
                                fillSelect(posSel, data, 'Pilih Kode Pos', 'kode', 'kode');
                                posSel.disabled = false;
                            })
                            .catch(function () {
                                posSel.disabled = false;
                                fallbackInput(posSel, 'kode_pos', '{{ old("kode_pos") }}');
                            });
                    });
                })();
            </script>

                <div>
                    <label for="alamat" class="au-label">Alamat Lengkap <i>*</i></label>
                    <textarea name="alamat" id="alamat" rows="3" required maxlength="500"
                              placeholder="Jalan, No Rumah, RT/RW, Patokan..." class="au-input">{{ old('alamat') }}</textarea>
                    @error('alamat')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <div class="au-grid">
                    <div>
                        <label for="password" class="au-label">Kata Sandi <i>*</i> <small>(Min 8 karakter)</small></label>
                        <div class="au-pw">
                            <input type="password" name="password" id="password" required minlength="8" class="au-input">
                            <button type="button" class="au-eye" data-pw-toggle="password" data-shown="0" aria-label="Tampilkan kata sandi">
                                <svg class="off" width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>
                                <svg class="on" width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="au-label">Ulangi Kata Sandi <i>*</i></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" class="au-input">
                    </div>
                </div>

                <div>
                    <label for="referral_code" class="au-label">Kode Undangan <small>(Opsional)</small></label>
                    <input type="text" name="referral_code" id="referral_code"
                           value="{{ old('referral_code', $referralCode) }}"
                           @if (request()->filled('ref')) readonly @endif
                           placeholder="MER-REF-8821" class="au-input">
                    @error('referral_code')<p class="au-err">{{ $message }}</p>@enderror
                </div>

                <div class="au-terms">
                    <label class="au-check">
                        <input type="checkbox" name="agree_tnc" value="1" @checked(old('agree_tnc'))>
                        <span>Saya menyetujui <a href="/tnc" class="au-link">Syarat &amp; Ketentuan</a> <i style="font-style:normal;color:#e53935">*</i></span>
                    </label>
                    @error('agree_tnc')<p class="au-err" style="margin:0">{{ $message }}</p>@enderror

                    <label class="au-check">
                        <input type="checkbox" name="agree_privacy" value="1" @checked(old('agree_privacy'))>
                        <span>Saya menyetujui <a href="/kebijakan-privasi" class="au-link">Kebijakan Privasi</a> <i style="font-style:normal;color:#e53935">*</i></span>
                    </label>
                    @error('agree_privacy')<p class="au-err" style="margin:0">{{ $message }}</p>@enderror
                </div>

                <x-turnstile />

                <button type="submit" class="au-btn">Daftar Sekarang</button>

                <p class="au-center">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="au-link">Masuk</a>
                </p>
            </form>
        </div>
    </div>
@endsection