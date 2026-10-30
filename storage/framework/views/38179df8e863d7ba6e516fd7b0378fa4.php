<?php $__env->startSection('title', 'Daftar — Mercatoria'); ?>

<?php $__env->startSection('width', 'w-full max-w-2xl'); ?>

<?php $__env->startSection('content'); ?>
    <div class="rounded-xl border border-rule bg-white px-6 py-8 shadow-[0_1px_0_rgba(20,27,34,0.04)] sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pendaftaran</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">
            Daftar Akun Baru
        </h1>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('buyer_protection')): ?>
            <div class="mt-5 rounded-xl border border-rule bg-paper px-4 py-4 sm:px-5">
                <p class="flex items-start gap-2.5 font-semibold text-ink">
                    <?php if (isset($component)) { $__componentOriginal4bd4abcc0b6a5d303dd619fff16100e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4bd4abcc0b6a5d303dd619fff16100e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.shield-check','data' => ['class' => 'mt-0.5 text-link','width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.shield-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-0.5 text-link','width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4bd4abcc0b6a5d303dd619fff16100e2)): ?>
<?php $attributes = $__attributesOriginal4bd4abcc0b6a5d303dd619fff16100e2; ?>
<?php unset($__attributesOriginal4bd4abcc0b6a5d303dd619fff16100e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4bd4abcc0b6a5d303dd619fff16100e2)): ?>
<?php $component = $__componentOriginal4bd4abcc0b6a5d303dd619fff16100e2; ?>
<?php unset($__componentOriginal4bd4abcc0b6a5d303dd619fff16100e2); ?>
<?php endif; ?>
                    <span>Email atau Nomor WhatsApp ini sudah terdaftar!</span>
                </p>
                <p class="mt-2 pl-[26px] text-sm text-ink/70">
                    Demi keamanan riwayat pesanan &amp; koin kamu, pendaftaran ulang tidak diizinkan.
                    Silakan masuk menggunakan akun lama kamu.
                </p>
                <div class="mt-3 flex flex-wrap gap-3 pl-[26px]">
                    <a href="<?php echo e(route('login')); ?>" class="rounded-lg bg-black px-4 py-2 text-xs font-semibold tracking-wide text-white transition-colors hover:bg-ink">Masuk ke Akun Lama</a>
                    <a href="<?php echo e(route('password.request')); ?>" class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">Lupa Password? Reset Disini</a>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-5 rounded-xl border border-rule bg-paper px-4 py-4 sm:px-5">
            <p class="flex items-start gap-2.5 text-sm text-ink/80">
                <?php if (isset($component)) { $__componentOriginal8c95dd796d6f3a930a3b53ef3a03cafb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c95dd796d6f3a930a3b53ef3a03cafb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.exclamation-triangle-fill','data' => ['class' => 'mt-0.5 text-ink/55','width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.exclamation-triangle-fill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-0.5 text-ink/55','width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c95dd796d6f3a930a3b53ef3a03cafb)): ?>
<?php $attributes = $__attributesOriginal8c95dd796d6f3a930a3b53ef3a03cafb; ?>
<?php unset($__attributesOriginal8c95dd796d6f3a930a3b53ef3a03cafb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c95dd796d6f3a930a3b53ef3a03cafb)): ?>
<?php $component = $__componentOriginal8c95dd796d6f3a930a3b53ef3a03cafb; ?>
<?php unset($__componentOriginal8c95dd796d6f3a930a3b53ef3a03cafb); ?>
<?php endif; ?>
                <span>Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran.</span>
            </p>
        </div>

        <div class="mt-6 h-px bg-rule"></div>

        <form method="POST" action="<?php echo e(route('register.store')); ?>" class="mt-6 space-y-5">
            <?php echo csrf_field(); ?>

            
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
                    <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required minlength="2" maxlength="100"
                           placeholder="Min 2 karakter"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="tgl_lahir" class="flex items-center gap-1.5 font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Tanggal Lahir
                        <?php if (isset($component)) { $__componentOriginal94ca7096e74c20f3fa526424e9f8c952 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal94ca7096e74c20f3fa526424e9f8c952 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.lock-fill','data' => ['class' => 'text-ink/45','width' => '11','height' => '11']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.lock-fill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-ink/45','width' => '11','height' => '11']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal94ca7096e74c20f3fa526424e9f8c952)): ?>
<?php $attributes = $__attributesOriginal94ca7096e74c20f3fa526424e9f8c952; ?>
<?php unset($__attributesOriginal94ca7096e74c20f3fa526424e9f8c952); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal94ca7096e74c20f3fa526424e9f8c952)): ?>
<?php $component = $__componentOriginal94ca7096e74c20f3fa526424e9f8c952; ?>
<?php unset($__componentOriginal94ca7096e74c20f3fa526424e9f8c952); ?>
<?php endif; ?>
                        <span class="text-danger">*</span>
                        <span class="font-sans text-[11px] font-normal tracking-normal normal-case text-ink/45">(Tidak bisa diubah)</span>
                    </label>
                    <div class="relative mt-2">
                        <input type="date" name="tgl_lahir" id="tgl_lahir" value="<?php echo e(old('tgl_lahir')); ?>" required
                               class="w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tgl_lahir'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="no_hp" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        No HP (WhatsApp)
                        <span class="text-danger">*</span>
                    </label>
                    <input type="tel" name="no_hp" id="no_hp" value="<?php echo e(old('no_hp')); ?>" required
                           placeholder="08xxx / 628xxx"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['no_hp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Email
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required
                           placeholder="email@domain.com"
                           class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="provinsi" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Provinsi
                        <span class="text-danger">*</span>
                    </label>
                    <select name="provinsi" id="provinsi" required data-region="province"
                            class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <option value="">Pilih Provinsi</option>
                    </select>
                    <input type="hidden" name="provinsi_id" id="provinsi_id" value="<?php echo e(old('provinsi_id')); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['provinsi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="kota" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kota / Kabupaten
                        <span class="text-danger">*</span>
                    </label>
                    <select name="kota" id="kota" required data-region="regency" disabled
                            class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                        <option value="">Pilih Provinsi dulu</option>
                    </select>
                    <input type="hidden" name="kota_id" id="kota_id" value="<?php echo e(old('kota_id')); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kota'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="kecamatan" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kecamatan
                        <span class="text-danger">*</span>
                    </label>
                    <select name="kecamatan" id="kecamatan" required data-region="district" disabled
                            class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                        <option value="">Pilih Kota dulu</option>
                    </select>
                    <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="<?php echo e(old('kecamatan_id')); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kecamatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="kode_pos" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                        Kode Pos
                        <span class="text-danger">*</span>
                    </label>
                    <select name="kode_pos" id="kode_pos" required disabled
                            class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink opacity-50 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 disabled:cursor-not-allowed">
                        <option value="">Pilih Kecamatan dulu</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kode_pos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        sel.outerHTML = '<input type="text" name="' + name + '" id="' + name + '" value="' + (val || '') + '" required maxlength="100" class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">';
                    }

                    // Load provinces
                    fetchJson('<?php echo e(route("api.regions.provinces")); ?>')
                        .then(function (data) {
                            fillSelect(provSel, data, 'Pilih Provinsi', 'nama', 'nama');
                        })
                        .catch(function () {
                            fallbackInput(provSel, 'provinsi', '<?php echo e(old("provinsi")); ?>');
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
                        fetchJson('<?php echo e(route("api.regions.regencies", ["province" => "__ID__"])); ?>'.replace('__ID__', provId.value))
                            .then(function (data) {
                                fillSelect(kotaSel, data, 'Pilih Kota/Kabupaten', 'nama', 'nama');
                                kotaSel.disabled = false; kotaSel.classList.remove('opacity-50');
                            })
                            .catch(function () {
                                kotaSel.disabled = false; kotaSel.classList.remove('opacity-50');
                                fallbackInput(kotaSel, 'kota', '<?php echo e(old("kota")); ?>');
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
                                kecSel.disabled = false; kecSel.classList.remove('opacity-50');
                            })
                            .catch(function () {
                                kecSel.disabled = false; kecSel.classList.remove('opacity-50');
                                fallbackInput(kecSel, 'kecamatan', '<?php echo e(old("kecamatan")); ?>');
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
                                posSel.disabled = false; posSel.classList.remove('opacity-50');
                            })
                            .catch(function () {
                                posSel.disabled = false; posSel.classList.remove('opacity-50');
                                fallbackInput(posSel, 'kode_pos', '<?php echo e(old("kode_pos")); ?>');
                            });
                    });
                })();
            </script>

            <div>
                <label for="alamat" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    Alamat Lengkap
                    <span class="text-danger">*</span>
                </label>
                <textarea name="alamat" id="alamat" rows="3" required maxlength="500"
                          placeholder="Jalan, No Rumah, RT/RW, Patokan..."
                          class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45"><?php echo e(old('alamat')); ?></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                            <?php if (isset($component)) { $__componentOriginalde15eeb0c668b68fe9f06ac8629cf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalde15eeb0c668b68fe9f06ac8629cf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.eye-fill','data' => ['class' => 'hidden [[data-shown=\'1\']_&]:block']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.eye-fill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden [[data-shown=\'1\']_&]:block']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalde15eeb0c668b68fe9f06ac8629cf6c7)): ?>
<?php $attributes = $__attributesOriginalde15eeb0c668b68fe9f06ac8629cf6c7; ?>
<?php unset($__attributesOriginalde15eeb0c668b68fe9f06ac8629cf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalde15eeb0c668b68fe9f06ac8629cf6c7)): ?>
<?php $component = $__componentOriginalde15eeb0c668b68fe9f06ac8629cf6c7; ?>
<?php unset($__componentOriginalde15eeb0c668b68fe9f06ac8629cf6c7); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalb44b60ae9c8bc7ca425c7e11c1519b50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb44b60ae9c8bc7ca425c7e11c1519b50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.eye-slash-fill','data' => ['class' => 'block [[data-shown=\'1\']_&]:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.eye-slash-fill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block [[data-shown=\'1\']_&]:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb44b60ae9c8bc7ca425c7e11c1519b50)): ?>
<?php $attributes = $__attributesOriginalb44b60ae9c8bc7ca425c7e11c1519b50; ?>
<?php unset($__attributesOriginalb44b60ae9c8bc7ca425c7e11c1519b50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb44b60ae9c8bc7ca425c7e11c1519b50)): ?>
<?php $component = $__componentOriginalb44b60ae9c8bc7ca425c7e11c1519b50; ?>
<?php unset($__componentOriginalb44b60ae9c8bc7ca425c7e11c1519b50); ?>
<?php endif; ?>
                        </button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                       value="<?php echo e(old('referral_code', $referralCode)); ?>"
                       <?php if(request()->filled('ref')): ?> readonly <?php endif; ?>
                       placeholder="MER-REF-8821"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 read-only:bg-paper read-only:text-ink/60">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['referral_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="space-y-3 rounded-xl border border-rule bg-paper px-4 py-4">
                <label class="flex items-start gap-2.5 text-sm text-ink/85">
                    <input type="checkbox" name="agree_tnc" value="1" <?php if(old('agree_tnc')): echo 'checked'; endif; ?> class="mt-0.5 h-4 w-4 rounded border-rule text-black focus:ring-accent/50">
                    <span>Saya menyetujui <a href="/tnc" class="font-medium text-link underline decoration-link/35 underline-offset-4 hover:text-ink">Syarat &amp; Ketentuan</a> <span class="text-danger">*</span></span>
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['agree_tnc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <label class="flex items-start gap-2.5 text-sm text-ink/85">
                    <input type="checkbox" name="agree_privacy" value="1" <?php if(old('agree_privacy')): echo 'checked'; endif; ?> class="mt-0.5 h-4 w-4 rounded border-rule text-black focus:ring-accent/50">
                    <span>Saya menyetujui <a href="/kebijakan-privasi" class="font-medium text-link underline decoration-link/35 underline-offset-4 hover:text-ink">Kebijakan Privasi</a> <span class="text-danger">*</span></span>
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['agree_privacy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                DAFTAR SEKARANG
            </button>

            <p class="pt-1 text-center text-sm text-ink/65">
                Sudah punya akun?
                <a href="<?php echo e(route('login')); ?>" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Masuk</a>
            </p>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/auth/register.blade.php ENDPATH**/ ?>