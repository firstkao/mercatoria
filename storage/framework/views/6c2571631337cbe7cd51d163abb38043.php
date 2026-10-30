<?php $__env->startSection('title', $page->title); ?>

<?php $__env->startSection('content'); ?>
    <article class="mx-auto max-w-3xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Halaman</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl"><?php echo e($page->title); ?></h1>
        <div class="mt-5 h-px bg-rule"></div>
        <div class="cms-prose mt-6 text-[15px] text-ink/85"><?php echo nl2br(e($page->content)); ?></div>
    </article>

    <style>
        .cms-prose { line-height: 1.75; }
        .cms-prose p { margin: 0 0 1em; }
        .cms-prose p:last-child { margin-bottom: 0; }
        .cms-prose h2 { margin: 1.75em 0 0.6em; font-family: 'Fraunces', Georgia, serif; font-size: 1.35rem; font-weight: 600; color: #141B22; line-height: 1.35; }
        .cms-prose h3 { margin: 1.4em 0 0.5em; font-family: 'Fraunces', Georgia, serif; font-size: 1.1rem; font-weight: 600; color: #141B22; line-height: 1.4; }
        .cms-prose ul, .cms-prose ol { margin: 0 0 1em; padding-left: 1.35em; }
        .cms-prose li { margin: 0.3em 0; }
        .cms-prose ul { list-style: disc; }
        .cms-prose ol { list-style: decimal; }
        .cms-prose a { color: #0C6FA6; text-decoration: underline; text-underline-offset: 3px; }
        .cms-prose strong { color: #141B22; font-weight: 600; }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/page/show.blade.php ENDPATH**/ ?>