<?php $__env->startSection('title', $title.' — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <nav class="mb-2 flex items-center justify-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">
        <a href="/" class="transition-colors hover:text-ink">Beranda</a>
        <span class="text-ink/35">/</span>
        <a href="/" class="transition-colors hover:text-ink">Produk</a>
        <span class="text-ink/35">/</span>
        <span class="text-ink"><?php echo e($title); ?></span>
    </nav>

    <h1 class="text-center font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl"><?php echo e($title); ?></h1>

    <div class="mt-6 flex flex-col gap-6 lg:flex-row">
        <aside class="w-full shrink-0 space-y-4 lg:w-1/4">
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Kategori Game</h2>
                <ul class="mt-2 space-y-1 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="/<?php echo e($g->slug); ?>"
                               class="block rounded px-2 py-1 transition-colors hover:bg-paper <?php echo e(($game?->id ?? null) === $g->id ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink'); ?>">
                                <?php echo e($g->name); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game): ?>
                <div class="rounded-xl border border-rule bg-white p-4">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Kategori Merch</h2>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>
                            <a href="/<?php echo e($game->slug); ?>"
                               class="block rounded px-2 py-1 transition-colors hover:bg-paper <?php echo e(! $activeCategory ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink'); ?>">
                                Semua
                            </a>
                        </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="/<?php echo e($game->slug); ?>/kategori/<?php echo e($category->slug); ?>"
                                   class="block rounded px-2 py-1 transition-colors hover:bg-paper <?php echo e(($activeCategory?->id ?? null) === $category->id ? 'font-semibold text-ink' : 'text-ink/65 hover:text-ink'); ?>">
                                    <?php echo e($category->name); ?>

                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bestOffers->isNotEmpty()): ?>
                <div class="rounded-xl border border-rule bg-white p-4">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Penawaran Terbaik</h2>
                    <ul class="mt-2 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bestOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="/<?php echo e($offer->game->slug); ?>/<?php echo e($offer->slug); ?>" class="flex items-center gap-2 text-sm transition-colors hover:text-ink">
                                    <span class="h-10 w-10 shrink-0 overflow-hidden rounded bg-paper">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($offer->image_path): ?>
                                            <img src="<?php echo e(Storage::url($offer->image_path)); ?>" alt="" class="h-full w-full object-cover">
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <span class="line-clamp-2 text-xs text-ink/75"><?php echo e($offer->name); ?></span>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </aside>

        <div class="flex-1">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                    Menampilkan
                    <?php echo e($products->total() > 0 ? ($products->firstItem()).'&ndash;'.($products->lastItem()) : 0); ?>

                    dari <?php echo e($products->total()); ?> hasil
                </p>

                <form method="GET" class="flex items-center gap-2 text-sm">
                    <label for="sort" class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Urutkan</label>
                    <select name="sort" id="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                        <option value="">Relevansi</option>
                        <option value="termurah" <?php if(request('sort') === 'termurah'): echo 'selected'; endif; ?>>Termurah</option>
                        <option value="termahal" <?php if(request('sort') === 'termahal'): echo 'selected'; endif; ?>>Termahal</option>
                        <option value="terbaru" <?php if(request('sort') === 'terbaru'): echo 'selected'; endif; ?>>Terbaru</option>
                    </select>
                </form>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->isEmpty()): ?>
                <div class="rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/45">
                    Tidak ada produk ditemukan.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-card','data' => ['product' => $product,'shipping' => $shipping]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'shipping' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shipping)]); ?>
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
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/catalog/game.blade.php ENDPATH**/ ?>