<?php $__env->startSection('title', 'Pesanan Saya — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pesanan</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Pesanan Saya</h1>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->isEmpty()): ?>
        <div class="mt-6 rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/50">
            Belum ada pesanan.
            <a href="/" class="ml-1 font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
        </div>
    <?php else: ?>
        <div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">No Order</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Total</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-rule/50">
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($order->no_order); ?></td>
                            <td class="px-4 py-3 text-ink/70"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                            <td class="px-4 py-3 text-ink/80"><?php echo e(format_rp($order->total)); ?></td>
                            <td class="px-4 py-3"><?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $attributes = $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $component = $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo e(route('orders.show', $order->no_order)); ?>" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($orders->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/orders/index.blade.php ENDPATH**/ ?>