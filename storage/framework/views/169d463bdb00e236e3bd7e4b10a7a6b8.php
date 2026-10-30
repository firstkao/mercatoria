<?php $__env->startSection('title', 'Verifikasi Email — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <div class="rounded-xl border border-rule bg-white px-6 py-8 shadow-[0_1px_0_rgba(20,27,34,0.04)] sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Verifikasi Email Kamu</h1>
        <p class="mt-2 text-sm text-ink/65">
            Kami sudah mengirim link verifikasi ke <strong class="text-ink"><?php echo e(auth()->user()->email); ?></strong>.
            Link hanya berlaku selama 24 jam sejak pendaftaran.
        </p>

        <div class="mt-6 h-px bg-rule"></div>

        <form method="POST" action="<?php echo e(route('verification.resend')); ?>" class="mt-6">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                KIRIM ULANG LINK VERIFIKASI
            </button>
        </form>

        <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-3">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="w-full rounded-lg border border-rule py-3.5 text-sm font-semibold text-ink transition-colors hover:border-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent">
                KELUAR
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>