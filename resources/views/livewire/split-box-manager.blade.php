<div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <h2 class="text-sm font-semibold">Split Box — Slot &amp; Kloter</h2>

    @if ($statusMessage)
        <p class="mt-2 rounded px-3 py-2 text-sm
            {{ $statusIsError ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800' }}">
            {{ $statusMessage }}
        </p>
    @endif

    @if (! $product->splitBox)
        <p class="mt-2 text-sm text-gray-500">Aktifkan <strong>is_split_box</strong> lalu simpan produk untuk mengelola slot.</p>
    @else

        {{-- ═══ SLOT TABLE ═══ --}}
        <table class="mt-3 w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="py-2">Karakter</th>
                    <th class="py-2">Role</th>
                    <th class="py-2">Qty/box</th>
                    <th class="py-2">Sisa stok</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->splitBox->slots as $slot)
                    <tr class="border-b border-gray-50" wire:key="slot-{{ $slot->id }}">
                        <td class="py-2">{{ $slot->variant?->name ?? '—' }}</td>
                        <td class="py-2">
                            <select wire:model="slotRoles.{{ $slot->id }}"
                                    class="rounded border border-gray-200 px-2 py-1 text-xs">
                                @foreach (['normal', 'hot_1', 'hot_2'] as $role)
                                    <option value="{{ $role }}">{{ \Illuminate\Support\Str::headline($role) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="py-2">
                            <input type="number" wire:model="slotQtys.{{ $slot->id }}" min="1"
                                   class="w-16 rounded border border-gray-200 px-2 py-1 text-xs">
                        </td>
                        <td class="py-2">
                            {{ $slot->batchSlots->filter(fn ($bs) => $bs->batch?->status === 'open')->sum(fn ($bs) => max(0, $bs->qty_total - $bs->qty_taken)) }}
                        </td>
                        <td class="py-2 text-right">
                            <button wire:click="saveSlot({{ $slot->id }})"
                                    wire:loading.attr="disabled"
                                    class="text-xs text-sky-600 hover:underline disabled:opacity-50">
                                <span wire:loading.remove wire:target="saveSlot({{ $slot->id }})">Simpan</span>
                                <span wire:loading wire:target="saveSlot({{ $slot->id }})">...</span>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($product->splitBox->slots->isEmpty())
            <p class="mt-2 text-sm text-gray-400">Belum ada slot. Tambahkan varian produk terlebih dahulu.</p>
        @endif

        {{-- ═══ KLOTER LIST ═══ --}}
        <h3 class="mt-4 text-sm font-semibold">Kloter</h3>

        @if ($product->splitBox->batches->isEmpty())
            <p class="mt-2 text-sm text-gray-400">Belum ada kloter.</p>
        @else
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($product->splitBox->batches as $batch)
                    <li class="flex items-center justify-between rounded border border-gray-100 px-3 py-2"
                        wire:key="batch-{{ $batch->id }}">
                        <span>
                            Kloter #{{ $batch->batch_no }} —
                            <span class="
                                @if ($batch->status === 'open') text-emerald-600
                                @elseif ($batch->status === 'full') text-blue-600
                                @elseif ($batch->status === 'closed') text-gray-500
                                @elseif ($batch->status === 'cancelled') text-red-600
                                @else text-red-500 @endif
                            ">{{ $batch->status }}</span>
                            @if ($batch->is_auto_opened)
                                <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-500">auto</span>
                            @endif
                            @if ($batch->status === 'open')
                                <input type="datetime-local" value="{{ $batch->closes_at?->format('Y-m-d\TH:i') }}"
                                       wire:change="setClosesAt({{ $batch->id }}, $event.target.value)"
                                       title="closes_at (opsional)"
                                       class="ml-2 rounded border border-gray-200 px-1.5 py-0.5 text-xs text-gray-600">
                            @elseif ($batch->closes_at)
                                <span class="ml-1 text-xs text-gray-400">· closes {{ $batch->closes_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </span>
                        @if ($batch->status === 'open')
                            <button wire:click="closeRefund({{ $batch->id }})"
                                    wire:confirm="Tutup kloter & refund semua order terkunci?"
                                    wire:loading.attr="disabled"
                                    class="text-xs text-red-600 hover:underline disabled:opacity-50">
                                <span wire:loading.remove wire:target="closeRefund({{ $batch->id }})">Tutup Kloter &amp; Refund Semua</span>
                                <span wire:loading wire:target="closeRefund({{ $batch->id }})">...</span>
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- ═══ OPEN BATCH BUTTON ═══ --}}
        <button wire:click="openBatch"
                wire:loading.attr="disabled"
                class="mt-3 rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black disabled:opacity-50">
            <span wire:loading.remove wire:target="openBatch">+ Buka Kloter Baru</span>
            <span wire:loading wire:target="openBatch">Membuka...</span>
        </button>

    @endif
</div>
