<?php $__env->startSection('title', $product->exists ? 'Edit Produk' : 'Tambah Produk'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Produk</p><h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink"><?php echo e($product->exists ? 'Edit: '.$product->name : 'Tambah Produk'); ?></h1>

    <form method="POST"
          action="<?php echo e($product->exists ? route('office.products.update', $product) : route('office.products.store')); ?>"
          enctype="multipart/form-data"
          class="mt-4 space-y-4 rounded-xl border border-rule bg-white p-4">
        <?php echo csrf_field(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" required
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">SKU <span class="text-danger">*</span></label>
                <input type="text" name="sku" value="<?php echo e(old('sku', $product->sku)); ?>" required
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Slug</label>
                <input type="text" name="slug" value="<?php echo e(old('slug', $product->slug)); ?>"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Game <span class="text-danger">*</span></label>
                <select name="game_id" required class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($game->id); ?>" <?php if(old('game_id', $product->game_id) == $game->id): echo 'selected'; endif; ?>><?php echo e($game->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Store (override)</label>
                <select name="store_id" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                    <option value="">— ikut game —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($store->id); ?>" <?php if(old('store_id', $product->store_id) == $store->id): echo 'selected'; endif; ?>><?php echo e($store->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Kategori Merch</label>
                <select name="merch_category_id" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                    <option value="">— auto —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if(old('merch_category_id', $product->merch_category_id) == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Harga RMB <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="price_rmb" value="<?php echo e(old('price_rmb', $product->price_rmb)); ?>" required
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price_rmb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Berat (gram)</label>
                <input type="number" name="weight_gram" value="<?php echo e(old('weight_gram', $product->weight_gram)); ?>"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Tipe</label>
                <select name="type" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                    <option value="single" <?php if(old('type', $product->type ?: 'single') === 'single'): echo 'selected'; endif; ?>>Single</option>
                    <option value="variant" <?php if(old('type') === 'variant' || $product->type === 'variant'): echo 'selected'; endif; ?>>Varian</option>
                </select>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Ketersediaan</label>
                <select name="availability" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                    <option value="tersedia" <?php if(old('availability', $product->availability ?: 'tersedia') === 'tersedia'): echo 'selected'; endif; ?>>Tersedia</option>
                    <option value="kosong" <?php if(old('availability') === 'kosong' || $product->availability === 'kosong'): echo 'selected'; endif; ?>>Kosong</option>
                </select>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Diskon (Rp)</label>
                <input type="number" step="0.01" name="discount_amount" value="<?php echo e(old('discount_amount', $product->discount_amount)); ?>"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Periode Diskon</label>
                <div class="mt-1 flex gap-2">
                    <input type="date" name="discount_start" value="<?php echo e(old('discount_start', $product->discount_start?->toDateString())); ?>"
                           class="w-1/2 rounded-lg border border-rule px-3 py-2 text-sm">
                    <input type="date" name="discount_end" value="<?php echo e(old('discount_end', $product->discount_end?->toDateString())); ?>"
                           class="w-1/2 rounded-lg border border-rule px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div>
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Deskripsi</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm"><?php echo e(old('description', $product->description)); ?></textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Gambar Produk</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->image_path): ?><p class="mt-1 text-xs text-ink/45">Saat ini: <?php echo e($product->image_path); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Gambar Deskripsi</label>
                <input type="file" name="description_image" accept=".jpg,.jpeg,.png,.webp"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->description_image_path): ?><p class="mt-1 text-xs text-ink/45">Saat ini: <?php echo e($product->description_image_path); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div>
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Tag</label>
            <div class="mt-1 flex flex-wrap gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" name="tags[]" value="<?php echo e($tag->id); ?>"
                               <?php if(in_array($tag->id, old('tags', $product->exists ? $product->tags->pluck('id')->all() : []))): echo 'checked'; endif; ?>>
                        <?php echo e($tag->name); ?>

                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $product->is_active ?? true)): echo 'checked'; endif; ?>>
            Produk aktif
        </label>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                <?php echo e($product->exists ? 'SIMPAN PERUBAHAN' : 'SIMPAN PRODUK'); ?>

            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->exists && \Illuminate\Support\Facades\Route::has('office.products.duplicate')): ?>
                <form method="POST" action="<?php echo e(route('office.products.duplicate', $product)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-lg border border-rule px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">Duplicate</button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->exists && \Illuminate\Support\Facades\Route::has('office.products.preview')): ?>
                <a href="<?php echo e(route('office.products.preview', $product)); ?>" target="_blank"
                   class="rounded-lg border border-rule px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">Preview</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->exists): ?>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('split-box-manager', ['product' => $product]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1938546242-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <div class="mt-6 rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Varian</h2>

            <table class="mt-3 w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nama</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">SKU</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga RMB</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Berat (g)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-gray-50">
                            <td class="py-2"><?php echo e($variant->name); ?></td>
                            <td class="py-2"><?php echo e($variant->sku); ?></td>
                            <td class="py-2"><?php echo e($variant->price_rmb ?? '-'); ?></td>
                            <td class="py-2"><?php echo e($variant->weight_gram ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>

            <form method="POST" action="<?php echo e(route('office.products.variants.bulk-weight', $product)); ?>" class="mt-4 flex items-end gap-2">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium">Bulk Edit Berat (Set Same)</label>
                    <input type="number" name="weight_gram" required placeholder="250"
                           class="mt-1 w-28 rounded-lg border border-rule px-3 py-2 text-sm">
                </div>
                <select name="ids[]" multiple size="3" class="rounded-lg border border-rule px-3 py-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($variant->id); ?>"><?php echo e($variant->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Apply</button>
            </form>

            <form method="POST" action="<?php echo e(route('office.products.variants.store', $product)); ?>" class="mt-4 grid grid-cols-2 gap-2" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="text" name="name" placeholder="Nama varian" required class="rounded-lg border border-rule px-3 py-2 text-sm">
                <input type="text" name="sku" placeholder="SKU" required class="rounded-lg border border-rule px-3 py-2 text-sm">
                <input type="number" step="0.01" name="price_rmb" placeholder="Harga RMB" class="rounded-lg border border-rule px-3 py-2 text-sm">
                <input type="number" name="weight_gram" placeholder="Berat (g)" class="rounded-lg border border-rule px-3 py-2 text-sm">
                <select name="availability" class="rounded-lg border border-rule px-3 py-2 text-sm">
                    <option value="tersedia">Tersedia</option>
                    <option value="kosong">Kosong</option>
                </select>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="rounded-lg border border-rule px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Varian</button>
            </form>
        </div>

        <form method="POST" action="<?php echo e(route('office.products.destroy', $product)); ?>" class="mt-6"
              onsubmit="return confirm('Hapus permanen produk ini? Order lama tetap utuh dari snapshot.')">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" class="rounded-lg border border-danger/40 px-4 py-2 text-sm text-danger hover:border-red-600">
                Hapus Permanen
            </button>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/products/form.blade.php ENDPATH**/ ?>