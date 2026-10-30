<?php $__env->startSection('title', 'Dashboard Pelanggan — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Dashboard Pelanggan</h1>
            <p class="mt-1 text-[15px] text-ink/80">Selamat datang, <?php echo e($user->name); ?></p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal022db797f26c11bb3e5147358069d1c6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal022db797f26c11bb3e5147358069d1c6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-badge','data' => ['value' => $stateBadge,'type' => 'state']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stateBadge),'type' => 'state']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal022db797f26c11bb3e5147358069d1c6)): ?>
<?php $attributes = $__attributesOriginal022db797f26c11bb3e5147358069d1c6; ?>
<?php unset($__attributesOriginal022db797f26c11bb3e5147358069d1c6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal022db797f26c11bb3e5147358069d1c6)): ?>
<?php $component = $__componentOriginal022db797f26c11bb3e5147358069d1c6; ?>
<?php unset($__componentOriginal022db797f26c11bb3e5147358069d1c6); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tierBadge): ?>
                    <?php if (isset($component)) { $__componentOriginal022db797f26c11bb3e5147358069d1c6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal022db797f26c11bb3e5147358069d1c6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-badge','data' => ['value' => $tierBadge,'type' => 'tier']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tierBadge),'type' => 'tier']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal022db797f26c11bb3e5147358069d1c6)): ?>
<?php $attributes = $__attributesOriginal022db797f26c11bb3e5147358069d1c6; ?>
<?php unset($__attributesOriginal022db797f26c11bb3e5147358069d1c6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal022db797f26c11bb3e5147358069d1c6)): ?>
<?php $component = $__componentOriginal022db797f26c11bb3e5147358069d1c6; ?>
<?php unset($__componentOriginal022db797f26c11bb3e5147358069d1c6); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">Terdaftar sejak: <?php echo e($user->created_at->format('d F Y')); ?></span>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($userState ?? '', ['crush', 'ex', 'ons'])): ?>
                <?php $kuotaHabis = ($kuotaViewUsed ?? 0) >= ($kuotaViewTotal ?? 10) ?>
                <div class="mt-3 flex flex-wrap items-center gap-3 rounded-lg border border-rule bg-white px-3 py-2">
                    <span class="font-mono text-[11px] uppercase tracking-[0.12em] text-ink/70">
                        Kuota View: <strong class="text-ink"><?php echo e($kuotaViewUsed ?? 0); ?>/<?php echo e($kuotaViewTotal ?? 10); ?></strong>
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($countdownState)): ?>
                        <span class="text-xs text-ink/60"><?php echo e($countdownState); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kuotaHabis && !empty($waUrl)): ?>
                        <a href="<?php echo e($waUrl); ?>" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-[#43A047] px-3.5 py-1.5 text-xs font-semibold text-white transition-colors hover:opacity-90">
                            <?php if (isset($component)) { $__componentOriginal734c205a8e07bc5e5255eaf8da6d2f7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal734c205a8e07bc5e5255eaf8da6d2f7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chat-bubble','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chat-bubble'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal734c205a8e07bc5e5255eaf8da6d2f7d)): ?>
<?php $attributes = $__attributesOriginal734c205a8e07bc5e5255eaf8da6d2f7d; ?>
<?php unset($__attributesOriginal734c205a8e07bc5e5255eaf8da6d2f7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal734c205a8e07bc5e5255eaf8da6d2f7d)): ?>
<?php $component = $__componentOriginal734c205a8e07bc5e5255eaf8da6d2f7d; ?>
<?php unset($__componentOriginal734c205a8e07bc5e5255eaf8da6d2f7d); ?>
<?php endif; ?>
                            WA
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tierProgress)): ?>
                <div class="mt-3 w-full max-w-md">
                    <div class="flex items-center justify-between font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
                        <span>Progress Tier</span>
                        <span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($tierProgress['current_tier']) && !empty($tierProgress['next_tier'])): ?>
                                <?php echo e($tierProgress['current_tier']); ?> → <?php echo e($tierProgress['next_tier']); ?>

                            <?php else: ?>
                                <?php echo e(format_rp($tierProgress['belanja'] ?? 0)); ?> / <?php echo e(format_rp($tierProgress['platinum'] ?? 0)); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-rule/60">
                        <div class="h-full rounded-full bg-amber-400" style="width: <?php echo e($tierProgress['progress_pct'] ?? 0); ?>%"></div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($tierProgress['belanja_sisa'] ?? null) !== null): ?>
                        <p class="mt-1 font-mono text-[10px] text-ink/45">Sisa <?php echo e(format_rp($tierProgress['belanja_sisa'])); ?> ke tier berikutnya</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                    Keluar
                </button>
            </form>
        </div>
    </div>

    
    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">TOTAL PESANAN</p>
            <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($totalOrders); ?></p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">TOTAL BELANJA</p>
            <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e(format_rp($totalBelanja)); ?></p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">KOIN AKTIF</p>
            <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e(number_format($coinBalance, 0, ',', '.')); ?></p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">UNDANG TEMAN</p>
            <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($undangBerhasil); ?> Berhasil</p>
        </div>
    </div>

    
    <div class="mt-6 flex flex-wrap gap-1 border-b border-rule">
        <a href="<?php echo e(route('dashboard')); ?>" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Pesanan Saya</a>
        <a href="<?php echo e(route('profile.notifications')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="<?php echo e(route('profile.coins')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="<?php echo e(route('profile.wallet')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="<?php echo e(route('profile.referral')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="<?php echo e(route('profile.edit')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                <?php if (isset($component)) { $__componentOriginal5ead194cf4080a31c19088dd2d29bb1d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ead194cf4080a31c19088dd2d29bb1d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.exclamation-circle','data' => ['width' => '14','height' => '14','class' => 'text-ink/60']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.exclamation-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14','class' => 'text-ink/60']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5ead194cf4080a31c19088dd2d29bb1d)): ?>
<?php $attributes = $__attributesOriginal5ead194cf4080a31c19088dd2d29bb1d; ?>
<?php unset($__attributesOriginal5ead194cf4080a31c19088dd2d29bb1d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5ead194cf4080a31c19088dd2d29bb1d)): ?>
<?php $component = $__componentOriginal5ead194cf4080a31c19088dd2d29bb1d; ?>
<?php unset($__componentOriginal5ead194cf4080a31c19088dd2d29bb1d); ?>
<?php endif; ?>
                TAGIHAN MENUNGGU (<?php echo e($tagihan->count()); ?>)
            </h2>
            <a href="<?php echo e(route('orders.index')); ?>" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat Semua</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tagihan->isEmpty()): ?>
            <div class="mt-3 rounded-xl border border-rule bg-white p-6 text-center text-sm text-ink/45">
                Tidak ada tagihan menunggu.
            </div>
        <?php else: ?>
            <div class="mt-3 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tagihan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rule bg-white p-4">
                        <div>
                            <p class="text-sm font-semibold text-ink"><?php echo e($order->no_order); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'menunggu_pembayaran'): ?>
                                <p class="text-xs text-ink/60"><?php echo e(format_rp($order->tagihan_web)); ?> · Menunggu Pembayaran</p>
                            <?php else: ?>
                                <p class="text-xs text-ink/60">
                                    Menunggu Pelunasan &amp; CO Marketplace
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->pickup_deadline_at): ?>
                                        <?php
                                            $sisa = now()->diff($order->pickup_deadline_at);
                                            $sisaText = sprintf('%02d Hari %02d Jam', (int) $sisa->days, (int) $sisa->h);
                                        ?>
                                        · <?php if (isset($component)) { $__componentOriginal0656bd305abc6f376ceab88970af3514 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0656bd305abc6f376ceab88970af3514 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.clock','data' => ['width' => '12','height' => '12','class' => 'inline align-[-2px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '12','height' => '12','class' => 'inline align-[-2px]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0656bd305abc6f376ceab88970af3514)): ?>
<?php $attributes = $__attributesOriginal0656bd305abc6f376ceab88970af3514; ?>
<?php unset($__attributesOriginal0656bd305abc6f376ceab88970af3514); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0656bd305abc6f376ceab88970af3514)): ?>
<?php $component = $__componentOriginal0656bd305abc6f376ceab88970af3514; ?>
<?php unset($__componentOriginal0656bd305abc6f376ceab88970af3514); ?>
<?php endif; ?>
                                        Sisa Waktu CO: <?php echo e($sisaText); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'menunggu_pembayaran'): ?>
                            <a href="<?php echo e(route('orders.show', $order->no_order)); ?>"
                               class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink">
                                Bayar &amp; Unggah Bukti
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('orders.show', $order->no_order)); ?>"
                               class="rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                                Lihat
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    
    <section class="mt-8">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">DAFTAR PESANAN SAYA</h2>
        <div class="mt-3 space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rule bg-white px-4 py-3">
                    <div class="text-sm">
                        <span class="font-semibold text-ink"><?php echo e($order->no_order); ?></span>
                        <span class="text-ink/50">· <?php echo e($order->created_at->format('d M Y')); ?> · <?php echo e(format_rp($order->total)); ?></span>
                        <?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $attributes = $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $component = $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
                    </div>
                    <a href="<?php echo e(route('orders.show', $order->no_order)); ?>" class="text-xs font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat</a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-xl border border-rule bg-white p-6 text-center text-sm text-ink/45">
                    Belum ada pesanan. <a href="/" class="font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($user, 'shouldSeeTour') && $user->shouldSeeTour()): ?>
        <dialog id="tour-modal" class="rounded-xl border border-rule bg-white p-0" style="margin:auto;max-width:560px;width:calc(100% - 2rem);">
            <div class="p-6 sm:p-8">
                <p class="flex items-center gap-2 font-display text-xl font-semibold text-ink">
                    <?php if (isset($component)) { $__componentOriginal4c316f3544fd858e5c71b5ef1159c026 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4c316f3544fd858e5c71b5ef1159c026 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.sparkles','data' => ['width' => '20','height' => '20','class' => 'text-accent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.sparkles'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '20','height' => '20','class' => 'text-accent']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4c316f3544fd858e5c71b5ef1159c026)): ?>
<?php $attributes = $__attributesOriginal4c316f3544fd858e5c71b5ef1159c026; ?>
<?php unset($__attributesOriginal4c316f3544fd858e5c71b5ef1159c026); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4c316f3544fd858e5c71b5ef1159c026)): ?>
<?php $component = $__componentOriginal4c316f3544fd858e5c71b5ef1159c026; ?>
<?php unset($__componentOriginal4c316f3544fd858e5c71b5ef1159c026); ?>
<?php endif; ?>
                    Baru di Mercatoria? Ini Cara Belanja di Mercatoria
                </p>
                <div class="mt-4 h-px bg-rule"></div>
                <ol class="mt-5 space-y-3 text-sm leading-relaxed text-ink/85">
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">1.</span>
                        <span><strong class="text-ink">Pilih Merch IP Favoritmu</strong> — Semua harga yang tertera adalah HARGA BERSIH (sudah termasuk ongkir CN-ID &amp; bea).</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">2.</span>
                        <span><strong class="text-ink">Pilih Skema Pembayaran</strong> — Bisa Full Payment (100%) atau Down Payment (DP 50%) via BCA, QRIS, atau ShopeePay.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">3.</span>
                        <span><strong class="text-ink">Pilih Platform Pengiriman Lokal</strong> — Toco atau Shopee.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">4.</span>
                        <span><strong class="text-ink">Pantau Status Real-time</strong> — Barang diproses dari WH China hingga sampai WH Indonesia (pelunasan &amp; ambil ≤ 7 hari).</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="font-mono text-[11px] text-ink/45">5.</span>
                        <span><strong class="text-ink">Kuota Akun Baru (Crush)</strong> — Kamu punya kuota melihat 10 detail produk. Order pertamamu langsung upgrade ke MVP!</span>
                    </li>
                </ol>
                <div class="mt-7 flex flex-wrap items-center justify-end gap-3">
                    <a href="<?php echo e(route('page.cara-belanja')); ?>" class="rounded-lg border border-rule px-5 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">
                        Baca Selengkapnya
                    </a>
                    <form method="POST" action="<?php echo e(route('profile.tour-seen')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90">
                            Mengerti
                        </button>
                    </form>
                </div>
            </div>
        </dialog>
        <script>
            (function () {
                var modal = document.getElementById('tour-modal');
                if (modal && typeof modal.showModal === 'function') modal.showModal();
            })();
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/profile/show.blade.php ENDPATH**/ ?>