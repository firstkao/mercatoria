<?php $__env->startSection('title', 'Auth Log'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold">Auth Log</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Alasan</th>
                    <th class="px-3 py-2">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-gray-50 <?php echo e($log->action === 'login_failed' ? 'bg-red-50' : ''); ?>">
                        <td class="px-3 py-2"><?php echo e($log->created_at->format('d/m/Y H:i')); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->email_attempt); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->action); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->reason); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->ip_address); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/logs/auth.blade.php ENDPATH**/ ?>