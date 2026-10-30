<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['seo', 'jsonLd' => null]));

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

foreach (array_filter((['seo', 'jsonLd' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<title><?php echo e($seo['title']); ?></title>
<meta name="description" content="<?php echo e($seo['description']); ?>">

<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo e($seo['title']); ?>">
<meta property="og:description" content="<?php echo e($seo['description']); ?>">
<meta property="og:image" content="<?php echo e($seo['image']); ?>">
<meta property="og:url" content="<?php echo e($seo['url']); ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($seo['title']); ?>">
<meta name="twitter:description" content="<?php echo e($seo['description']); ?>">
<meta name="twitter:image" content="<?php echo e($seo['image']); ?>">

<link rel="canonical" href="<?php echo e($seo['url']); ?>">

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jsonLd): ?>
    <script type="application/ld+json"><?php echo json_encode($jsonLd); ?></script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/components/seo-head.blade.php ENDPATH**/ ?>