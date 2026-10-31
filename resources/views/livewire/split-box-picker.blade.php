<div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <h2 class="text-sm font-semibold">Pilih Karakter (Split Box)</h2>
    <p class="mt-1 text-xs text-gray-500">Slot terkunci setelah bukti bayar diunggah — bisa diambil orang lain sebelum itu.</p>

    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($slots as $slot)
            <div class="rounded-lg border p-3 transition-colors
                        {{ ($selected[$slot['id']] ?? 0) > 0 ? 'border-black bg-gray-50' : 'border-gray-200' }}
                        {{ $slot['available'] <= 0 ? 'opacity-50' : '' }}"
                 wire:key="slot-card-{{ $slot['id'] }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">{{ $slot['name'] }}</p>
                    @if ($slot['role'] === 'hot_1')
                        <span class="rounded bg-orange-100 px-1.5 py-0.5 text-[10px] text-orange-700">Wajib Pair +1</span>
                    @elseif ($slot['role'] === 'hot_2')
                        <span class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] text-red-700">Wajib Pair +2</span>
                    @else
                        <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] text-emerald-700">Normal</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-400">Sisa: {{ $slot['available'] }}</p>

                <div class="mt-2 flex items-center gap-2">
                    {{-- [FIX #1] Tombol - disabled saat qty 0 --}}
                    <button type="button" wire:click="decrement({{ $slot['id'] }})"
                            class="h-7 w-7 rounded border border-gray-200 text-sm transition-colors hover:border-gray-400 disabled:opacity-30 disabled:cursor-not-allowed"
                            @if (($selected[$slot['id']] ?? 0) <= 0) disabled @endif>
                        &minus;
                    </button>

                    <span class="w-6 text-center text-sm font-medium">{{ $selected[$slot['id']] ?? 0 }}</span>

                    <button type="button" wire:click="increment({{ $slot['id'] }})"
                            class="h-7 w-7 rounded border border-gray-200 text-sm transition-colors hover:border-gray-400 disabled:opacity-30 disabled:cursor-not-allowed"
                            @if ($slot['available'] <= 0) disabled @endif>
                        +
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($message)
        <p class="mt-3 rounded px-3 py-2 text-sm {{ str_contains($message, 'berhasil') || str_contains($message, 'ditambahkan') ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-800' }}">
            {{ $message }}
        </p>
    @endif

    {{-- [FIX #2] Disabled kalau tidak ada yang dipilih --}}
    @php($totalSelected = collect($selected)->sum())
    <button type="button" wire:click="addToCart"
            wire:loading.attr="disabled"
            class="mt-4 w-full rounded-lg bg-black py-2.5 text-sm font-semibold text-white transition-colors hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed"
            @if ($totalSelected <= 0) disabled @endif>
        <span wire:loading.remove wire:target="addToCart">
            TAMBAH PAKET SPLIT BOX KE KERANJANG
            @if ($totalSelected > 0)
                ({{ $totalSelected }} slot)
            @endif
        </span>
        <span wire:loading wire:target="addToCart">Menambahkan...</span>
    </button>

    @if ($totalSelected <= 0)
        <p class="mt-1.5 text-center text-xs text-gray-400">Pilih minimal 1 karakter untuk melanjutkan.</p>
    @endif
</div>