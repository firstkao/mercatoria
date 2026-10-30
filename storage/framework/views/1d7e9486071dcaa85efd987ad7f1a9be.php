<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product', 'shipping', 'matchedVariant' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product', 'shipping', 'matchedVariant' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $pricing = $shipping->calculateItemShipping($product, $matchedVariant);
    $tags = $product->tags;
    $href = '/'.$product->game->slug.'/'.$product->slug
        .($matchedVariant ? '?variant='.$matchedVariant->id : '');
?>

<a href="<?php echo e($href); ?>" class="group flex flex-col overflow-hidden rounded-lg border border-rule bg-white transition-colors hover:border-ink">
    <div class="relative aspect-square overflow-hidden bg-paper">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matchedVariant?->image_path): ?>
            <img src="<?php echo e(Storage::url($matchedVariant->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        <?php elseif($product->image_path): ?>
            <img src="<?php echo e(Storage::url($product->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        <?php else: ?>
            <div class="flex h-full w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">Tanpa Foto</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="absolute left-0 top-2 -rotate-45 px-3 py-0.5 text-[9.5px] font-bold uppercase text-white <?php echo e($tag->slug === 'presale' ? 'bg-[#29B6F6]' : ($tag->slug === 'limited' ? 'bg-[#E53935]' : 'bg-[#4FC3F7]')); ?>">
                <?php echo e($tag->name); ?>

            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="flex flex-1 flex-col gap-1 p-3">
        <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
            [<?php echo e($product->game->name); ?>]
        </p>
        <p class="line-clamp-2 text-[11.5px] font-bold leading-[1.35] text-ink"><?php echo e($product->name); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matchedVariant): ?>
            <p class="inline-flex w-fit items-center gap-1 rounded bg-accent/15 px-1.5 py-0.5 text-[11px] text-link">
                Varian tersedia: <?php echo e($matchedVariant->name); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-auto pt-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->hasActiveDiscount()): ?>
                <span class="text-xs text-ink/45 line-through"><?php echo e(format_rp($pricing['base_price_bersih'])); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span class="text-[11.5px] font-bold text-[#29B6F6]"><?php echo e(format_rp($pricing['final_price_bersih'])); ?></span>
        </div>
    </div>
</a>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/components/product-card.blade.php ENDPATH**/ ?>