<?php $__env->startSection('title', 'Lupa Password - Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <div class="rounded-xl border border-rule bg-white px-6 py-8 shadow-[0_1px_0_rgba(20,27,34,0.04)] sm:px-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Lupa Kata Sandi?</h1>
        <p class="mt-2 text-sm text-ink/65">
            Masukkan email yang terdaftar, dan kami akan mengirimkan tautan untuk mengatur ulang kata sandimu.
        </p>

        <div class="mt-6 h-px bg-rule"></div>

        <form method="POST" action="<?php echo e(route('password.email')); ?>" class="mt-6 space-y-5">
            <?php echo csrf_field(); ?>

            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                <label for="url">URL</label>
                <input type="text" name="url" id="url" tabindex="-1" autocomplete="off">
                <label for="phone2">Phone</label>
                <input type="text" name="phone2" id="phone2" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label for="email" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    <span class="text-ink/40">01</span>
                    Email
                    <span class="text-danger">*</span>
                </label>
                <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required autofocus
                       placeholder="nama@email.com"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 transition-colors focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                KIRIM LINK RESET
            </button>

            <p class="pt-1 text-center text-sm text-ink/65">
                <a href="<?php echo e(route('login')); ?>" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Kembali ke halaman Masuk</a>
            </p>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>