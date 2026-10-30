<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Office'); ?> — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-[#F8F9FA] font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        <aside class="w-60 shrink-0 bg-[#1E1E2D] text-white">
            <div class="border-b border-white/10 px-4 py-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-white/45">Back-Office</p>
                <p class="mt-1 font-display text-lg font-semibold tracking-wide text-white">MERCATORIA</p>
            </div>
            <nav class="space-y-0.5 px-3 py-3 text-[13px]">
                <a href="<?php echo e(route('office.dashboard')); ?>" class="block rounded px-3 py-2 text-white transition-colors hover:bg-white/10">Dashboard</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Log</p>
                <a href="<?php echo e(route('office.activity-logs')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Activity Log</a>
                <a href="<?php echo e(route('office.auth-logs')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Auth Log</a>
                <a href="<?php echo e(route('office.admin-logs')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Admin Log</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Produk</p>
                <a href="<?php echo e(route('office.products.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Semua Produk</a>
                <a href="<?php echo e(route('office.merch-categories.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Kategori Merch</a>
                <a href="<?php echo e(route('office.tags.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Tag</a>
                <a href="<?php echo e(route('office.stores.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Stores</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Katalog</p>
                <a href="<?php echo e(route('office.games.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Game</a>
                <a href="<?php echo e(route('office.developers.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Developer</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Konten</p>
                <a href="<?php echo e(route('office.hero-slides.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Hero Slides</a>
                <a href="<?php echo e(route('office.pages.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Halaman CMS</a>
                <a href="<?php echo e(route('office.vouchers.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Vouchers</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Transaksi</p>
                <a href="<?php echo e(route('office.orders.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Orders</a>
                <a href="<?php echo e(route('office.payments.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Verifikasi Pembayaran</a>
                <a href="<?php echo e(route('office.refunds.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Refund</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Pengguna</p>
                <a href="<?php echo e(route('office.users.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Users</a>

                <p class="px-3 pt-4 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">Lainnya</p>
                <a href="<?php echo e(route('office.reports.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Laporan</a>
                <a href="<?php echo e(route('office.payment-methods.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Metode Bayar</a>
                <a href="<?php echo e(route('office.email-templates.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Template Email</a>
                <a href="<?php echo e(route('office.spammers.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Spammers</a>
                <a href="<?php echo e(route('office.settings.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Settings</a>
                <a href="<?php echo e(route('office.seo.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">SEO</a>
                <a href="<?php echo e(route('office.marketing.index')); ?>" class="block rounded px-3 py-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">Marketing</a>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-14 items-center justify-between gap-4 border-b border-[#EAEAEA] bg-white px-6">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-[11px] uppercase tracking-[0.16em] text-ink/55"><?php echo $__env->yieldContent('title', 'Office'); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('office.search')): ?>
                        <div class="relative" id="admin-search">
                            <input type="text" id="admin-search-input" placeholder="Cari Produk/Order/User..."
                                   class="w-56 rounded-lg border border-rule bg-white px-3 py-1.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                            <div id="admin-search-results" class="absolute left-0 top-full z-30 mt-1 hidden w-72 rounded-lg border border-rule bg-white shadow-sm"></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('office.notifications.read-all')): ?>
                        <details class="relative" id="admin-bell">
                            <summary class="flex cursor-pointer list-none items-center gap-1 text-ink/70 transition-colors hover:text-ink" aria-label="Notifikasi">
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($adminBellUnreadCount) && $adminBellUnreadCount > 0): ?>
                                    <span class="flex h-4 min-w-4 items-center justify-center rounded-full bg-[#E53935] px-1 text-[9px] font-semibold text-white"><?php echo e($adminBellUnreadCount > 99 ? '99+' : $adminBellUnreadCount); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </summary>
                            <div class="absolute right-0 top-full z-30 mt-1 w-80 rounded-lg border border-rule bg-white shadow-sm">
                                <div class="flex items-center justify-between border-b border-rule px-3 py-2">
                                    <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Notifikasi</p>
                                    <form method="POST" action="<?php echo e(route('office.notifications.read-all')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="text-xs text-link underline decoration-link/35 underline-offset-4">Tandai semua dibaca</button>
                                    </form>
                                </div>
                                <ul class="max-h-72 overflow-y-auto">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $adminBellNotifications ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li class="border-b border-rule/50 px-3 py-2 text-sm <?php echo e(($notif->is_read ?? false) ? 'opacity-60' : ''); ?>">
                                            <p class="<?php echo e(($notif->is_read ?? false) ? '' : 'font-semibold'); ?> text-ink"><?php echo e($notif->title ?? $notif->message ?? '-'); ?></p>
                                            <p class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45"><?php echo e(isset($notif->created_at) ? $notif->created_at->diffForHumans() : ''); ?></p>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <li class="px-4 py-6 text-center text-sm text-ink/45">Belum ada notifikasi.</li>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </ul>
                            </div>
                        </details>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="/" class="font-medium text-link underline decoration-link/35 underline-offset-4 transition-colors hover:text-ink hover:decoration-ink">Lihat Frontend</a>
                    <span class="text-ink/75"><?php echo e(auth()->user()->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg border border-rule px-3 py-1 text-xs font-semibold text-ink transition-colors hover:border-ink">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                    <div class="mb-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('office.search')): ?>
    <script>
        (function () {
            var input = document.getElementById('admin-search-input');
            var box = document.getElementById('admin-search-results');
            if (!input || !box) return;
            var t;
            input.addEventListener('input', function () {
                clearTimeout(t);
                var q = input.value.trim();
                if (q.length < 2) { box.classList.add('hidden'); box.innerHTML = ''; return; }
                t = setTimeout(function () {
                    fetch('<?php echo e(route("office.search")); ?>?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) {
                            var html = '';
                            function sec(label, items, render) {
                                if (!items || !items.length) return;
                                html += '<p class="px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">' + label + '</p>';
                                items.forEach(function (x) { html += render(x); });
                            }
                            sec('Produk', d.products, function (x) {
                                return '<a href="/office/products/' + x.id + '/edit" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.name || '') + '</a>';
                            });
                            sec('Orders', d.orders, function (x) {
                                return '<a href="/office/orders/' + (x.no_order || x.id) + '" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.no_order || '') + '</a>';
                            });
                            sec('Users', d.users, function (x) {
                                return '<a href="/office/users/' + x.id + '/edit" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.name || x.email || '') + '</a>';
                            });
                            if (!html) html = '<p class="px-3 py-4 text-center text-sm text-ink/45">Tidak ada hasil.</p>';
                            box.innerHTML = html;
                            box.classList.remove('hidden');
                        })
                        .catch(function () { box.classList.add('hidden'); });
                }, 250);
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#admin-search')) box.classList.add('hidden');
            });
        })();
    </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/layouts/admin.blade.php ENDPATH**/ ?>