<?php $__env->startSection('title', 'Hasil Pencarian — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-[1200px]">
        <div class="rounded-xl border border-rule bg-white p-6">
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pencarian</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">
                Hasil Pencarian
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== ''): ?> untuk &ldquo;<?php echo e($q); ?>&rdquo; <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h1>

            <form action="<?php echo e(route('search')); ?>" method="GET" class="mt-4 flex gap-2">
                <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Cari produk, karakter, game..."
                       class="flex-1 rounded-lg border border-rule bg-white px-4 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-ink">
                    Cari
                </button>
            </form>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->total() > 0): ?>
                        Menampilkan <?php echo e($results->firstItem()); ?>&ndash;<?php echo e($results->lastItem()); ?> dari <?php echo e($results->total()); ?> hasil
                    <?php else: ?>
                        Tidak ada hasil ditemukan.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->total() === 0 && isset($games)): ?>
                <div class="mt-4">
                    <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/50">Coba jelajahi game</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('search', ['game_id' => $g->id])); ?>" class="rounded-full border border-rule px-3 py-1 text-xs text-ink/75 transition-colors hover:border-ink hover:text-ink">
                                <?php echo e($g->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <form method="GET" class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-rule bg-white p-3">
            <input type="hidden" name="q" value="<?php echo e($q); ?>">

            <select name="game_id" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Semua Game</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($g->id); ?>" <?php if($filters['game_id'] == $g->id): echo 'selected'; endif; ?>><?php echo e($g->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            <select name="merch_category_id" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Kategori Merch</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if($filters['merch_category_id'] == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            <select name="tag" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Tag</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->slug); ?>" <?php if($filters['tag'] === $t->slug): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            <label for="sort" class="ml-auto font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Urutkan</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink">
                <option value="">Relevansi</option>
                <option value="termurah" <?php if(request('sort') === 'termurah'): echo 'selected'; endif; ?>>Termurah</option>
                <option value="termahal" <?php if(request('sort') === 'termahal'): echo 'selected'; endif; ?>>Termahal</option>
                <option value="terbaru" <?php if(request('sort') === 'terbaru'): echo 'selected'; endif; ?>>Terbaru</option>
            </select>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->isNotEmpty()): ?>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-card','data' => ['product' => $product,'shipping' => $shipping,'matchedVariant' => $matchedVariants[$product->id]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'shipping' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shipping),'matched-variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($matchedVariants[$product->id])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $attributes = $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $component = $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-6">
                <?php echo e($results->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/search/index.blade.php ENDPATH**/ ?>