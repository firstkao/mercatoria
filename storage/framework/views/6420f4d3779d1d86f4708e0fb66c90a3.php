<?php $__env->startSection('title', 'Refund'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Refund</h1>

    <form method="GET" class="mt-4 flex items-end gap-2">
        <div>
            <label for="filter-status" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Status</label>
            <select name="status" id="filter-status" onchange="this.form.submit()"
                    class="mt-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="">Semua</option>
                <option value="requested" <?php if($status === 'requested'): echo 'selected'; endif; ?>>Requested</option>
                <option value="completed" <?php if($status === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                <option value="rejected" <?php if($status === 'rejected'): echo 'selected'; endif; ?>>Rejected</option>
            </select>
        </div>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-rule text-left">
                <tr>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Order</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Amount</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Reason</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-rule/50">
                        <td class="px-3 py-2 font-medium text-ink"><?php echo e($refund->order->no_order ?? '—'); ?></td>
                        <td class="px-3 py-2 text-ink/80"><?php echo e(format_rp($refund->amount)); ?></td>
                        <td class="px-3 py-2">
                            <span class="rounded px-2 py-0.5 text-[10px] font-semibold <?php echo e($refund->status === 'completed' ? 'bg-[#43A047] text-white' : ($refund->status === 'rejected' ? 'bg-[#E53935] text-white' : 'bg-amber-100 text-amber-800')); ?>">
                                <?php echo e(strtoupper($refund->status)); ?>

                            </span>
                        </td>
                        <td class="px-3 py-2 text-ink/60"><?php echo e(\Illuminate\Support\Str::limit($refund->reason ?? '—', 50)); ?></td>
                        <td class="px-3 py-2 text-right">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->status === 'requested'): ?>
                                <form method="POST" action="<?php echo e(route('office.refunds.complete', $refund)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-[#43A047] hover:underline">Proses</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('office.refunds.reject', $refund)); ?>" class="ml-2 inline">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-danger hover:underline">Tolak</button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="py-8 text-center text-sm text-ink/45">Belum ada refund.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($refunds->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/refunds/index.blade.php ENDPATH**/ ?>