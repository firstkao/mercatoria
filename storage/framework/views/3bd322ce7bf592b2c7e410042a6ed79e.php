<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (isset($component)) { $__componentOriginal4232ba5ed77147a6b6573253fafb715d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4232ba5ed77147a6b6573253fafb715d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo-head','data' => ['seo' => $seo,'jsonLd' => $jsonLd]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['seo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seo),'json-ld' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jsonLd)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4232ba5ed77147a6b6573253fafb715d)): ?>
<?php $attributes = $__attributesOriginal4232ba5ed77147a6b6573253fafb715d; ?>
<?php unset($__attributesOriginal4232ba5ed77147a6b6573253fafb715d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4232ba5ed77147a6b6573253fafb715d)): ?>
<?php $component = $__componentOriginal4232ba5ed77147a6b6573253fafb715d; ?>
<?php unset($__componentOriginal4232ba5ed77147a6b6573253fafb715d); ?>
<?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body id="top" class="flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(setting('promo_bar_active', '1') !== '0'): ?>
        <div id="promo-bar" class="relative bg-[#F2EFE8] px-10 py-2 text-center border-b border-[#D8D2C4]">
            <p class="text-[13px] text-ink">
                Gunakan kode promo <strong class="font-semibold text-danger">NEWWEB</strong> dan nikmati potongan harga sebesar <strong class="font-semibold italic">Rp 25.000</strong> dengan minimal pembelanjaan <strong class="font-semibold italic">Rp 150.000</strong>.
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto max-w-[1200px] px-4">
            <div class="hidden min-h-[72px] lg:grid lg:grid-cols-3 lg:items-center">
                <nav class="flex items-center gap-5" aria-label="Menu utama">
                    <a href="<?php echo e(route('home')); ?>" class="text-[13px] font-medium text-[#4FC3F7]">Beranda</a>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Game
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <a href="<?php echo e(route('catalog.game', $g->slug)); ?>" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]"><?php echo e($g->name); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </details>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Developer
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerDevelopers)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $footerDevelopers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <a href="<?php echo e(data_get($dev, 'url') ?? (data_get($dev, 'slug') ? route('catalog.developer', data_get($dev, 'slug')) : '#')); ?>" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]"><?php echo e(data_get($dev, 'name')); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </details>

                    <a href="#merch-baru" class="text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
                </nav>

                <a href="<?php echo e(route('home')); ?>" class="flex items-center justify-center no-underline">
                    <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" class="h-12 w-auto lg:h-16" width="1608" height="494">
                </a>

                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('search')); ?>" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Cari">
                        <?php if (isset($component)) { $__componentOriginal4915e8c04a943a0310c6ccf0a8e7cb92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4915e8c04a943a0310c6ccf0a8e7cb92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.magnifying-glass','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.magnifying-glass'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4915e8c04a943a0310c6ccf0a8e7cb92)): ?>
<?php $attributes = $__attributesOriginal4915e8c04a943a0310c6ccf0a8e7cb92; ?>
<?php unset($__attributesOriginal4915e8c04a943a0310c6ccf0a8e7cb92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4915e8c04a943a0310c6ccf0a8e7cb92)): ?>
<?php $component = $__componentOriginal4915e8c04a943a0310c6ccf0a8e7cb92; ?>
<?php unset($__componentOriginal4915e8c04a943a0310c6ccf0a8e7cb92); ?>
<?php endif; ?>
                    </a>
                    <div class="flex items-center gap-2.5 text-[#333]">
                        <a href="<?php echo e(setting('url_instagram', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Instagram"><?php if (isset($component)) { $__componentOriginal1ea42232d0b13214e79b5e861644d3ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ea42232d0b13214e79b5e861644d3ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.instagram','data' => ['width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.instagram'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ea42232d0b13214e79b5e861644d3ac)): ?>
<?php $attributes = $__attributesOriginal1ea42232d0b13214e79b5e861644d3ac; ?>
<?php unset($__attributesOriginal1ea42232d0b13214e79b5e861644d3ac); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ea42232d0b13214e79b5e861644d3ac)): ?>
<?php $component = $__componentOriginal1ea42232d0b13214e79b5e861644d3ac; ?>
<?php unset($__componentOriginal1ea42232d0b13214e79b5e861644d3ac); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_facebook', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Facebook"><?php if (isset($component)) { $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.facebook','data' => ['width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.facebook'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $attributes = $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $component = $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_x', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="X"><?php if (isset($component)) { $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.twitter-x','data' => ['width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.twitter-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $attributes = $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $component = $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_threads', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Threads"><?php if (isset($component)) { $__componentOriginale608a3a4d58371f8d58a450f9ade4f85 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale608a3a4d58371f8d58a450f9ade4f85 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.threads','data' => ['width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.threads'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale608a3a4d58371f8d58a450f9ade4f85)): ?>
<?php $attributes = $__attributesOriginale608a3a4d58371f8d58a450f9ade4f85; ?>
<?php unset($__attributesOriginale608a3a4d58371f8d58a450f9ade4f85); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale608a3a4d58371f8d58a450f9ade4f85)): ?>
<?php $component = $__componentOriginale608a3a4d58371f8d58a450f9ade4f85; ?>
<?php unset($__componentOriginale608a3a4d58371f8d58a450f9ade4f85); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_whatsapp', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp"><?php if (isset($component)) { $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.whatsapp','data' => ['width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1)): ?>
<?php $attributes = $__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1; ?>
<?php unset($__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1)): ?>
<?php $component = $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1; ?>
<?php unset($__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1); ?>
<?php endif; ?></a>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('cart.index')); ?>" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Keranjang"><?php if (isset($component)) { $__componentOriginalc6221bf432d0d2487c80c8c8e3ed8cbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6221bf432d0d2487c80c8c8e3ed8cbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.shopping-cart','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.shopping-cart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6221bf432d0d2487c80c8c8e3ed8cbb)): ?>
<?php $attributes = $__attributesOriginalc6221bf432d0d2487c80c8c8e3ed8cbb; ?>
<?php unset($__attributesOriginalc6221bf432d0d2487c80c8c8e3ed8cbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6221bf432d0d2487c80c8c8e3ed8cbb)): ?>
<?php $component = $__componentOriginalc6221bf432d0d2487c80c8c8e3ed8cbb; ?>
<?php unset($__componentOriginalc6221bf432d0d2487c80c8c8e3ed8cbb); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]"><?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.user','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $attributes = $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $component = $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?></a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]"><?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.user','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $attributes = $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $component = $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="flex min-h-[64px] items-center justify-between gap-3 lg:hidden">
                <details class="relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-[#333]" aria-label="Menu">
                        <?php if (isset($component)) { $__componentOriginalfda9c33bba3cbf6c8c18ef880c16f8c0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfda9c33bba3cbf6c8c18ef880c16f8c0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.list','data' => ['width' => '22','height' => '22']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '22','height' => '22']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfda9c33bba3cbf6c8c18ef880c16f8c0)): ?>
<?php $attributes = $__attributesOriginalfda9c33bba3cbf6c8c18ef880c16f8c0; ?>
<?php unset($__attributesOriginalfda9c33bba3cbf6c8c18ef880c16f8c0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfda9c33bba3cbf6c8c18ef880c16f8c0)): ?>
<?php $component = $__componentOriginalfda9c33bba3cbf6c8c18ef880c16f8c0; ?>
<?php unset($__componentOriginalfda9c33bba3cbf6c8c18ef880c16f8c0); ?>
<?php endif; ?>
                    </summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-64 rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                        <a href="<?php echo e(route('home')); ?>" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
                        <a href="#game-grid" class="block px-4 py-2.5 text-[13px] text-[#333]">Game</a>
                        <a href="#merch-baru" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('catalog.game', $g->slug)); ?>" class="block px-4 py-2 text-[12px] text-[#333]"><?php echo e($g->name); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerDevelopers)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerDevelopers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(data_get($dev, 'url') ?? (data_get($dev, 'slug') ? route('catalog.developer', data_get($dev, 'slug')) : '#')); ?>" class="block px-4 py-2 text-[12px] text-[#333]"><?php echo e(data_get($dev, 'name')); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <a href="<?php echo e(route('login')); ?>" class="block px-4 py-2.5 text-[13px] font-medium text-[#333]">Masuk</a>
                        </div>
                    </div>
                </details>

                <a href="<?php echo e(route('home')); ?>" class="flex items-center no-underline">
                    <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" class="h-12 w-auto" width="1608" height="494">
                </a>

                <a href="<?php echo e(route('login')); ?>" class="flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Masuk">
                    <?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.user','data' => ['width' => '20','height' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '20','height' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $attributes = $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $component = $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        
        <section id="hero" class="mx-auto max-w-[1200px] px-4 py-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($heroSlides)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($heroSlides) > 0): ?>
                    <div class="relative overflow-hidden rounded-lg border border-rule" id="hero-carousel">
                        <div class="relative aspect-[2/1] bg-ink" id="hero-track">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(data_get($slide, 'url') ?? '#'); ?>" class="absolute inset-0 <?php echo e($i === 0 ? '' : 'hidden'); ?>" data-hero-slide="<?php echo e($i); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($slide, 'image')): ?>
                                        <img src="<?php echo e(data_get($slide, 'image')); ?>" alt="" class="h-full w-full object-cover">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <button type="button" id="hero-prev" class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Sebelumnya">
                            <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['class' => 'rotate-90','width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rotate-90','width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
                        </button>
                        <button type="button" id="hero-next" class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Berikutnya">
                            <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['class' => '-rotate-90','width' => '16','height' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '-rotate-90','width' => '16','height' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
                        </button>

                        <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2" id="hero-dots">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" data-hero-dot="<?php echo e($i); ?>" class="h-2 w-2 rounded-full <?php echo e($i === 0 ? 'bg-white' : 'bg-white/45'); ?>" aria-label="Slide <?php echo e($i + 1); ?>"></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-rule bg-[#F5F7FA] px-8 py-16 text-center">
                        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Mercatoria</p>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-ink">Merch &amp; Collectibles untuk IP Favoritmu</h1>
                        <p class="mt-2 text-sm text-ink/65">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="rounded-lg border border-rule bg-[#F5F7FA] px-8 py-16 text-center">
                    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Mercatoria</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold text-ink">Merch &amp; Collectibles untuk IP Favoritmu</h1>
                    <p class="mt-2 text-sm text-ink/65">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="mt-3 h-px bg-rule"></div>
        </section>

        
        <section id="game-grid" class="mx-auto max-w-[1200px] px-4 pb-12">
            <h2 class="text-center font-display text-[26px] font-semibold text-ink">Jelajahi Game Favoritmu</h2>
            <p class="mt-1 text-center text-[13px] text-ink/60">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori</p>

            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('catalog.game', $game->slug)); ?>" class="group relative block aspect-square overflow-hidden rounded-lg bg-paper">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->logo_path): ?>
                            <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($game->logo_path)); ?>" alt="<?php echo e($game->name); ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center bg-[#F5F7FA]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35"><?php echo e($game->name); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="absolute bottom-2 left-1/2 w-[82%] -translate-x-1/2 rounded bg-white/92 px-2 py-1.5 text-center">
                            <p class="text-[10.5px] font-bold uppercase leading-tight text-ink"><?php echo e($game->name); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="col-span-full text-center text-sm text-ink/45">Game akan segera hadir.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($bestOffers ?? collect())->isNotEmpty()): ?>
            <section id="penawaran-terbaik" class="mx-auto max-w-[1200px] px-4 pb-12">
                <h2 class="text-center font-display text-[22px] font-semibold text-ink">Penawaran Terbaik</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bestOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('product.show', [$product->game->slug, $product->slug])); ?>" class="group block">
                            <div class="relative aspect-square overflow-hidden rounded-lg bg-paper">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->image_path): ?>
                                    <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($product->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#E53935] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">DISKON</span>
                            </div>
                            <p class="mt-2 text-[11.5px] font-bold leading-snug text-ink">
                                <span class="text-ink/60">[<?php echo e($product->game->name); ?>]</span> <?php echo e($product->name); ?>

                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->hasActiveDiscount() && !empty($product->price_idr)): ?>
                                <p class="mt-0.5 text-xs text-ink/45 line-through"><?php echo e(format_rp($product->price_idr + (float) $product->discount_amount)); ?></p>
                                <p class="text-[11.5px] font-bold text-[#29B6F6]"><?php echo e(format_rp($product->price_idr)); ?></p>
                            <?php elseif(!empty($product->price_idr)): ?>
                                <p class="mt-0.5 text-[11.5px] font-bold text-[#29B6F6]"><?php echo e(format_rp($product->price_idr)); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <section id="merch-baru" class="mx-auto max-w-[1200px] px-4 pb-14">
            <h2 class="text-center text-[18px] font-bold uppercase tracking-wide text-ink">MERCH BARU</h2>

            <div class="mt-5 grid grid-cols-2 gap-3.5 md:grid-cols-5 lg:grid-cols-7">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('product.show', [$product->game->slug, $product->slug])); ?>" class="group block">
                        <div class="relative aspect-square overflow-hidden rounded bg-paper">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->image_path): ?>
                                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($product->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->tags->contains('slug', 'presale')): ?>
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#29B6F6] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">PRESALE</span>
                            <?php elseif($product->tags->contains('slug', 'limited')): ?>
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#E53935] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">LIMITED</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="mt-1.5 text-[11.5px] font-bold leading-[1.35] text-ink">
                            <span class="text-ink/55">[<?php echo e($product->game->name); ?>]</span> <?php echo e($product->name); ?>

                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($product->price_idr)): ?>
                            <p class="mt-0.5 text-[11.5px] font-bold text-[#29B6F6]"><?php echo e(format_rp($product->price_idr)); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="col-span-full text-center text-sm text-ink/45">Belum ada merch.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
    </main>

    
    <footer id="footer" class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col items-center">
                    <a href="<?php echo e(route('home')); ?>" class="inline-flex flex-col items-center no-underline">
                        <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" class="h-12 w-auto lg:h-14" width="1608" height="494">
                    </a>
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <a href="<?php echo e(setting('url_instagram', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Instagram"><?php if (isset($component)) { $__componentOriginal1ea42232d0b13214e79b5e861644d3ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ea42232d0b13214e79b5e861644d3ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.instagram','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.instagram'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ea42232d0b13214e79b5e861644d3ac)): ?>
<?php $attributes = $__attributesOriginal1ea42232d0b13214e79b5e861644d3ac; ?>
<?php unset($__attributesOriginal1ea42232d0b13214e79b5e861644d3ac); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ea42232d0b13214e79b5e861644d3ac)): ?>
<?php $component = $__componentOriginal1ea42232d0b13214e79b5e861644d3ac; ?>
<?php unset($__componentOriginal1ea42232d0b13214e79b5e861644d3ac); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_facebook', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Facebook"><?php if (isset($component)) { $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.facebook','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.facebook'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $attributes = $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $component = $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_x', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="X"><?php if (isset($component)) { $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.twitter-x','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.twitter-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $attributes = $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $component = $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_threads', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Threads"><?php if (isset($component)) { $__componentOriginale608a3a4d58371f8d58a450f9ade4f85 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale608a3a4d58371f8d58a450f9ade4f85 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.threads','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.threads'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale608a3a4d58371f8d58a450f9ade4f85)): ?>
<?php $attributes = $__attributesOriginale608a3a4d58371f8d58a450f9ade4f85; ?>
<?php unset($__attributesOriginale608a3a4d58371f8d58a450f9ade4f85); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale608a3a4d58371f8d58a450f9ade4f85)): ?>
<?php $component = $__componentOriginale608a3a4d58371f8d58a450f9ade4f85; ?>
<?php unset($__componentOriginale608a3a4d58371f8d58a450f9ade4f85); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_whatsapp', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp"><?php if (isset($component)) { $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.whatsapp','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1)): ?>
<?php $attributes = $__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1; ?>
<?php unset($__attributesOriginal5c686fa3cdb117f240449b4ccf99b8c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1)): ?>
<?php $component = $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1; ?>
<?php unset($__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1); ?>
<?php endif; ?></a>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="<?php echo e(setting('url_toco', '#')); ?>" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">TOCO <?php if (isset($component)) { $__componentOriginal37a3f047daccd28b87517bd215a12923 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal37a3f047daccd28b87517bd215a12923 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.arrow-right','data' => ['width' => '12','height' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.arrow-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '12','height' => '12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal37a3f047daccd28b87517bd215a12923)): ?>
<?php $attributes = $__attributesOriginal37a3f047daccd28b87517bd215a12923; ?>
<?php unset($__attributesOriginal37a3f047daccd28b87517bd215a12923); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal37a3f047daccd28b87517bd215a12923)): ?>
<?php $component = $__componentOriginal37a3f047daccd28b87517bd215a12923; ?>
<?php unset($__componentOriginal37a3f047daccd28b87517bd215a12923); ?>
<?php endif; ?></a>
                        <a href="<?php echo e(setting('url_shopee', '#')); ?>" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">SHOPEE <?php if (isset($component)) { $__componentOriginal37a3f047daccd28b87517bd215a12923 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal37a3f047daccd28b87517bd215a12923 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.arrow-right','data' => ['width' => '12','height' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.arrow-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '12','height' => '12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal37a3f047daccd28b87517bd215a12923)): ?>
<?php $attributes = $__attributesOriginal37a3f047daccd28b87517bd215a12923; ?>
<?php unset($__attributesOriginal37a3f047daccd28b87517bd215a12923); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal37a3f047daccd28b87517bd215a12923)): ?>
<?php $component = $__componentOriginal37a3f047daccd28b87517bd215a12923; ?>
<?php unset($__componentOriginal37a3f047daccd28b87517bd215a12923); ?>
<?php endif; ?></a>
                    </div>
                </div>

                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Game
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?></span>
                    </summary>
                    <ul class="mt-3.5 text-[12px] leading-[1.8]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('catalog.game', $game->slug)); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]"><?php echo e($game->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </details>

                <div class="space-y-8">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Jelajahi
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?></span>
                        </summary>
                        <ul class="mt-3.5 text-[12px] leading-[1.8]">
                            <li><a href="<?php echo e(route('page.tentang-kami')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tentang Kami</a></li>
                            <li><a href="<?php echo e(route('page.faq')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tanya Jawab Umum</a></li>
                            <li><a href="<?php echo e(route('page.cara-belanja')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Cara Belanja</a></li>
                        </ul>
                    </details>
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Legal
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?></span>
                        </summary>
                        <ul class="mt-3.5 text-[12px] leading-[1.8]">
                            <li><a href="<?php echo e(route('page.tnc')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Syarat &amp; Ketentuan</a></li>
                            <li><a href="<?php echo e(route('page.kebijakan-privasi')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Kebijakan Privasi</a></li>
                        </ul>
                    </details>
                </div>

                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Produk Terpopuler
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $attributes = $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__attributesOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1)): ?>
<?php $component = $__componentOriginal7c8cb3634876f8430f20291d6ef649c1; ?>
<?php unset($__componentOriginal7c8cb3634876f8430f20291d6ef649c1); ?>
<?php endif; ?></span>
                    </summary>
                    <ul class="mt-3.5 space-y-2 text-[12px]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerTopProducts)): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerTopProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center gap-2.5 border-b border-white/10 pb-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($product, 'image')): ?>
                                        <img src="<?php echo e(data_get($product, 'image')); ?>" alt="" class="h-12 w-12 shrink-0 rounded object-cover">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div class="min-w-0">
                                        <a href="<?php echo e(data_get($product, 'url') ?? '#'); ?>" class="block truncate text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            <?php echo e(data_get($product, 'name')); ?>

                                        </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($product, 'price') !== null): ?>
                                            <span class="text-white"><?php echo e(format_rp(data_get($product, 'price'))); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </details>
            </div>
        </div>

        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; <?php echo e(now()->year); ?> MERCATORIA</p>
                <p>Powered by MERCATORIA</p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var carousel = document.getElementById('hero-carousel');
            if (!carousel) return;
            var slides = carousel.querySelectorAll('[data-hero-slide]');
            var dots = carousel.querySelectorAll('[data-hero-dot]');
            if (slides.length < 2) return;
            var index = 0;
            var timer = null;

            function show(i) {
                index = (i + slides.length) % slides.length;
                slides.forEach(function (s, n) { s.classList.toggle('hidden', n !== index); });
                dots.forEach(function (d, n) {
                    d.classList.toggle('bg-white', n === index);
                    d.classList.toggle('bg-white/45', n !== index);
                });
            }
            function next() { show(index + 1); }
            function prev() { show(index - 1); }
            function start() { stop(); timer = setInterval(next, 5000); }
            function stop() { if (timer) clearInterval(timer); timer = null; }

            var prevBtn = document.getElementById('hero-prev');
            var nextBtn = document.getElementById('hero-next');
            if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
            dots.forEach(function (d) {
                d.addEventListener('click', function () {
                    show(parseInt(d.getAttribute('data-hero-dot'), 10) || 0);
                    start();
                });
            });

            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);

            var touchX = null;
            carousel.addEventListener('touchstart', function (e) {
                touchX = e.changedTouches[0].clientX;
                stop();
            }, { passive: true });
            carousel.addEventListener('touchend', function (e) {
                if (touchX === null) return;
                var dx = e.changedTouches[0].clientX - touchX;
                if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); }
                touchX = null;
                start();
            }, { passive: true });

            show(0);
            start();
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/home/index.blade.php ENDPATH**/ ?>