<?php $__env->startSection('title', 'Pesanan '.$order->no_order.' — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pesanan</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Detail Pesanan</h1>

    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">Nomor Pesanan</p>
                <p class="mt-1 text-lg font-semibold tracking-wide text-ink"><?php echo e($order->no_order); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_number): ?>
                    <p class="mt-0.5 font-mono text-xs text-ink/55"><?php echo e($order->invoice_number); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $order->status,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $attributes = $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $component = $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
                <a href="<?php echo e(route('orders.invoice', $order->no_order)); ?>"
                   class="rounded-lg border border-rule px-3 py-1.5 text-sm text-ink transition-colors hover:border-ink">
                    Download Invoice PDF
                </a>
            </div>
        </div>

        <table class="mt-4 w-full text-sm">
            <thead class="border-b border-rule text-left">
                <tr>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Item</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $snapshot = $item->snapshot; ?>
                    <tr class="border-b border-rule/50">
                        <td class="py-2">
                            <p class="font-medium text-ink"><?php echo e($snapshot->product_name); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($snapshot->variant_name): ?>
                                <p class="text-xs text-ink/55">Varian: <?php echo e($snapshot->variant_name); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($snapshot->store_name): ?>
                                <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Store: <?php echo e($snapshot->store_name); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-2 text-ink/80"><?php echo e(format_rp($snapshot->final_price)); ?></td>
                        <td class="py-2 text-ink/80"><?php echo e($snapshot->qty); ?></td>
                        <td class="py-2 text-ink/80"><?php echo e(format_rp($snapshot->subtotal)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <dl class="mt-4 space-y-1 text-sm">
            <div class="flex justify-between"><dt class="text-ink/70">Subtotal</dt><dd class="text-ink"><?php echo e(format_rp($order->subtotal)); ?></dd></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $order->discount_amount > 0): ?>
                <div class="flex justify-between text-danger">
                    <dt>Diskon <?php echo e($order->discount_type === 'coin' ? 'Koin' : 'Voucher'); ?></dt>
                    <dd>- <?php echo e(format_rp($order->discount_amount)); ?></dd>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex justify-between font-medium"><dt class="text-ink">Total Harga Barang</dt><dd class="text-ink"><?php echo e(format_rp($order->total)); ?></dd></div>
            <div class="flex justify-between text-ink/55"><dt>Fee QRIS</dt><dd><?php echo e(format_rp($order->fee_qris)); ?></dd></div>
            <div class="flex justify-between text-lg font-semibold"><dt class="text-ink">Bayar di Web</dt><dd class="text-ink"><?php echo e(format_rp($order->tagihan_web)); ?></dd></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->estimasi_co_marketplace): ?>
                <div class="flex justify-between text-ink/55"><dt>Estimasi CO Marketplace</dt><dd><?php echo e(format_rp($order->estimasi_co_marketplace)); ?></dd></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </dl>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->customer_note): ?>
            <p class="mt-4 rounded-lg bg-paper p-3 text-sm text-ink/75">
                Catatan: <?php echo e($order->customer_note); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->note_to_customer): ?>
            <div class="mt-4 rounded-lg border border-rule bg-white p-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pesan dari Admin</p>
                <p class="mt-1.5 text-sm text-ink/80"><?php echo e($order->note_to_customer); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->note_to_customer_updated_at): ?>
                    <p class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">
                        Diperbarui <?php echo e($order->note_to_customer_updated_at->format('d/m/Y H:i')); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $order->discount_amount > 0 && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true)): ?>
            <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                Koin atau voucher yang dipakai pada pesanan ini hangus permanen bila pembayaran gagal (PRD 9.3).
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'menunggu_pembayaran'): ?>
        <div class="mt-6 rounded-xl border border-rule bg-white p-6">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Bayar &amp; Unggah Bukti</h2>
            <p class="mt-1 text-sm text-ink/70">
                Tagihan: <strong class="text-ink"><?php echo e(format_rp($order->tagihan_web)); ?></strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment_deadline_at): ?>
                    &middot; Batas waktu: <?php echo e($order->payment_deadline_at->format('d/m/Y H:i')); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>

            <form method="POST" action="<?php echo e(route('orders.payments.store', $order->no_order)); ?>" enctype="multipart/form-data"
                  class="mt-4 space-y-3" id="upload-proof-form">
                <?php echo csrf_field(); ?>
                <input type="file" name="proof" accept=".jpg,.jpeg,.png" required
                       class="block w-full text-sm text-ink/70 file:mr-3 file:rounded-lg file:border file:border-rule file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-ink">
                <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Format JPG/PNG, maksimal 1 MB. Hanya bisa diunggah 1 kali.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="submit"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    UNGGAH BUKTI
                </button>
            </form>
            <script>
                (function () {
                    var form = document.getElementById('upload-proof-form');
                    if (!form) return;
                    form.addEventListener('submit', function () {
                        var btn = form.querySelector('button[type="submit"]');
                        if (!btn || btn.disabled) return;
                        btn.disabled = true;
                        btn.textContent = 'Memproses...';
                    });
                })();
            </script>
        </div>
    <?php elseif($order->status === 'ditahan'): ?>
        <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-6">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-sky-900">Bukti Bayar Terkirim</h2>
            <p class="mt-1 text-sm text-sky-800">
                Menunggu verifikasi admin. Form upload terkunci sampai admin menyetujui atau menolak bukti kamu.
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Status</h2>
        <ol class="mt-3 space-y-3 border-l border-rule pl-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="relative">
                    <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full <?php echo e($loop->last ? 'bg-ink' : 'bg-rule'); ?>"></span>
                    <p class="text-sm <?php echo e($loop->last ? 'font-semibold text-ink' : 'text-ink/75'); ?>">
                        <?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $log->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $attributes = $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $component = $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
                    </p>
                    <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">
                        <?php echo e($log->created_at->format('d/m/Y H:i')); ?> &middot; oleh <?php echo e($log->changed_by); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->note): ?> — <?php echo e($log->note); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ol>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/order/show.blade.php ENDPATH**/ ?>