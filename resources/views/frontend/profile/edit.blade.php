@extends('layouts.app')

@section('title', 'Edit Profil — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Edit Profil</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Edit Profil</a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('name')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('email')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">No HP (WA) <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('no_hp')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tanggal Lahir <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="{{ $user->tgl_lahir?->format('d/m/Y') }}" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Undangan <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="{{ $user->referrer?->referral_code ?? '-' }}" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Provinsi <span class="text-danger">*</span></label>
                <select name="provinsi" id="provinsi" required data-region="province"
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="{{ old('provinsi', $user->provinsi) }}" selected>{{ old('provinsi', $user->provinsi) ?: 'Pilih Provinsi' }}</option>
                </select>
                <input type="hidden" name="provinsi_id" id="provinsi_id" value="{{ old('provinsi_id') }}">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kota / Kabupaten <span class="text-danger">*</span></label>
                <select name="kota" id="kota" required data-region="regency" disabled
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value="{{ old('kota', $user->kota) }}" selected>{{ old('kota', $user->kota) ?: 'Pilih Provinsi dulu' }}</option>
                </select>
                <input type="hidden" name="kota_id" id="kota_id" value="{{ old('kota_id') }}">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kecamatan <span class="text-danger">*</span></label>
                <select name="kecamatan" id="kecamatan" required disabled
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value="{{ old('kecamatan', $user->kecamatan) }}" selected>{{ old('kecamatan', $user->kecamatan) ?: 'Pilih Kota dulu' }}</option>
                </select>
                <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="{{ old('kecamatan_id') }}">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Pos <span class="text-danger">*</span></label>
                <select name="kode_pos" id="kode_pos" required disabled
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value="{{ old('kode_pos', $user->kode_pos) }}" selected>{{ old('kode_pos', $user->kode_pos) ?: 'Pilih Kecamatan dulu' }}</option>
                </select>
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
                    var prev = sel.value;
                    sel.innerHTML = '<option value="">' + placeholder + '</option>';
                    (items || []).forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = item[valueKey] || item.nama || '';
                        opt.textContent = item[labelKey] || item.nama || '';
                        opt.dataset.id = item.id || '';
                        sel.appendChild(opt);
                    });
                    if (prev) sel.value = prev;
                }

                function fallbackInput(sel, name, val) {
                    sel.outerHTML = '<input type="text" name="' + name + '" id="' + name + '" value="' + (val || '') + '" required maxlength="100" class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">';
                }

                fetchJson('{{ route("api.regions.provinces") }}')
                    .then(function (data) {
                        fillSelect(provSel, data, 'Pilih Provinsi', 'nama', 'nama');
                    })
                    .catch(function () {
                        fallbackInput(provSel, 'provinsi', '{{ old("provinsi", $user->provinsi) }}');
                    });

                provSel.addEventListener('change', function () {
                    var opt = provSel.options[provSel.selectedIndex];
                    provId.value = opt ? (opt.dataset.id || '') : '';
                    kotaSel.disabled = true; kotaSel.classList.add('opacity-50');
                    kecSel.disabled = true; kecSel.classList.add('opacity-50');
                    posSel.disabled = true; posSel.classList.add('opacity-50');
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
                            kotaSel.disabled = false; kotaSel.classList.remove('opacity-50');
                        })
                        .catch(function () {
                            kotaSel.disabled = false; kotaSel.classList.remove('opacity-50');
                            fallbackInput(kotaSel, 'kota', '{{ old("kota", $user->kota) }}');
                        });
                });

                kotaSel.addEventListener('change', function () {
                    var opt = kotaSel.options[kotaSel.selectedIndex];
                    kotaId.value = opt ? (opt.dataset.id || '') : '';
                    kecSel.disabled = true; kecSel.classList.add('opacity-50');
                    posSel.disabled = true; posSel.classList.add('opacity-50');
                    kecSel.innerHTML = '<option value="">Memuat...</option>';
                    posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                    if (!kotaSel.value) {
                        kecSel.innerHTML = '<option value="">Pilih Kota dulu</option>';
                        return;
                    }
                    fetchJson('{{ route("api.regions.districts", ["regency" => "__ID__"]) }}'.replace('__ID__', kotaId.value))
                        .then(function (data) {
                            fillSelect(kecSel, data, 'Pilih Kecamatan', 'nama', 'nama');
                            kecSel.disabled = false; kecSel.classList.remove('opacity-50');
                        })
                        .catch(function () {
                            kecSel.disabled = false; kecSel.classList.remove('opacity-50');
                            fallbackInput(kecSel, 'kecamatan', '{{ old("kecamatan", $user->kecamatan) }}');
                        });
                });

                kecSel.addEventListener('change', function () {
                    var opt = kecSel.options[kecSel.selectedIndex];
                    kecId.value = opt ? (opt.dataset.id || '') : '';
                    posSel.disabled = true; posSel.classList.add('opacity-50');
                    posSel.innerHTML = '<option value="">Memuat...</option>';
                    if (!kecSel.value) {
                        posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                        return;
                    }
                    fetchJson('{{ route("api.regions.postalCodes", ["district" => "__ID__"]) }}'.replace('__ID__', kecId.value))
                        .then(function (data) {
                            fillSelect(posSel, data, 'Pilih Kode Pos', 'kode', 'kode');
                            posSel.disabled = false; posSel.classList.remove('opacity-50');
                        })
                        .catch(function () {
                            posSel.disabled = false; posSel.classList.remove('opacity-50');
                            fallbackInput(posSel, 'kode_pos', '{{ old("kode_pos", $user->kode_pos) }}');
                        });
                });
            })();
        </script>

        <div>
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" rows="2" required
                      class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN PROFIL</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ganti Kata Sandi</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Lama <span class="text-danger">*</span></label>
                <input type="password" name="current_password" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('current_password')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('password')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
        </div>

        <button type="submit" class="rounded-lg border border-rule px-6 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">GANTI KATA SANDI</button>
    </form>
@endsection
