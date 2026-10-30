<?php $__env->startSection('title', 'Koin Saya — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Koin Saya</h1>
    <p class="mt-1 text-sm text-ink/65">1 Koin = Rp1. Bisa dipakai maksimal 5% dari total pesanan.</p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="<?php echo e(route('dashboard')); ?>" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Pesanan Saya</a>
        <a href="<?php echo e(route('profile.notifications')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="<?php echo e(route('profile.coins')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="<?php echo e(route('profile.wallet')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="<?php echo e(route('profile.referral')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="<?php echo e(route('profile.edit')); ?>" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-6 text-center">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">TOTAL KOIN AKTIF</p>
        <p class="mt-2 font-display text-5xl font-semibold text-ink"><?php echo e(number_format($balance, 0, ',', '.')); ?></p>
    </div>

    
    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule" role="tablist">
        <button type="button" data-coin-tab="aktif" class="coin-tab border-b-2 border-ink px-4 py-2 text-sm font-semibold text-ink">Aktif</button>
        <button type="button" data-coin-tab="expiring" class="coin-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Akan Expired</button>
        <button type="button" data-coin-tab="riwayat" class="coin-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Riwayat</button>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-4">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Koin</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tanggal</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Sumber</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Awal</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Sisa</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Kedaluwarsa</th>
                        <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isExpired = $tx->expired_at && $tx->expired_at->isPast();
                            $isExpiring = $tx->expired_at && ! $tx->expired_at->isPast() && $tx->expired_at->diffInDays(now()) <= 30;
                            $statusClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'aktif');
                        ?>
                        <tr class="border-b border-rule/50" data-coin-row="<?php echo e($statusClass); ?>">
                            <td class="py-2 text-ink/75"><?php echo e($tx->created_at->format('d/m/Y H:i')); ?></td>
                            <td class="py-2 text-ink/80"><?php echo e($tx->type); ?></td>
                            <td class="py-2 <?php echo e($tx->amount >= 0 ? 'text-[#43A047]' : 'text-danger'); ?>">
                                <?php echo e($tx->amount >= 0 ? '+' : ''); ?><?php echo e(number_format($tx->amount, 0, ',', '.')); ?>

                            </td>
                            <td class="py-2 text-ink/80"><?php echo e(number_format($tx->balance_after, 0, ',', '.')); ?></td>
                            <td class="py-2 text-ink/60"><?php echo e($tx->expired_at?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="py-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isExpired): ?>
                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold text-white" style="background:#E53935">HABIS</span>
                                <?php else: ?>
                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold text-white" style="background:#43A047">AKTIF</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="py-6 text-center text-ink/45">Belum ada transaksi koin.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.coin-tab');
            var rows = document.querySelectorAll('[data-coin-row]');
            function show(mode) {
                rows.forEach(function (row) {
                    var kind = row.getAttribute('data-coin-row');
                    var visible = mode === 'riwayat'
                        || (mode === 'aktif' && kind !== 'expired')
                        || (mode === 'expiring' && kind === 'expiring');
                    row.style.display = visible ? '' : 'none';
                });
            }
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                        t.classList.add('border-transparent', 'text-ink/55');
                    });
                    tab.classList.remove('border-transparent', 'text-ink/55');
                    tab.classList.add('border-ink', 'font-semibold', 'text-ink');
                    show(tab.getAttribute('data-coin-tab'));
                });
            });
            show('aktif');
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/profile/coins.blade.php ENDPATH**/ ?>