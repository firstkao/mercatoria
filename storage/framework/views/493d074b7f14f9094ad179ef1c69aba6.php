<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['notifications', 'unreadCount']));

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

foreach (array_filter((['notifications', 'unreadCount']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<details class="notification-bell relative">
    <summary class="relative list-none cursor-pointer text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Notifikasi">
        <?php if (isset($component)) { $__componentOriginal4525404018e5b2e94f2d1e4dc53d4ad7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4525404018e5b2e94f2d1e4dc53d4ad7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.bell','data' => ['width' => '18','height' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.bell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '18','height' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4525404018e5b2e94f2d1e4dc53d4ad7)): ?>
<?php $attributes = $__attributesOriginal4525404018e5b2e94f2d1e4dc53d4ad7; ?>
<?php unset($__attributesOriginal4525404018e5b2e94f2d1e4dc53d4ad7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4525404018e5b2e94f2d1e4dc53d4ad7)): ?>
<?php $component = $__componentOriginal4525404018e5b2e94f2d1e4dc53d4ad7; ?>
<?php unset($__componentOriginal4525404018e5b2e94f2d1e4dc53d4ad7); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
            <span class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#E53935] px-1 text-[9px] font-semibold text-white"
                  data-unread="<?php echo e($unreadCount); ?>">
                <?php echo e($unreadCount > 99 ? '99+' : $unreadCount); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </summary>

    <div class="absolute right-0 top-full z-30 mt-2 w-[380px] max-w-[90vw] rounded-xl border border-gray-100 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <div>
                <p class="text-sm font-semibold">Notifikasi</p>
                <p class="text-xs text-gray-500"><?php echo e($unreadCount); ?> belum dibaca</p>
            </div>
            <form method="POST" action="<?php echo e(route('profile.notifications.read-all')); ?>">
                <?php echo csrf_field(); ?>
                <button class="text-xs text-[#4FC3F7] hover:underline">Tandai semua dibaca</button>
            </form>
        </div>

        <ul class="max-h-80 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li data-notification-item class="border-b border-gray-50 px-4 py-2.5 text-sm <?php echo e($notification->is_read ? 'text-gray-500' : 'font-medium'); ?>">
                    <a href="<?php echo e($notification->target_url ?? '#'); ?>" class="block hover:text-black">
                        <?php echo e($notification->title); ?>

                        <span class="block text-[11px] text-gray-400"><?php echo e($notification->created_at->diffForHumans()); ?></span>
                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="px-4 py-6 text-center text-sm text-ink/45">Belum ada notifikasi.</li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>

        <a href="<?php echo e(route('profile.notifications')); ?>" class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs text-gray-500 hover:text-black">
            Lihat Semua Notifikasi
        </a>
    </div>
</details>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/components/notification-bell.blade.php ENDPATH**/ ?>