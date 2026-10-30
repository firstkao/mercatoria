<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Mercatoria'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body id="top" class="flex min-h-screen flex-col font-sans text-ink antialiased">

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
                    <a href="<?php echo e(route('home')); ?>" class="text-[14px] font-medium text-[#4FC3F7]">Beranda</a>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Game
                            <span class="text-[12px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerGames)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $footerGames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <a href="<?php echo e(data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#')); ?>" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">
                                        <?php echo e(data_get($game, 'name')); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </details>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Developer
                            <span class="text-[10px] transition-transform group-open:rotate-180">&#9662;</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerDevelopers)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $footerDevelopers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $developer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <a href="<?php echo e(data_get($developer, 'url') ?? (data_get($developer, 'slug') ? route('catalog.developer', data_get($developer, 'slug')) : '#')); ?>" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">
                                        <?php echo e(data_get($developer, 'name')); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </details>

                    <a href="#" class="text-[14px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
                </nav>

                <a href="<?php echo e(route('home')); ?>" class="flex flex-col items-center justify-center no-underline">
                    <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" style="height: 50px; width: auto;">
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

                    <div class="flex items-center gap-4 text-[#333]">
                        <a href="<?php echo e(setting('url_instagram', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Instagram">
                            <?php if (isset($component)) { $__componentOriginal1ea42232d0b13214e79b5e861644d3ac = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_facebook', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Facebook">
                            <?php if (isset($component)) { $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_x', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="X">
                            <?php if (isset($component)) { $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_threads', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="Threads">
                            <?php if (isset($component)) { $__componentOriginale608a3a4d58371f8d58a450f9ade4f85 = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_whatsapp', '#')); ?>" class="transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp">
                            <?php if (isset($component)) { $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $component; } ?>
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
<?php endif; ?>
                        </a>
                    </div>

                    <a href="<?php echo e(route('login')); ?>" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Masuk">
                        <?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
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
<?php endif; ?>
                    </a>
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
                        <a href="<?php echo e(route('search')); ?>" class="block px-4 py-2.5 text-[13px] text-[#333]">Cari</a>
                        <a href="#" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerGames)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerGames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#')); ?>" class="block px-4 py-2 text-[12px] text-[#333]">
                                        <?php echo e(data_get($game, 'name')); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerDevelopers)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerDevelopers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $developer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(data_get($developer, 'url') ?? (data_get($developer, 'slug') ? route('catalog.developer', data_get($developer, 'slug')) : '#')); ?>" class="block px-4 py-2 text-[12px] text-[#333]">
                                        <?php echo e(data_get($developer, 'name')); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </details>

                <a href="<?php echo e(route('home')); ?>" class="flex flex-col items-center no-underline">
                    <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" class="h-9 w-auto" width="1608" height="494">
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

    <main class="flex flex-1 items-start justify-center px-4 py-8 sm:py-10">
        <div class="<?php echo $__env->yieldContent('width', 'w-full max-w-md'); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div class="mb-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="mb-4 rounded-lg border border-danger/35 bg-white px-4 py-3 text-sm text-danger">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    
    <footer class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                
                <div class="flex flex-col items-center">
                    <a href="<?php echo e(route('home')); ?>" class="inline-flex flex-col items-center no-underline">
                        <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" class="h-10 w-auto" width="1608" height="494">
                    </a>

                    
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <a href="<?php echo e(setting('url_instagram', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Instagram">
                            <?php if (isset($component)) { $__componentOriginal1ea42232d0b13214e79b5e861644d3ac = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_facebook', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Facebook">
                            <?php if (isset($component)) { $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_x', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="X">
                            <?php if (isset($component)) { $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_threads', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Threads">
                            <?php if (isset($component)) { $__componentOriginale608a3a4d58371f8d58a450f9ade4f85 = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_whatsapp', '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp">
                            <?php if (isset($component)) { $__componentOriginal5c686fa3cdb117f240449b4ccf99b8c1 = $component; } ?>
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
<?php endif; ?>
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="<?php echo e(setting('url_toco', '#')); ?>" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            TOCO
                            <?php if (isset($component)) { $__componentOriginal37a3f047daccd28b87517bd215a12923 = $component; } ?>
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
<?php endif; ?>
                        </a>
                        <a href="<?php echo e(setting('url_shopee', '#')); ?>" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            SHOPEE
                            <?php if (isset($component)) { $__componentOriginal37a3f047daccd28b87517bd215a12923 = $component; } ?>
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
<?php endif; ?>
                        </a>
                    </div>
                </div>

                
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Game
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                            <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
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
<?php endif; ?>
                        </span>
                    </summary>
                    <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerGames)): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerGames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(data_get($game, 'url') ?? (data_get($game, 'slug') ? route('catalog.game', data_get($game, 'slug')) : '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                        <?php echo e(data_get($game, 'name')); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </details>

                
                <div class="space-y-8">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Developer
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
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
<?php endif; ?>
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerDevelopers)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerDevelopers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $developer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(data_get($developer, 'url') ?? (data_get($developer, 'slug') ? route('catalog.developer', data_get($developer, 'slug')) : '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            <?php echo e(data_get($developer, 'name')); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </details>

                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Jelajahi
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
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
<?php endif; ?>
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerPages)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $footerPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(data_get($page, 'url') ?? (data_get($page, 'slug') ? url(data_get($page, 'slug')) : '#')); ?>" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            <?php echo e(data_get($page, 'name')); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </details>
                </div>

                
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Produk Terpopuler
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                            <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
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
<?php endif; ?>
                        </span>
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
                <div class="flex items-center gap-4">
                    <p>Powered by MERCATORIA</p>
                    <a href="#top" class="inline-flex h-7 w-7 items-center justify-center rounded border border-white/15 text-[#B0B3C5] transition-colors hover:border-white/35 hover:text-white" aria-label="Kembali ke atas">
                        <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['width' => '12','height' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '12','height' => '12']); ?>
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
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/layouts/guest.blade.php ENDPATH**/ ?>