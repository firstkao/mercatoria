<?php $__env->startSection('title', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold">Settings</h1>

    <form method="POST" action="<?php echo e(route('office.settings.update')); ?>" class="mt-4 space-y-6">
        <?php echo csrf_field(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold"><?php echo e(ucfirst($group)); ?></h2>

                <div class="mt-3 space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                            <label class="text-sm" for="setting-<?php echo e($setting->key); ?>">
                                <?php echo e($setting->key); ?>

                                <span class="block text-xs text-gray-400"><?php echo e($setting->description); ?></span>
                            </label>
                            <div class="md:col-span-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($setting->type === 'bool'): ?>
                                    <select name="settings[<?php echo e($setting->key); ?>]" id="setting-<?php echo e($setting->key); ?>" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <option value="1" <?php if($setting->value === '1'): echo 'selected'; endif; ?>>Aktif</option>
                                        <option value="0" <?php if($setting->value === '0'): echo 'selected'; endif; ?>>Nonaktif</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" name="settings[<?php echo e($setting->key); ?>]" id="setting-<?php echo e($setting->key); ?>"
                                           value="<?php echo e($setting->value); ?>"
                                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['settings.'.$setting->key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">SIMPAN SETTINGS</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>