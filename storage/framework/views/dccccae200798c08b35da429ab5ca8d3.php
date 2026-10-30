<?php $__env->startSection('title', 'Order '.$order->no_order); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink"><?php echo e($order->no_order); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_number): ?>
                <p class="mt-0.5 font-mono text-xs text-ink/55"><?php echo e($order->invoice_number); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p class="mt-1.5 text-sm text-ink/70">
                <?php echo e($order->user->name ?? '-'); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->user?->email): ?> · <?php echo e($order->user->email); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->user?->no_hp): ?> · <?php echo e($order->user->no_hp); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $order->status,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status),'size' => 'lg']); ?>
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
                <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50"><?php echo e(strtoupper($order->scheme)); ?> · <?php echo e(strtoupper($order->payment_method)); ?> · <?php echo e(ucfirst($order->platform)); ?></span>
            </div>
        </div>
        <a href="<?php echo e(route('orders.invoice', $order->no_order)); ?>" target="_blank"
           class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">
            Invoice PDF
        </a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-[1fr_340px]">
        
        <div class="space-y-4">
            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Item (snapshot)</h2>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-rule text-left">
                            <tr>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Item</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-rule/50">
                                    <td class="py-1.5">
                                        <p class="font-medium text-ink"><?php echo e($item->snapshot->product_name); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->snapshot->variant_name): ?>
                                            <p class="text-xs text-ink/55">Varian: <?php echo e($item->snapshot->variant_name); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->snapshot->store_name): ?>
                                            <p class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45">Store: <?php echo e($item->snapshot->store_name); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->product_id === null): ?>
                                            <span class="mt-1 inline-block rounded border border-rule px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-[0.12em] text-ink/45">Produk Asli Telah Dihapus</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="py-1.5 text-ink/80"><?php echo e($item->snapshot->qty); ?></td>
                                    <td class="py-1.5 text-ink/80"><?php echo e(format_rp($item->snapshot->final_price)); ?></td>
                                    <td class="py-1.5 text-ink/80"><?php echo e(format_rp($item->snapshot->subtotal)); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="pb-2">
                                        <details class="group rounded-lg border border-rule/60">
                                            <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">
                                                <span>Detail Ongkir</span>
                                                <span class="transition-transform group-open:rotate-180">▼</span>
                                            </summary>
                                            <div class="border-t border-rule/60 bg-[#F5F7FA] px-3 py-2">
                                                <dl class="space-y-0.5 font-mono text-[11px]">
                                                    <div class="flex justify-between"><dt class="text-ink/55">Berat</dt><dd class="text-ink"><?php echo e(number_format($item->snapshot->weight_gram ?? 0)); ?> gram</dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Store</dt><dd class="text-ink"><?php echo e($item->snapshot->store_name ?? '—'); ?></dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Ongkir Lokal CN</dt><dd class="text-ink"><?php echo e($item->snapshot->ongkir_lokal_cn_idr ? format_rp($item->snapshot->ongkir_lokal_cn_idr) : '—'); ?></dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Ongkir CN-BTM</dt><dd class="text-ink"><?php echo e($item->snapshot->ongkir_cn_btm_idr ? format_rp($item->snapshot->ongkir_cn_btm_idr) : '—'); ?></dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Ongkir BTM-JKT</dt><dd class="text-ink"><?php echo e($item->snapshot->ongkir_btm_jkt_idr ? format_rp($item->snapshot->ongkir_btm_jkt_idr) : '—'); ?></dd></div>
                                                    <div class="flex justify-between border-t border-rule/60 pt-1 font-semibold"><dt class="text-ink">Total Ongkir</dt><dd class="text-ink"><?php echo e($item->snapshot->ongkir_total_idr ? format_rp($item->snapshot->ongkir_total_idr) : '—'); ?></dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Harga Produk</dt><dd class="text-ink"><?php echo e($item->snapshot->base_price ? format_rp($item->snapshot->base_price) : '—'); ?></dd></div>
                                                    <div class="flex justify-between"><dt class="text-ink/55">Profit 11%</dt><dd class="text-ink/40">—</dd></div>
                                                    <div class="flex justify-between border-t border-rule/60 pt-1 font-semibold"><dt class="text-ink">Harga Tayang</dt><dd class="text-ink"><?php echo e(format_rp($item->snapshot->final_price)); ?></dd></div>
                                                </dl>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ringkasan</h2>
                <dl class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Subtotal</dt><dd class="text-ink"><?php echo e(format_rp($order->subtotal)); ?></dd></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $order->discount_amount > 0): ?>
                        <div class="flex justify-between border-b border-rule/50 py-1 text-danger">
                            <dt>Diskon <?php echo e($order->discount_type === 'coin' ? 'Koin' : 'Voucher'); ?></dt>
                            <dd>- <?php echo e(format_rp($order->discount_amount)); ?></dd>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Fee QRIS</dt><dd class="text-ink"><?php echo e(format_rp($order->fee_qris)); ?></dd></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->estimasi_co_marketplace): ?>
                        <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Estimasi CO Marketplace</dt><dd class="text-ink"><?php echo e(format_rp($order->estimasi_co_marketplace)); ?></dd></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex justify-between border-b border-rule/50 py-1 font-medium"><dt class="text-ink">Total</dt><dd class="text-ink"><?php echo e(format_rp($order->total)); ?></dd></div>
                    <div class="flex justify-between py-1 text-base font-semibold"><dt class="text-ink">Tagihan Web</dt><dd class="text-ink"><?php echo e(format_rp($order->tagihan_web)); ?></dd></div>
                </dl>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->customer_note): ?>
                    <p class="mt-3 rounded-lg bg-paper p-2.5 text-xs text-ink/70">Catatan pembeli: <?php echo e($order->customer_note); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pesan dari Admin</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->note_to_customer): ?>
                    <div class="mt-2 rounded-lg border border-rule bg-paper px-3 py-2">
                        <p class="text-sm text-ink/80"><?php echo e($order->note_to_customer); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->note_to_customer_updated_at): ?>
                            <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Diperbarui <?php echo e($order->note_to_customer_updated_at->format('d/m/Y H:i')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <form method="POST" action="<?php echo e(route('office.orders.note-to-customer', $order->no_order)); ?>" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <textarea name="note_to_customer" rows="3" maxlength="1000" required
                              placeholder="Pesan untuk customer (maks 1000 karakter)..."
                              class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink"><?php echo e(old('note_to_customer', $order->note_to_customer)); ?></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['note_to_customer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button class="mt-2 rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Kirim Pesan</button>
                </form>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Refund</h2>

                <?php ($refunds = \App\Models\Refund::where('order_id', $order->id)->latest()->get()); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refunds->isNotEmpty()): ?>
                    <div class="mt-2 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-lg border border-rule px-3 py-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55"><?php echo e(strtoupper($refund->status)); ?></span>
                                        <span class="ml-2 font-semibold text-ink"><?php echo e(format_rp($refund->amount)); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->status === 'requested'): ?>
                                        <div class="flex gap-2">
                                            <form method="POST" action="<?php echo e(route('office.refunds.complete', $refund)); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="rounded-lg bg-[#43A047] px-3 py-1 text-xs font-semibold text-white">Proses Refund</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('office.refunds.reject', $refund)); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="text" name="rejection_reason" placeholder="Alasan tolak" required maxlength="1000"
                                                       class="w-40 rounded border border-rule px-2 py-1 text-xs">
                                                <button class="rounded-lg border border-danger/40 px-3 py-1 text-xs font-semibold text-danger">Tolak</button>
                                            </form>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <p class="mt-1 text-xs text-ink/65">Alasan: <?php echo e($refund->reason); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->rejection_reason): ?>
                                    <p class="mt-0.5 text-xs text-danger">Ditolak: <?php echo e($refund->rejection_reason); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('office.orders.refund.full')): ?>
                        <form method="POST" action="<?php echo e(route('office.orders.refund.full', $order->no_order)); ?>" class="rounded-lg border border-rule p-3">
                            <?php echo csrf_field(); ?>
                            <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Ajukan Refund (Full)</p>
                            <textarea name="reason" rows="2" required maxlength="1000" placeholder="Alasan refund..."
                                      class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                            <button class="mt-2 rounded-lg bg-black px-4 py-1.5 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Ajukan</button>
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('office.orders.refund.partial')): ?>
                        <form method="POST" action="<?php echo e(route('office.orders.refund.partial', $order->no_order)); ?>" class="rounded-lg border border-rule p-3">
                            <?php echo csrf_field(); ?>
                            <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Ajukan Refund Partial</p>
                            <input type="number" step="0.01" name="amount" min="0.01" required placeholder="Nominal"
                                   class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                            <textarea name="reason" rows="2" required maxlength="1000" placeholder="Alasan refund..."
                                      class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                            <button class="mt-2 rounded-lg bg-black px-4 py-1.5 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Ajukan</button>
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pembayaran</h2>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-rule text-left">
                            <tr>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tipe</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nominal</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Bukti</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-rule/50">
                                    <td class="py-1.5 text-ink/80"><?php echo e($payment->type); ?></td>
                                    <td class="py-1.5 text-ink/80"><?php echo e(format_rp($payment->amount)); ?></td>
                                    <td class="py-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->proof_path ?? $payment->file_path ?? null): ?>
                                            <a href="<?php echo e(\Illuminate\Support\Facades\Storage::url($payment->proof_path ?? $payment->file_path)); ?>" target="_blank" class="text-link underline decoration-link/35 underline-offset-4">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-ink/40">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="py-1.5"><?php if (isset($component)) { $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-status-badge','data' => ['status' => $payment->status === 'verified' ? 'selesai' : ($payment->status === 'rejected' ? 'pembayaran_gagal' : 'menunggu_pembayaran')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($payment->status === 'verified' ? 'selesai' : ($payment->status === 'rejected' ? 'pembayaran_gagal' : 'menunggu_pembayaran'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $attributes = $__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__attributesOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5)): ?>
<?php $component = $__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5; ?>
<?php unset($__componentOriginalf1c27fc0f5533a4a12499a75bd1c2dc5); ?>
<?php endif; ?></td>
                                    <td class="py-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->status === 'pending'): ?>
                                            <form method="POST" action="<?php echo e(route('office.payments.verify', [$order->no_order, $payment->id])); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="text-[#43A047] hover:underline">Verifikasi</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('office.payments.reject', [$order->no_order, $payment->id])); ?>" class="ml-2 inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="text-danger hover:underline">Tolak</button>
                                            </form>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="py-4 text-center text-ink/45">Belum ada pembayaran.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Status</h2>
                <ol class="mt-3 space-y-2 border-l border-rule pl-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="relative">
                            <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full <?php echo e($loop->last ? 'bg-ink' : 'bg-rule'); ?>"></span>
                            <p class="text-sm <?php echo e($loop->last ? 'font-semibold text-ink' : 'text-ink/75'); ?>"><?php echo e(order_status_label($log->status)); ?></p>
                            <p class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45">
                                <?php echo e($log->created_at->format('d/m/Y H:i')); ?> · oleh <?php echo e($log->changed_by); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->note): ?> — <?php echo e($log->note); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ol>
            </div>
        </div>

        
        <div class="space-y-4">
            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Info User</h2>
                <p class="mt-2 text-sm font-medium text-ink"><?php echo e($order->user->name ?? '-'); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->user?->email): ?>
                    <p class="text-xs text-ink/60"><?php echo e($order->user->email); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->user?->no_hp): ?>
                    <p class="text-xs text-ink/60"><?php echo e($order->user->no_hp); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->user): ?>
                    <p class="mt-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                        Order Count: <?php echo e($order->user->orders()->count()); ?>x
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($visit)): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'source') || data_get($visit, 'landing_page')): ?>
                        <div class="mt-3 border-t border-rule/50 pt-2">
                            <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Sumber Referral</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'source')): ?>
                                <p class="mt-1 text-xs text-ink/70"><?php echo e(data_get($visit, 'source')); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($visit, 'landing_page')): ?>
                                <p class="text-xs text-ink/50"><?php echo e(data_get($visit, 'landing_page')); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ubah Status</h2>
                <form method="POST" action="<?php echo e(route('office.orders.status', $order->no_order)); ?>" class="mt-2 space-y-2">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <select name="status" class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['menunggu_pembayaran', 'ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen', 'menunggu_denda']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if($order->status === $status): echo 'selected'; endif; ?>><?php echo e(order_status_label($status)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <input type="text" name="note" placeholder="Alasan (opsional)" maxlength="255"
                           class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                    <button class="w-full rounded-lg bg-black py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan</button>
                </form>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Catat Pelunasan</h2>
                <form method="POST" action="<?php echo e(route('office.orders.settlements', $order->no_order)); ?>" class="mt-2 flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="number" step="0.01" name="amount" required placeholder="Nominal"
                           class="flex-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                    <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Catat</button>
                </form>
            </div>

            
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Denda &amp; Hangus</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'hangus'): ?>
                    <form method="POST" action="<?php echo e(route('office.orders.unlock-penalty', $order->no_order)); ?>" class="mt-2 flex gap-2">
                        <?php echo csrf_field(); ?>
                        <input type="number" name="penalty_amount" min="0" placeholder="Nominal denda (opsional)"
                               class="flex-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                        <button class="rounded-lg bg-black px-3 py-2 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Buka Kunci</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($order->status, ['hangus', 'menunggu_denda'], true)): ?>
                    <form method="POST" action="<?php echo e(route('office.orders.hangus-permanent', $order->no_order)); ?>" class="mt-2"
                          onsubmit="return confirm('Tetapkan Hangus Permanen? Tidak dapat dibuka lagi.')">
                        <?php echo csrf_field(); ?>
                        <button class="w-full rounded-lg border border-danger/40 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">
                            Hangus Permanen
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>