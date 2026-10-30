<div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <h2 class="text-sm font-semibold">Split Box — Slot &amp; Kloter</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusMessage): ?>
        <p class="mt-2 rounded bg-green-50 px-3 py-2 text-sm text-green-800"><?php echo e($statusMessage); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $product->splitBox): ?>
        <p class="mt-2 text-sm text-gray-500">Aktifkan <strong>is_split_box</strong> lalu simpan produk untuk mengelola slot.</p>
    <?php else: ?>
        <table class="mt-3 w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="py-2">Karakter</th>
                    <th class="py-2">Role</th>
                    <th class="py-2">Qty/box</th>
                    <th class="py-2">Sisa stok</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->splitBox->slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-gray-50" wire:key="slot-<?php echo e($slot->id); ?>">
                        <td class="py-2"><?php echo e($slot->variant?->name); ?></td>
                        <td class="py-2">
                            <select wire:model.defer="slotRoles.<?php echo e($slot->id); ?>" class="rounded border border-gray-200 px-2 py-1 text-xs">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['normal', 'hot_1', 'hot_2']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role); ?>" <?php if($slot->split_role === $role): echo 'selected'; endif; ?>><?php echo e($role); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </td>
                        <td class="py-2">
                            <input type="number" wire:model.defer="slotQtys.<?php echo e($slot->id); ?>" min="1"
                                   value="<?php echo e($slot->qty_per_box); ?>" class="w-16 rounded border border-gray-200 px-2 py-1 text-xs">
                        </td>
                        <td class="py-2"><?php echo e($slot->batchSlots->sum(fn ($bs) => max(0, $bs->qty_total - $bs->qty_taken))); ?></td>
                        <td class="py-2 text-right">
                            <button wire:click="saveSlot(<?php echo e($slot->id); ?>, '<?php echo e($slot->split_role); ?>', <?php echo e($slot->qty_per_box); ?>)"
                                    class="text-xs text-sky-600 hover:underline">Simpan</button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <h3 class="mt-4 text-sm font-semibold">Kloter</h3>
        <ul class="mt-2 space-y-1 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->splitBox->batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between rounded border border-gray-100 px-3 py-2">
                    <span>
                        Kloter #<?php echo e($batch->batch_no); ?> — <?php echo e($batch->status); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batch->is_auto_opened): ?> (auto) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batch->closes_at): ?> · closes <?php echo e($batch->closes_at->format('d/m/Y H:i')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batch->status === 'open'): ?>
                        <button wire:click="closeRefund(<?php echo e($batch->id); ?>)"
                                class="text-xs text-red-600 hover:underline"
                                onclick="return confirm('Tutup kloter & refund semua order terkunci?')">
                            Tutup Kloter &amp; Refund Semua
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>

        <button wire:click="openBatch" class="mt-3 rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">
            + Buka Kloter Baru
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/livewire/split-box-manager.blade.php ENDPATH**/ ?>