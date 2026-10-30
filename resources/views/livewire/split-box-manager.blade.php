<div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <h2 class="text-sm font-semibold">Split Box &mdash; Slot &amp; Kloter</h2>

    @if ($statusMessage)
        <p class="mt-2 rounded bg-green-50 px-3 py-2 text-sm text-green-800">{{ $statusMessage }}</p>
    @endif

    @if (! $product->splitBox)
        <p class="mt-2 text-sm text-gray-500">Aktifkan <strong>is_split_box</strong> lalu simpan produk untuk mengelola slot.</p>
    @else
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
                        <td class="py-2">{{ $slot->variant?->name }}</td>
                        <td class="py-2">
                            <select wire:model.defer="slotRoles.{{ $slot->id }}" class="rounded border border-gray-200 px-2 py-1 text-xs">
                                @foreach (['normal', 'hot_1', 'hot_2'] as $role)
                                    <option value="{{ $role }}" @selected($slot->split_role === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="py-2">
                            <input type="number" wire:model.defer="slotQtys.{{ $slot->id }}" min="1"
                                   value="{{ $slot->qty_per_box }}" class="w-16 rounded border border-gray-200 px-2 py-1 text-xs">
                        </td>
                        <td class="py-2">{{ $slot->batchSlots->sum(fn ($bs) => max(0, $bs->qty_total - $bs->qty_taken)) }}</td>
                        <td class="py-2 text-right">
                            <button wire:click="saveSlot({{ $slot->id }}, '{{ $slot->split_role }}', {{ $slot->qty_per_box }})"
                                    class="text-xs text-sky-600 hover:underline">Simpan</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3 class="mt-4 text-sm font-semibold">Kloter</h3>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($product->splitBox->batches as $batch)
                <li class="flex items-center justify-between rounded border border-gray-100 px-3 py-2">
                    <span>
                        Kloter #{{ $batch->batch_no }} &mdash; {{ $batch->status }}
                        @if ($batch->is_auto_opened) (auto) @endif
                        @if ($batch->closes_at) · closes {{ $batch->closes_at->format('d/m/Y H:i') }} @endif
                    </span>
                    @if ($batch->status === 'open')
                        <button wire:click="closeRefund({{ $batch->id }})"
                                class="text-xs text-red-600 hover:underline"
                                onclick="return confirm('Tutup kloter & refund semua order terkunci?')">
                            Tutup Kloter &amp; Refund Semua
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>

        <button wire:click="openBatch" class="mt-3 rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">
            + Buka Kloter Baru
        </button>
    @endif
</div>
