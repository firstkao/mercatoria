<?php $__env->startSection('title', 'Edit Profil — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Edit Profil</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="<?php echo e(route('dashboard')); ?>" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Pesanan Saya</a>
        <a href="<?php echo e(route('profile.notifications')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="<?php echo e(route('profile.coins')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="<?php echo e(route('profile.wallet')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="<?php echo e(route('profile.referral')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="<?php echo e(route('profile.edit')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
        <div class="mt-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink"><?php echo e(session('status')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="mt-5 space-y-4 rounded-xl border border-rule bg-white p-6">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">No HP (WA) <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" value="<?php echo e(old('no_hp', $user->no_hp)); ?>" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['no_hp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tanggal Lahir <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="<?php echo e($user->tgl_lahir?->format('d/m/Y')); ?>" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Undangan <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="<?php echo e($user->referrer?->referral_code ?? '-'); ?>" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Provinsi <span class="text-danger">*</span></label>
                <select name="provinsi" id="provinsi" required data-region="province"
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="<?php echo e(old('provinsi', $user->provinsi)); ?>" selected><?php echo e(old('provinsi', $user->provinsi) ?: 'Pilih Provinsi'); ?></option>
                </select>
                <input type="hidden" name="provinsi_id" id="provinsi_id" value="<?php echo e(old('provinsi_id')); ?>">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kota / Kabupaten <span class="text-danger">*</span></label>
                <select name="kota" id="kota" required
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value=""><?php echo e(old('kota', $user->kota) ?: 'Memuat...'); ?></option>
                </select>
                <input type="hidden" name="kota_id" id="kota_id" value="<?php echo e(old('kota_id')); ?>">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kecamatan <span class="text-danger">*</span></label>
                <select name="kecamatan" id="kecamatan" required
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value=""><?php echo e(old('kecamatan', $user->kecamatan) ?: 'Memuat...'); ?></option>
                </select>
                <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="<?php echo e(old('kecamatan_id')); ?>">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Pos <span class="text-danger">*</span></label>
                <select name="kode_pos" id="kode_pos" required
                        class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                    <option value=""><?php echo e(old('kode_pos', $user->kode_pos) ?: 'Memuat...'); ?></option>
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

                var userProv = <?php echo json_encode(old('provinsi', $user->provinsi), 512) ?>;
                var userKota = <?php echo json_encode(old('kota', $user->kota), 512) ?>;
                var userKec = <?php echo json_encode(old('kecamatan', $user->kecamatan), 512) ?>;
                var userPos = <?php echo json_encode(old('kode_pos', $user->kode_pos), 512) ?>;

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
                        // Set hidden id from selected option
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
                    sel.classList.remove('opacity-50');
                }

                function fallbackInput(sel, name, val) {
                    sel.outerHTML = '<input type="text" name="' + name + '" id="' + name + '" value="' + (val || '') + '" required maxlength="100" class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">';
                }

                // INIT: load all levels based on existing user data
                function initCascade() {
                    fetchJson('<?php echo e(route("api.regions.provinces")); ?>')
                        .then(function (provinces) {
                            fillSelect(provSel, provinces, 'Pilih Provinsi', 'nama', 'nama', userProv);
                            if (!userProv) { enable(kotaSel); enable(kecSel); enable(posSel); return; }
                            // Load regencies for user's province
                            return fetchJson('<?php echo e(route("api.regions.regencies", ["province" => "__ID__"])); ?>'.replace('__ID__', provId.value))
                                .then(function (regencies) {
                                    fillSelect(kotaSel, regencies, 'Pilih Kota/Kabupaten', 'nama', 'nama', userKota);
                                    enable(kotaSel);
                                    if (!userKota) { enable(kecSel); enable(posSel); return; }
                                    // Load districts for user's kota
                                    return fetchJson('<?php echo e(route("api.regions.districts", ["regency" => "__ID__"])); ?>'.replace('__ID__', kotaId.value))
                                        .then(function (districts) {
                                            fillSelect(kecSel, districts, 'Pilih Kecamatan', 'nama', 'nama', userKec);
                                            enable(kecSel);
                                            if (!userKec) { enable(posSel); return; }
                                            // Load postal codes for user's kecamatan
                                            return fetchJson('<?php echo e(route("api.regions.postalCodes", ["district" => "__ID__"])); ?>'.replace('__ID__', kecId.value))
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

                // CASCADE: on change, reset children and load
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
                    fetchJson('<?php echo e(route("api.regions.regencies", ["province" => "__ID__"])); ?>'.replace('__ID__', provId.value))
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
                    kecSel.disabled = true; kecSel.classList.add('opacity-50');
                    posSel.disabled = true; posSel.classList.add('opacity-50');
                    kecSel.innerHTML = '<option value="">Memuat...</option>';
                    posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                    if (!kotaSel.value) {
                        kecSel.innerHTML = '<option value="">Pilih Kota dulu</option>';
                        return;
                    }
                    fetchJson('<?php echo e(route("api.regions.districts", ["regency" => "__ID__"])); ?>'.replace('__ID__', kotaId.value))
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
                    posSel.disabled = true; posSel.classList.add('opacity-50');
                    posSel.innerHTML = '<option value="">Memuat...</option>';
                    if (!kecSel.value) {
                        posSel.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                        return;
                    }
                    fetchJson('<?php echo e(route("api.regions.postalCodes", ["district" => "__ID__"])); ?>'.replace('__ID__', kecId.value))
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
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" rows="2" required
                      class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45"><?php echo e(old('alamat', $user->alamat)); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN PROFIL</button>
    </form>

    <form method="POST" action="<?php echo e(route('profile.password')); ?>" class="mt-6 space-y-4 rounded-xl border border-rule bg-white p-6">
        <?php echo csrf_field(); ?>
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ganti Kata Sandi</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Lama <span class="text-danger">*</span></label>
                <input type="password" name="current_password" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
        </div>

        <button type="submit" class="rounded-lg border border-rule px-6 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">GANTI KATA SANDI</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/profile/edit.blade.php ENDPATH**/ ?>