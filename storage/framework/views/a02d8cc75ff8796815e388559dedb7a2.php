<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value', 'type' => 'state']));

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

foreach (array_filter((['value', 'type' => 'state']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $state = [
        'Crush' => ['bg' => '#F48FB1', 'text' => '#212121', 'icon' => 'heart'],
        'MVP' => ['bg' => '#43A047', 'text' => '#FFFFFF', 'icon' => 'star'],
        'Ex' => ['bg' => '#64B5F6', 'text' => '#212121', 'icon' => 'clock'],
        'ONS' => ['bg' => '#FFB74D', 'text' => '#212121', 'icon' => 'moon'],
        'Blocked' => ['bg' => '#E53935', 'text' => '#FFFFFF', 'icon' => 'lock'],
    ];
    $tier = [
        'MVP Silver' => ['bg' => '#B0BEC5', 'text' => '#212121', 'icon' => 'shield'],
        'MVP Gold' => ['bg' => '#FFC107', 'text' => '#212121', 'icon' => 'trophy'],
        'MVP Platinum' => ['bg' => '#4FC3F7', 'text' => '#FFFFFF', 'icon' => 'sparkles'],
    ];
    $map = $type === 'tier' ? $tier : $state;
    $meta = $map[$value] ?? ['bg' => '#D8D2C4', 'text' => '#141B22', 'icon' => null];
?>

<span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[10px] font-semibold"
      style="background:<?php echo e($meta['bg']); ?>;color:<?php echo e($meta['text']); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'tier'): ?>
        <?php if (isset($component)) { $__componentOriginalf63dc849b36103e585bc591c17c3f2c6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf63dc849b36103e585bc591c17c3f2c6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.trophy','data' => ['width' => '10','height' => '10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.trophy'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '10','height' => '10']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf63dc849b36103e585bc591c17c3f2c6)): ?>
<?php $attributes = $__attributesOriginalf63dc849b36103e585bc591c17c3f2c6; ?>
<?php unset($__attributesOriginalf63dc849b36103e585bc591c17c3f2c6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf63dc849b36103e585bc591c17c3f2c6)): ?>
<?php $component = $__componentOriginalf63dc849b36103e585bc591c17c3f2c6; ?>
<?php unset($__componentOriginalf63dc849b36103e585bc591c17c3f2c6); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($value); ?>

</span>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/components/user-badge.blade.php ENDPATH**/ ?>