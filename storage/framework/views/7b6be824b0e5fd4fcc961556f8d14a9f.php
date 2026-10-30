<?php $__env->startSection('title', $user->exists ? 'Edit User' : 'Tambah User'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold"><?php echo e($user->exists ? 'Edit: '.$user->name : 'Tambah User (Inject)'); ?></h1>

    <form method="POST"
          action="<?php echo e($user->exists ? route('office.users.update', $user) : route('office.users.store')); ?>"
          class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <?php echo csrf_field(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Password <?php echo e($user->exists ? '(kosongkan bila tetap)' : '*'); ?></label>
                <input type="password" name="password" <?php echo e($user->exists ? '' : 'required'); ?> class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Role</label>
                <select name="role" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="customer" <?php if(old('role', $user->role ?: 'customer') === 'customer'): echo 'selected'; endif; ?>>customer</option>
                    <option value="admin" <?php if(old('role') === 'admin' || $user->role === 'admin'): echo 'selected'; endif; ?>>admin</option>
                </select>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($user->exists)): ?>
                <div>
                    <label class="block text-sm font-medium">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_lahir" value="<?php echo e(old('tgl_lahir')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">No HP <span class="text-red-500">*</span></label>
                    <input type="text" name="no_hp" value="<?php echo e(old('no_hp')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Provinsi <span class="text-red-500">*</span></label>
                    <input type="text" name="provinsi" value="<?php echo e(old('provinsi')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kota <span class="text-red-500">*</span></label>
                    <input type="text" name="kota" value="<?php echo e(old('kota')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kecamatan <span class="text-red-500">*</span></label>
                    <input type="text" name="kecamatan" value="<?php echo e(old('kecamatan')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kode Pos <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_pos" value="<?php echo e(old('kode_pos')); ?>" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="2" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><?php echo e(old('alamat')); ?></textarea>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">
            <?php echo e($user->exists ? 'SIMPAN PERUBAHAN' : 'SIMPAN USER'); ?>

        </button>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($visit)): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'source') || data_get($visit, 'landing_page')): ?>
            <div class="mt-4 rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Sumber Referral</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'source')): ?>
                    <p class="mt-2 text-sm text-ink/80"><?php echo e(data_get($visit, 'source')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'landing_page')): ?>
                    <p class="text-xs text-ink/50"><?php echo e(data_get($visit, 'landing_page')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/users/form.blade.php ENDPATH**/ ?>