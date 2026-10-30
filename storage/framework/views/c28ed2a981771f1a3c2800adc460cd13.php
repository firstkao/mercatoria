<?php $__env->startSection('title', 'Admin Log'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold">Admin Log</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">Admin</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Model</th>
                    <th class="px-3 py-2">Deskripsi</th>
                    <th class="px-3 py-2">Old → New</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-gray-50 align-top">
                        <td class="px-3 py-2"><?php echo e($log->created_at->format('d/m/Y H:i')); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->admin->name ?? '-'); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->action); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->model); ?> #<?php echo e($log->model_id); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->description); ?></td>
                        <td class="px-3 py-2 text-xs">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->old_value || $log->new_value): ?>
                                <span class="text-red-600"><?php echo e(json_encode($log->old_value)); ?></span>
                                →
                                <span class="text-green-700"><?php echo e(json_encode($log->new_value)); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/logs/admin.blade.php ENDPATH**/ ?>