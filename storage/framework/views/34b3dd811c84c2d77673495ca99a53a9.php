<?php $__env->startSection('title', 'Keranjang — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Belanja</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Keranjang</h1>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->isEmpty()): ?>
        <div class="mt-6 rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/50">
            Keranjang kamu masih kosong.
            <a href="/" class="ml-1 font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
        </div>
    <?php else: ?>
        <form id="checkout-form" action="<?php echo e(route('checkout.show')); ?>" method="GET">
            <div class="mt-6 overflow-hidden rounded-xl border border-rule bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-rule text-left">
                        <tr>
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="checkbox" checked class="rounded border-rule" onclick="document.querySelectorAll('input[name=&quot;items[]&quot;]').forEach(c=>c.checked=this.checked)">
                                    Pilih Semua
                                </label>
                            </th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pricing = app(\App\Services\Order\ShippingCalculatorService::class)
                                    ->calculateItemShipping($item->product, $item->variant);
                            ?>
                            <tr class="border-b border-rule/50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="items[]" value="<?php echo e($item->id); ?>" checked
                                           form="checkout-form" class="rounded border-rule">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-ink"><?php echo e($item->product->name); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->variant): ?>
                                        <p class="text-xs text-ink/55">Varian: <?php echo e($item->variant->name); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->bundle_group_id): ?>
                                        <p class="text-[11px] text-violet-500">Paket Split Box</p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">[<?php echo e($item->product->game->name); ?>]</p>
                                </td>
                                <td class="px-4 py-3 text-ink/80"><?php echo e(format_rp($pricing['final_price_bersih'])); ?></td>
                                <td class="px-4 py-3">
                                    <input type="number" name="qty" value="<?php echo e($item->qty); ?>" min="1" max="999"
                                           form="qty-form-<?php echo e($item->id); ?>"
                                           class="w-16 rounded border border-rule px-2 py-1 text-sm text-ink">
                                    <button type="submit" form="qty-form-<?php echo e($item->id); ?>"
                                            class="ml-1 rounded border border-rule px-2 py-1 text-xs text-ink/70 transition-colors hover:border-ink">
                                        Simpan
                                    </button>
                                </td>
                                <td class="px-4 py-3 font-medium text-ink">
                                    <?php echo e(format_rp($pricing['final_price_bersih'] * $item->qty)); ?>

                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="submit" form="delete-form-<?php echo e($item->id); ?>"
                                            class="text-xs text-danger hover:underline">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-rule bg-white p-4">
                <label for="customer_note" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Catatan untuk Mercatoria</label>
                <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                          form="checkout-form"
                          placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                          class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45"></textarea>
                <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Maksimal 500 karakter.</p>
            </div>

            <button type="submit" form="checkout-form"
                    class="mt-4 rounded-lg bg-black px-8 py-3 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                CHECKOUT ITEM TERPILIH
            </button>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form id="qty-form-<?php echo e($item->id); ?>" method="POST" action="<?php echo e(route('cart.update', $item)); ?>" class="hidden">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            </form>
            <form id="delete-form-<?php echo e($item->id); ?>" method="POST" action="<?php echo e(route('cart.destroy', $item)); ?>" class="hidden">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/cart/index.blade.php ENDPATH**/ ?>