@extends('layouts.app')

@section('title', 'Edit Profil &mdash; Mercatoria')

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
                <div class="relative">
                    <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan', $user->kecamatan) }}" required maxlength="100"
                           placeholder="Ketik untuk cari..." autocomplete="off"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <div id="kecamatan-suggestions" class="absolute left-0 top-full z-20 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-lg border border-rule bg-white shadow-sm"></div>
                </div>
                <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="{{ old('kecamatan_id') }}">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Pos <span class="text-danger">*</span></label>
                <input type="text" name="kode_pos" id="kode_pos" value="{{ old('kode_pos', $user->kode_pos) }}" required
                       maxlength="5" inputmode="numeric" pattern="[0-9]{5}" placeholder="11xxx"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
        </div>

        <script>
            (function () {
                var provSel = document.getElementById('provinsi');
                var kotaSel = document.getElementById('kota');
                var kecInput = document.getElementById('kecamatan');
                var kecBox = document.getElementById('kecamatan-suggestions');
                var provId = document.getElementById('provinsi_id');
                var kotaId = document.getElementById('kota_id');
                var kecId = document.getElementById('kecamatan_id');
                var kodePos = document.getElementById('kode_pos');
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

                fetchJson('{{ route("api.regions.provinces") }}')
                    .then(function (data) {
                        var current = provSel.value;
                        fillSelect(provSel, data, 'Pilih Provinsi', 'nama', 'nama');
                        if (current) provSel.value = current;
                    })
                    .catch(function () {
                        provSel.outerHTML = '<input type="text" name="provinsi" id="provinsi" value="{{ old("provinsi", $user->provinsi) }}" required maxlength="100" class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">';
                    });

                provSel.addEventListener('change', function () {
                    var opt = provSel.options[provSel.selectedIndex];
                    provId.value = opt ? (opt.dataset.id || '') : '';
                    kotaSel.disabled = true;
                    kotaSel.classList.add('opacity-50');
                    kotaSel.innerHTML = '<option value="">Memuat...</option>';
                    if (!provSel.value) {
                        kotaSel.innerHTML = '<option value="">Pilih Provinsi dulu</option>';
                        return;
                    }
                    fetchJson('{{ route("api.regions.regencies", ["province" => "__ID__"]) }}'.replace('__ID__', provId.value))
                        .then(function (data) {
                            var current = kotaSel.value;
                            fillSelect(kotaSel, data, 'Pilih Kota/Kabupaten', 'nama', 'nama');
                            if (current) kotaSel.value = current;
                            kotaSel.disabled = false;
                            kotaSel.classList.remove('opacity-50');
                        })
                        .catch(function () {
                            kotaSel.disabled = false;
                            kotaSel.classList.remove('opacity-50');
                            kotaSel.outerHTML = '<input type="text" name="kota" id="kota" value="{{ old("kota", $user->kota) }}" required maxlength="100" class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">';
                        });
                });

                kotaSel.addEventListener('change', function () {
                    var opt = kotaSel.options[kotaSel.selectedIndex];
                    kotaId.value = opt ? (opt.dataset.id || '') : '';
                });

                var kecTimer;
                kecInput.addEventListener('input', function () {
                    clearTimeout(kecTimer);
                    var q = kecInput.value.trim();
                    if (q.length < 2 || !kotaId.value) { kecBox.classList.add('hidden'); return; }
                    kecTimer = setTimeout(function () {
                        fetchJson('{{ route("api.regions.districts", ["regency" => "__ID__"]) }}'.replace('__ID__', kotaId.value))
                            .then(function (data) {
                                var items = (data || []).filter(function (d) {
                                    return (d.nama || '').toLowerCase().indexOf(q.toLowerCase()) > -1;
                                }).slice(0, 8);
                                kecBox.innerHTML = items.map(function (d) {
                                    return '<button type="button" class="block w-full px-3.5 py-2 text-left text-sm text-ink hover:bg-paper" data-id="' + (d.id || '') + '" data-nama="' + (d.nama || '') + '">' + (d.nama || '') + '</button>';
                                }).join('') || '<p class="px-3.5 py-2 text-sm text-ink/45">Tidak ditemukan</p>';
                                kecBox.classList.remove('hidden');
                            })
                            .catch(function () { kecBox.classList.add('hidden'); });
                    }, 250);
                });

                kecBox.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-nama]');
                    if (!btn) return;
                    kecInput.value = btn.dataset.nama || '';
                    kecId.value = btn.dataset.id || '';
                    kecBox.classList.add('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#kecamatan') && !e.target.closest('#kecamatan-suggestions')) {
                        kecBox.classList.add('hidden');
                    }
                });

                if (kodePos) {
                    kodePos.addEventListener('input', function () {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5);
                    });
                }
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
