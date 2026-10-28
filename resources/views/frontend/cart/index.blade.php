@extends('layouts.app')

@section('title', 'Keranjang — Mercatoria')

@section('content')
    <h1 class="text-2xl font-semibold">Keranjang</h1>

    @if ($items->isEmpty())
        <div class="mt-6 rounded-xl bg-white p-10 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-100">
            Keranjang kamu masih kosong.
            <a href="/" class="ml-1 font-medium text-black underline">Mulai belanja</a>
        </div>
    @else
        <form id="checkout-form" action="{{ route('checkout.show') }}" method="GET">
            <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100 text-left text-gray-500">
                        <tr>
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $pricing = app(\App\Services\Order\ShippingCalculatorService::class)
                                    ->calculateItemShipping($item->product, $item->variant);
                            @endphp
                            <tr class="border-b border-gray-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" checked
                                           form="checkout-form" class="rounded border-gray-300">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $item->product->name }}</p>
                                    @if ($item->variant)
                                        <p class="text-xs text-gray-500">Varian: {{ $item->variant->name }}</p>
                                    @endif
                                    <p class="text-[11px] text-gray-400">[{{ $item->product->game->name }}]</p>
                                </td>
                                <td class="px-4 py-3">{{ format_rp($pricing['final_price_bersih']) }}</td>
                                <td class="px-4 py-3">
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="999"
                                           form="qty-form-{{ $item->id }}"
                                           class="w-16 rounded border border-gray-200 px-2 py-1 text-sm">
                                    <button type="submit" form="qty-form-{{ $item->id }}"
                                            class="ml-1 rounded border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:border-black">
                                        Simpan
                                    </button>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ format_rp($pricing['final_price_bersih'] * $item->qty) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="submit" form="delete-form-{{ $item->id }}"
                                            class="text-xs text-red-500 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <label for="customer_note" class="text-sm font-medium">Catatan untuk Mercatoria</label>
                <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                          form="checkout-form"
                          placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                          class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"></textarea>
                <p class="mt-1 text-xs text-gray-400">Maksimal 500 karakter.</p>
            </div>

            <button type="submit" form="checkout-form"
                    class="mt-4 rounded-lg bg-black px-8 py-3 text-sm font-semibold text-white hover:bg-gray-800">
                CHECKOUT ITEM TERPILIH
            </button>
        </form>

        @foreach ($items as $item)
            <form id="qty-form-{{ $item->id }}" method="POST" action="{{ route('cart.update', $item) }}" class="hidden">
                @csrf @method('PATCH')
            </form>
            <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('cart.destroy', $item) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endif
@endsection
