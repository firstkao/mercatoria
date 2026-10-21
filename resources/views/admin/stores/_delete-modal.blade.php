@php
    $gamesUsed = (int) ($usage['games'] ?? 0);
    $productsUsed = (int) ($usage['products'] ?? 0);
@endphp
<x-modal :name="$modalName" max-width="max-w-lg">
    <h2 class="text-xl font-bold text-danger-500"><span aria-hidden="true">⚠️ </span>Hapus Store?</h2>
    <hr class="mt-3 border-border">

    <p class="mt-4 text-sm font-medium">Store:</p>
    <div class="mt-2 flex items-center gap-3 rounded-lg border border-border bg-surface-2 p-3">
        <span class="text-2xl" aria-hidden="true">🏪</span>
        <div>
            <p class="font-semibold">{{ $store->name }}</p>
            <p class="text-sm text-text-muted">
                Min: {{ $store->hasMinBelanja() ? number_format((float) $store->min_belanja_rmb, 0, ',', '.').' RMB' : '— (gak ada min)' }}
                · Ongkir: {{ $store->isFreeShipping() ? 'Gratis' : number_format((float) $store->ongkir_rmb, 0, ',', '.').' RMB' }}
            </p>
        </div>
    </div>

    @if ($gamesUsed > 0 || $productsUsed > 0)
        <div class="mt-4 rounded-lg border border-warning-100 bg-warning-50 p-4 text-sm text-warning-700">
            <p class="font-semibold"><span aria-hidden="true">⚠️ </span>Store ini dipakai oleh:</p>
            <ul class="mt-2 list-inside list-disc">
                <li>{{ $gamesUsed }} game</li>
                <li>{{ $productsUsed }} produk</li>
            </ul>
            <p class="mt-3 font-semibold">Kalau dihapus:</p>
            <ul class="mt-1 list-inside list-disc">
                <li>Game default_store_id → NULL</li>
                <li>Produk store_id → NULL</li>
                <li>Ongkir lokal akan fallback ke game default</li>
                <li>Order lama TETAP UTUH (data dari snapshot)</li>
            </ul>
        </div>
    @else
        <div class="mt-4 rounded-lg border border-info-100 bg-info-50 p-4 text-sm text-info-700">
            <p><span aria-hidden="true">ℹ️ </span>Store ini belum dipakai produk manapun.</p>
            <p class="mt-2">Aman untuk dihapus.</p>
        </div>
    @endif

    <div class="mt-6 flex justify-end gap-3">
        <x-button type="button" variant="ghost" x-on:click="$dispatch('close-modal', '{{ $modalName }}')">Batal</x-button>
        <form method="POST" action="{{ route('office.stores.destroy', $store) }}">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">Hapus Store</x-button>
        </form>
    </div>
</x-modal>
