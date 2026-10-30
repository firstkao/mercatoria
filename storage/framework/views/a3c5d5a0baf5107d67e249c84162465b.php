<?php $__env->startSection('title', 'Undang Teman — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Undang Teman</h1>
    <p class="mt-1 text-sm text-ink/65">Ajak teman berbelanja dan dapatkan koin.</p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="<?php echo e(route('dashboard')); ?>" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Pesanan Saya</a>
        <a href="<?php echo e(route('profile.notifications')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="<?php echo e(route('profile.coins')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="<?php echo e(route('profile.wallet')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="<?php echo e(route('profile.referral')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="<?php echo e(route('profile.edit')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-6">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">KODE UNDANGANMU</p>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-[0.28em] text-ink"><?php echo e($user->referral_code); ?></p>
        <p class="mt-2 text-xs text-ink/50" id="referral-link">
            Link: <?php echo e(url('/register?ref='.$user->referral_code)); ?>

        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" data-copy="<?php echo e($user->referral_code); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                <?php if (isset($component)) { $__componentOriginalc22673952a0371058898d78abddef343 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc22673952a0371058898d78abddef343 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.clipboard','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.clipboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc22673952a0371058898d78abddef343)): ?>
<?php $attributes = $__attributesOriginalc22673952a0371058898d78abddef343; ?>
<?php unset($__attributesOriginalc22673952a0371058898d78abddef343); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc22673952a0371058898d78abddef343)): ?>
<?php $component = $__componentOriginalc22673952a0371058898d78abddef343; ?>
<?php unset($__componentOriginalc22673952a0371058898d78abddef343); ?>
<?php endif; ?>
                Copy Kode
            </button>
            <button type="button" data-copy="<?php echo e(url('/register?ref='.$user->referral_code)); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                <?php if (isset($component)) { $__componentOriginal238429d2301c052c543df7063ba8941d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal238429d2301c052c543df7063ba8941d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.link','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal238429d2301c052c543df7063ba8941d)): ?>
<?php $attributes = $__attributesOriginal238429d2301c052c543df7063ba8941d; ?>
<?php unset($__attributesOriginal238429d2301c052c543df7063ba8941d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal238429d2301c052c543df7063ba8941d)): ?>
<?php $component = $__componentOriginal238429d2301c052c543df7063ba8941d; ?>
<?php unset($__componentOriginal238429d2301c052c543df7063ba8941d); ?>
<?php endif; ?>
                Copy Link Undangan
            </button>
        </div>
    </div>

    
    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">UNDANG TEMAN</p>
            <p class="mt-1 text-lg font-semibold text-ink">+3.000 Koin</p>
            <p class="mt-1 text-xs text-ink/60">Saat temanmu menyelesaikan order pertama. Teman mendapat +1.000 koin saat pembayaran pertama terverifikasi.</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">SHARE PRODUK</p>
            <p class="mt-1 text-lg font-semibold text-ink">+5 Koin / Klik</p>
            <p class="mt-1 text-xs text-ink/60">Per IP unik Indonesia saat membuka link produk yang kamu bagikan.</p>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($shareStats)): ?>
        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-3">
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">LINK DIBUKA</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($shareStats['opens'] ?? 0); ?></p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">KOIN DARI SHARE</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e(number_format($shareStats['coins'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">REWARD</p>
                <p class="mt-1 text-2xl font-semibold text-ink">5 / klik</p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($referralStats)): ?>
        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Total Diundang</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($referralStats['total_diundang'] ?? 0); ?></p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Berhasil</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($referralStats['berhasil'] ?? 0); ?></p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Menunggu Order</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e($referralStats['menunggu_order'] ?? 0); ?></p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Koin</p>
                <p class="mt-1 text-2xl font-semibold text-ink"><?php echo e(number_format($referralStats['koin_didapat'] ?? 0, 0, ',', '.')); ?></p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mt-5 rounded-xl border border-rule bg-white p-4">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Undangan</h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($referrals->isEmpty()): ?>
            <p class="mt-2 text-sm text-ink/45">Belum ada yang bergabung lewat kode kamu.</p>
        <?php else: ?>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-rule text-left">
                        <tr>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nama / Email</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tgl Daftar</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Koin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $hasOrder = $referral->orders()->exists();
                                $koin = $hasOrder ? 3000 : 0;
                            ?>
                            <tr class="border-b border-rule/50">
                                <td class="py-2 text-ink/80"><?php echo e($referral->name); ?></td>
                                <td class="py-2 font-mono text-xs text-ink/55"><?php echo e($referral->created_at->format('d/m/Y')); ?></td>
                                <td class="py-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasOrder): ?>
                                        <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-[#43A047] text-white">Order Pertama Lunas</span>
                                    <?php else: ?>
                                        <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800">Menunggu Order</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="py-2 text-ink/80">+<?php echo e(number_format($koin)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = btn.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value);
                    }
                });
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/profile/referral.blade.php ENDPATH**/ ?>