<?php $__env->startSection('title', 'Verifikasi Pembayaran'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold">Verifikasi Pembayaran</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">No Order</th>
                    <th class="px-3 py-2">Tipe</th>
                    <th class="px-3 py-2">Nominal</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Upload</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-gray-50">
                        <td class="px-3 py-2 font-medium"><?php echo e($payment->order->no_order ?? '-'); ?></td>
                        <td class="px-3 py-2"><?php echo e($payment->type); ?></td>
                        <td class="px-3 py-2"><?php echo e(format_rp($payment->amount)); ?></td>
                        <td class="px-3 py-2"><?php echo e($payment->status); ?></td>
                        <td class="px-3 py-2"><?php echo e($payment->uploaded_at?->format('d/m/Y H:i')); ?></td>
                        <td class="px-3 py-2 text-right">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->order): ?>
                                <a href="<?php echo e(route('office.orders.show', $payment->order->no_order)); ?>" class="text-sky-600 hover:underline">Detail</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($payments->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/payments/index.blade.php ENDPATH**/ ?>