@extends('layouts.app')

@section('title', 'Edit Profil — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">
        <div class="ac-head">
            <h1 class="ac-title">Edit Profil</h1>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'edit'])

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Data Profil</h2></div>
            <div class="ac-body">
                <form method="POST" action="{{ route('profile.update') }}" class="au-form">
                    @csrf @method('PUT')

                    <div class="au-grid">
                        <div>
                            <label class="au-label">Nama <i>*</i></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="au-input">
                            @error('name')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="au-label">Email <i>*</i></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="au-input">
                            @error('email')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="au-label">No HP (WA) <i>*</i></label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required class="au-input">
                            @error('no_hp')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="au-label">Tanggal Lahir <small>(Tidak dapat diubah)</small></label>
                            <input type="text" value="{{ $user->tgl_lahir?->format('d/m/Y') }}" disabled class="au-input">
                        </div>
                        <div>
                            <label class="au-label">Kode Undangan <small>(Tidak dapat diubah)</small></label>
                            <input type="text" value="{{ $user->referrer?->referral_code ?? '-' }}" disabled class="au-input">
                        </div>
                        <div>
                            <label class="au-label">Provinsi <i>*</i></label>
                            <select name="provinsi" id="provinsi" required data-region="province" class="au-input">
                                <option value="{{ old('provinsi', $user->provinsi) }}" selected>{{ old('provinsi', $user->provinsi) ?: 'Pilih Provinsi' }}</option>
                            </select>
                            <input type="hidden" name="provinsi_id" id="provinsi_id" value="{{ old('provinsi_id') }}">
                        </div>
                        <div>
                            <label class="au-label">Kota / Kabupaten <i>*</i></label>
                            <select name="kota" id="kota" required class="au-input is-dim">
                                <option value="">{{ old('kota', $user->kota) ?: 'Memuat...' }}</option>
                            </select>
                            <input type="hidden" name="kota_id" id="kota_id" value="{{ old('kota_id') }}">
                        </div>
                        <div>
                            <label class="au-label">Kecamatan <i>*</i></label>
                            <select name="kecamatan" id="kecamatan" required class="au-input is-dim">
                                <option value="">{{ old('kecamatan', $user->kecamatan) ?: 'Memuat...' }}</option>
                            </select>
                            <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="{{ old('kecamatan_id') }}">
                        </div>
                        <div>
                            <label class="au-label">Kode Pos <i>*</i></label>
                            <select name="kode_pos" id="kode_pos" required class="au-input is-dim">
                                <option value="">{{ old('kode_pos', $user->kode_pos) ?: 'Memuat...' }}</option>
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

                        var userProv = @json(old('provinsi', $user->provinsi));
                        var userKota = @json(old('kota', $user->kota));
                        var userKec = @json(old('kecamatan', $user->kecamatan));
                        var userPos = @json(old('kode_pos', $user->kode_pos));

                        function fetchJson(url) {
                            return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) {
                                if (!r.ok) throw new Error('fetch');
                                return r.json();
                            });
                        }

                        function fillSelect(sel, items, placeholder, valueKey, labelKey, selectVal) {
                            sel.innerHTML = '<option value="">' + placeholder + '</option>';
                            (items || []).forEach(function (item) {
                                var opt = document.createElement('option');
                                opt.value = item[valueKey] || item.nama || '';
                                opt.textContent = item[labelKey] || item.nama || '';
                                opt.dataset.id = item.id || '';
                                sel.appendChild(opt);
                            });
                            if (selectVal) {
                                sel.value = selectVal;
                                var found = sel.options[sel.selectedIndex];
                                if (found && found.dataset.id) {
                                    if (sel === provSel) provId.value = found.dataset.id;
                                    if (sel === kotaSel) kotaId.value = found.dataset.id;
                                    if (sel === kecSel) kecId.value = found.dataset.id;
                                }
                            }
                        }

                        function enable(sel) {
                            sel.disabled = false;
                            sel.classList.remove('is-dim');
                        }

                        function fallbackInput(sel, name, val) {
                            sel.outerHTML = '<input type="text" name="' + name + '" id="' + name + '" value="' + (val || '') + '" required maxlength="100" class="au-input">';
                        }

                        function initCascade() {
                            fetchJson('{{ route("api.regions.provinces") }}')
                                .then(function (provinces) {
                                    fillSelect(provSel, provinces, 'Pilih Provinsi', 'nama', 'nama', userProv);
                                    if (!userProv) { enable(kotaSel); enable(kecSel); enable(posSel); return; }
                                    return fetchJson('{{ route("api.regions.regencies", ["province" => "__ID__"]) }}'.replace('__ID__', provId.value))
                                        .then(function (regencies) {
                                            fillSelect(kotaSel, regencies, 'Pilih Kota/Kabupaten', 'nama', 'nama', userKota);
                                            enable(kotaSel);
                                            if (!userKota) { enable(kecSel); enable(posSel); return; }
                                            return fetchJson('{{ route("api.regions.districts", ["regency" => "__ID__"]) }}'.replace('__ID__', kotaId.value))
                                                .then(function (districts) {
                                                    fillSelect(kecSel, districts, 'Pilih Kecamatan', 'nama', 'nama', userKec);
                                                    enable(kecSel);
                                                    if (!userKec) { enable(posSel); return; }
                                                    return fetchJson('{{ route("api.regions.postalCodes", ["district" => "__ID__"]) }}'.replace('__ID__', kecId.value))
                                                        .then(function (codes) {
                                                            fillSelect(posSel, codes, 'Pilih Kode Pos', 'kode', 'kode', userPos);
                                                            enable(posSel);
                                                        })
                                                        .catch(function () {
                                                            enable(posSel);
                                                            fallbackInput(posSel, 'kode_pos', userPos);
                                                        });
                                                })
                                                .catch(function () {
                                                    enable(kecSel); enable(posSel);
                                                    fallbackInput(kecSel, 'kecamatan', userKec);
                                                });
                                        })
                                        .catch(function () {
                                            enable(kotaSel); enable(kecSel); enable(posSel);
                                            fallbackInput(kotaSel, 'kota', userKota);
                                        });
                                })
                                .catch(function () {
                                    fallbackInput(provSel, 'provinsi', userProv);
                                    fallbackInput(kotaSel, 'kota', userKota);
                                    fallbackInput(kecSel, 'kecamatan', userKec);
                                    fallbackInput(posSel, 'kode_pos', userPos);
                                });
                        }

                        provSel.addEventListener('change', function () {
                            var opt = provSel.options[provSel.selectedIndex];
                            provId.value = opt ? (opt.dataset.id || '') : '';
                            kotaSel.disabled = true; kotaSel.classList.add('is-dim');
                            kecSel.disabled = true; kecSel.classList.add('is-dim');
                            posSel.disabled = true; posSel.classList.add('is-dim');
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
                                    enable(kotaSel);
                                })
                                .catch(function () {
                                    enable(kotaSel);
                                    fallbackInput(kotaSel, 'kota', '');
                                });
                        });

                        kotaSel.addEventListener('change', function () {
                            var opt = kotaSel.options[kotaSel.selectedIndex];
                            kotaId.value = opt ? (opt.dataset.id || '') : '';
                            kecSel.disabled = true; kecSel.classList.add('is-dim');
                            posSel.disabled = true; posSel.classList.add('is-dim');
                            kecSel.innerHTML = '<option value="">Memuat...</option>';
                            posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                            if (!kotaSel.value) {
                                kecSel.innerHTML = '<option value="">Pilih Kota dulu</option>';
                                return;
                            }
                            fetchJson('{{ route("api.regions.districts", ["regency" => "__ID__"]) }}'.replace('__ID__', kotaId.value))
                                .then(function (data) {
                                    fillSelect(kecSel, data, 'Pilih Kecamatan', 'nama', 'nama');
                                    enable(kecSel);
                                })
                                .catch(function () {
                                    enable(kecSel);
                                    fallbackInput(kecSel, 'kecamatan', '');
                                });
                        });

                        kecSel.addEventListener('change', function () {
                            var opt = kecSel.options[kecSel.selectedIndex];
                            kecId.value = opt ? (opt.dataset.id || '') : '';
                            posSel.disabled = true; posSel.classList.add('is-dim');
                            posSel.innerHTML = '<option value="">Memuat...</option>';
                            if (!kecSel.value) {
                                posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                                return;
                            }
                            fetchJson('{{ route("api.regions.postalCodes", ["district" => "__ID__"]) }}'.replace('__ID__', kecId.value))
                                .then(function (data) {
                                    fillSelect(posSel, data, 'Pilih Kode Pos', 'kode', 'kode');
                                    enable(posSel);
                                })
                                .catch(function () {
                                    enable(posSel);
                                    fallbackInput(posSel, 'kode_pos', '');
                                });
                        });

                        initCascade();
                    })();
                </script>

                    <div>
                        <label class="au-label">Alamat Lengkap <i>*</i></label>
                        <textarea name="alamat" rows="2" required class="au-input">{{ old('alamat', $user->alamat) }}</textarea>
                        @error('alamat')<p class="au-err">{{ $message }}</p>@enderror
                    </div>

                    <div><button type="submit" class="ac-btn">Simpan Profil</button></div>
                </form>
            </div>
        </section>

        <section class="ac-sec">
            <div class="ac-sec-h"><h2>Ganti Kata Sandi</h2></div>
            <div class="ac-body">
                <form method="POST" action="{{ route('profile.password') }}" class="au-form">
                    @csrf

                    <div class="au-grid ac-grid3">
                        <div>
                            <label class="au-label">Kata Sandi Lama <i>*</i></label>
                            <input type="password" name="current_password" required class="au-input">
                            @error('current_password')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="au-label">Kata Sandi Baru <i>*</i></label>
                            <input type="password" name="password" required minlength="8" class="au-input">
                            @error('password')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="au-label">Ulangi Kata Sandi Baru <i>*</i></label>
                            <input type="password" name="password_confirmation" required minlength="8" class="au-input">
                        </div>
                    </div>

                    <div><button type="submit" class="ac-btn is-ghost">Ganti Kata Sandi</button></div>
                </form>
            </div>
        </section>
    </div>
@endsection
