<?php $__env->startSection('title', 'Input Order Lama'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-semibold">Input Order Lama (Inject)</h1>
    <p class="mt-1 text-sm text-gray-500">PRD 22.7 — harga snapshot bisa diedit manual, status bebas, tanpa bukti bayar.</p>

    <form method="POST" action="<?php echo e(route('office.orders.inject.store')); ?>" class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium">User <span class="text-red-500">*</span></label>
                <select name="user_id" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">— Pilih user (nama / email / HP) —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> · <?php echo e($user->email); ?> · <?php echo e($user->no_hp); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <a href="<?php echo e(route('office.users.create')); ?>" class="mt-1 inline-block text-xs text-sky-600 hover:underline">+ User Baru</a>
            </div>

            <div>
                <label class="block text-sm font-medium">Status Tujuan <span class="text-red-500">*</span></label>
                <select name="status" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>"><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Catatan Pembelian</label>
                <input type="text" name="customer_note" maxlength="500" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium">Tanggal Order</label>
                <input type="date" name="tanggal_order" max="<?php echo e(now()->format('Y-m-d')); ?>"
                       class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-400">Opsional. Masa lalu. Kosong = pakai waktu sekarang.</p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">Item Pesanan</h2>
            <a href="<?php echo e(route('office.products.create')); ?>" class="text-xs text-sky-600 hover:underline">+ Produk Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="px-2 py-2">Produk</th>
                        <th class="px-2 py-2">Varian</th>
                        <th class="px-2 py-2">Qty</th>
                        <th class="px-2 py-2">Harga Snapshot (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 3; $i++): ?>
                        <tr class="border-b border-gray-50">
                            <td class="px-2 py-2">
                                <select name="items[<?php echo e($i); ?>][product_id]" class="w-full rounded border border-gray-200 px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <select name="items[<?php echo e($i); ?>][variant_id]" class="w-full rounded border border-gray-200 px-2 py-1.5 text-sm">
                                    <option value="">— tanpa varian —</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($variant->id); ?>"><?php echo e($product->name); ?> — <?php echo e($variant->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[<?php echo e($i); ?>][qty]" min="1" value="1"
                                       class="w-20 rounded border border-gray-200 px-2 py-1.5 text-sm">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" step="0.01" name="items[<?php echo e($i); ?>][price]" min="0" placeholder="manual"
                                       class="w-32 rounded border border-gray-200 px-2 py-1.5 text-sm">
                            </td>
                        </tr>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400">Baris tanpa produk diabaikan. Harga yang diketik menjadi harga snapshot final (bisa diedit).</p>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">
            SIMPAN ORDER LAMA
        </button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/orders/inject.blade.php ENDPATH**/ ?>