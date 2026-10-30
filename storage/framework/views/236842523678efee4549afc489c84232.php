<?php $__env->startSection('title', $product->name.' — Mercatoria'); ?>

<?php $__env->startSection('content'); ?>
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[48%_52%]">
        <div>
            <div class="overflow-hidden rounded-xl border border-rule bg-white">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($variant?->image_path) ?? $product->image_path): ?>
                    <img id="product-main-img" src="<?php echo e(Storage::url($variant?->image_path ?? $product->image_path)); ?>"
                         alt="<?php echo e($product->name); ?>" class="aspect-square w-full object-cover cursor-zoom-in"
                         onclick="document.getElementById('product-lightbox').showModal()">
                <?php else: ?>
                    <div class="flex aspect-square w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">
                        Tanpa Foto
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <dialog id="product-lightbox" class="m-0 h-full w-full max-h-none max-w-none bg-transparent p-0">
                <div class="fixed inset-0 flex items-center justify-center bg-black/85 p-4" onclick="if(event.target===this)this.closest('dialog').close()">
                    <button type="button" class="absolute right-4 top-4 text-white/80 transition-colors hover:text-white"
                            onclick="document.getElementById('product-lightbox').close()" aria-label="Tutup">
                        <?php if (isset($component)) { $__componentOriginal7c8cb3634876f8430f20291d6ef649c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c8cb3634876f8430f20291d6ef649c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chevron-up','data' => ['class' => 'rotate-45','width' => '24','height' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rotate-45','width' => '24','height' => '24']); ?>
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
                    <img id="lightbox-img" src="<?php echo e(Storage::url($variant?->image_path ?? $product->image_path)); ?>"
                         alt="<?php echo e($product->name); ?>" class="max-h-full max-w-full object-contain">
                </div>
            </dialog>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->description_image_path): ?>
                <div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
                    <img src="<?php echo e(Storage::url($product->description_image_path)); ?>" alt="Deskripsi <?php echo e($product->name); ?>" class="w-full">
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <nav class="flex flex-wrap items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">
                <a href="/" class="transition-colors hover:text-ink">Beranda</a>
                <span class="text-ink/35">/</span>
                <a href="/<?php echo e($game->slug); ?>" class="transition-colors hover:text-ink"><?php echo e($game->name); ?></a>
                <span class="text-ink/35">/</span>
                <span class="text-ink"><?php echo e($product->name); ?></span>
            </nav>

            <p class="mt-3 font-mono text-[10px] uppercase tracking-[0.16em] text-ink/50">[<?php echo e($game->name); ?>]</p>
            <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl"><?php echo e($product->name); ?></h1>

            <div class="mt-2 flex flex-wrap gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase text-white <?php echo e($tag->slug === 'presale' ? 'bg-[#FFC107] text-[#000000]' : ($tag->slug === 'limited' ? 'bg-[#E53935]' : ($tag->slug === 'new' ? 'bg-[#4FC3F7]' : 'bg-[#E53935]'))); ?>"><?php echo e($tag->name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase <?php echo e($product->availability === 'tersedia' ? 'bg-[#43A047] text-white' : 'bg-[#9E9E9E] text-white'); ?>">
                    <?php echo e($product->availability === 'tersedia' ? 'Tersedia' : 'Kosong'); ?>

                </span>
            </div>

            <div class="mt-4 rounded-xl border border-rule bg-white p-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->hasActiveDiscount()): ?>
                    <p class="text-sm text-ink/45 line-through"><?php echo e(format_rp($pricing['base_price_bersih'])); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-3xl font-semibold text-[#29B6F6]"><?php echo e(format_rp($pricing['final_price_bersih'])); ?></p>
                <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_split_box && $product->splitBox): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('split-box-picker', ['product' => $product]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3855057902-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php elseif($product->variants->isNotEmpty()): ?>
                <div class="mt-6">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pilih Varian</h2>
                    <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="/<?php echo e($game->slug); ?>/<?php echo e($product->slug); ?>?variant=<?php echo e($v->id); ?>"
                               class="rounded-lg border px-3 py-2 text-center text-sm transition-colors
                                      <?php echo e(($variant?->id ?? null) === $v->id
                                          ? 'border-ink bg-ink text-white'
                                          : 'border-rule bg-white text-ink/80 hover:border-ink'); ?>">
                                <?php echo e($v->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant): ?>
                            <a href="/<?php echo e($game->slug); ?>/<?php echo e($product->slug); ?>"
                               class="inline-flex items-center justify-center gap-1 rounded-lg border border-rule px-3 py-2 text-center text-sm text-ink/60 transition-colors hover:border-ink hover:text-ink">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                                Clear
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <dl class="mt-6 space-y-1 text-sm text-ink/70">
                <div class="flex justify-between border-b border-rule/50 py-1">
                    <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">SKU</dt>
                    <dd class="text-ink"><?php echo e($variant->sku ?? $product->sku); ?></dd>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pricing['store_name']): ?>
                    <div class="flex justify-between border-b border-rule/50 py-1">
                        <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Store</dt>
                        <dd class="text-ink"><?php echo e($pricing['store_name']); ?></dd>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex justify-between border-b border-rule/50 py-1">
                    <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Berat</dt>
                    <dd class="text-ink"><?php echo e(number_format($pricing['weight_gram'])); ?> gram</dd>
                </div>
            </dl>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($product->is_split_box && $product->splitBox)): ?>
                <form method="POST" action="<?php echo e(route('cart.store')); ?>" class="mt-6 flex flex-wrap items-end gap-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant): ?>
                        <input type="hidden" name="variant_id" value="<?php echo e($variant->id); ?>">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div>
                        <label for="qty" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Jumlah</label>
                        <input type="number" name="qty" id="qty" value="1" min="1" max="999"
                               class="mt-2 w-24 rounded-lg border border-rule bg-white px-3 py-2.5 text-center text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    </div>
                    <button type="submit"
                            class="rounded-lg bg-accent px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:opacity-90">
                        TAMBAH KE KERANJANG
                    </button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php ($shareUrl = request()->getSchemeAndHttpHost().'/'.$game->slug.'/'.$product->slug.(auth()->user() ? '?ref='.auth()->user()->referral_code : '')); ?>
            <div class="mt-5 rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Bagikan produk ini</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <p class="mt-1.5 text-sm text-ink/75">+5 koin per IP unik (khusus IP Indonesia) saat link dibuka.</p>
                    <p class="mt-2 break-all font-mono text-xs text-link"><?php echo e($shareUrl); ?></p>
                <?php else: ?>
                    <p class="mt-1.5 text-sm text-ink/70">
                        <a href="<?php echo e(route('login')); ?>" class="font-medium text-link underline decoration-link/35 underline-offset-4">Masuk</a>
                        untuk membagikan link referral dan mendapat koin.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" data-copy="<?php echo e($shareUrl); ?>"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
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
<?php endif; ?> Copy Link
                    </button>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode($shareUrl)); ?>" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <?php if (isset($component)) { $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.facebook','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.facebook'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $attributes = $__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__attributesOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b)): ?>
<?php $component = $__componentOriginal1c905e780cce2dce10f915dce1e1ac5b; ?>
<?php unset($__componentOriginal1c905e780cce2dce10f915dce1e1ac5b); ?>
<?php endif; ?> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode($shareUrl)); ?>&text=<?php echo e(urlencode($product->name)); ?>" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <?php if (isset($component)) { $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.twitter-x','data' => ['width' => '14','height' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.twitter-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '14','height' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $attributes = $__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__attributesOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e)): ?>
<?php $component = $__componentOriginal2b7d16630a566a5c36da1de2b626ff5e; ?>
<?php unset($__componentOriginal2b7d16630a566a5c36da1de2b626ff5e); ?>
<?php endif; ?> X
                    </a>
                    <a href="mailto:?subject=<?php echo e(urlencode($product->name)); ?>&body=<?php echo e(urlencode($shareUrl)); ?>"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg>
                        Email
                    </a>
                    <button type="button" data-bookmark="<?php echo e($shareUrl); ?>"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5z"/></svg>
                        Bookmark
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('protected-images.show')): ?>
        <script src="<?php echo e(asset('js/image-protection.js')); ?>" defer></script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
            document.querySelectorAll('[data-bookmark]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = btn.getAttribute('data-bookmark');
                    try {
                        var list = JSON.parse(localStorage.getItem('bookmarked_products') || '[]');
                        if (list.indexOf(url) === -1) list.push(url);
                        localStorage.setItem('bookmarked_products', JSON.stringify(list));
                        btn.textContent = 'Tersimpan';
                    } catch (e) {}
                });
            });
        })();
    </script>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($related->isNotEmpty()): ?>
        <section class="mt-12">
            <h2 class="font-display text-lg font-semibold text-ink">Produk Terkait</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-card','data' => ['product' => $relatedProduct,'shipping' => $shipping]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relatedProduct),'shipping' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shipping)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $attributes = $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $component = $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/frontend/product/show.blade.php ENDPATH**/ ?>