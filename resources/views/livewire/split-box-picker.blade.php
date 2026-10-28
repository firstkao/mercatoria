<div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <h2 class="text-sm font-semibold">Pilih Karakter (Split Box)</h2>
    <p class="mt-1 text-xs text-gray-500">Slot terkunci setelah bukti bayar diunggah — bisa diambil orang lain sebelum itu.</p>

    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($slots as $slot)
            <div class="rounded-lg border border-gray-200 p-3 {{ $slot['available'] <= 0 ? 'opacity-50' : '' }}">
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
                    <button type="button" wire:click="decrement({{ $slot['id'] }})"
                            class="h-7 w-7 rounded border border-gray-200 text-sm">-</button>
                    <span class="w-6 text-center text-sm">{{ $selected[$slot['id']] ?? 0 }}</span>
                    <button type="button" wire:click="increment({{ $slot['id'] }})"
                            class="h-7 w-7 rounded border border-gray-200 text-sm"
                            @if($slot['available'] <= 0) disabled @endif>+</button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($message)
        <p class="mt-3 rounded bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ $message }}</p>
    @endif

    <button type="button" wire:click="addToCart"
            class="mt-4 w-full rounded-lg bg-black py-2.5 text-sm font-semibold text-white hover:bg-gray-800">
        TAMBAH PAKET SPLIT BOX KE KERANJANG
    </button>
</div>
