@extends('layouts.app')

@section('title', 'Keranjang â€” Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Belanja</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Keranjang</h1>

    @if ($items->isEmpty())
        <div class="mt-6 rounded-xl border border-rule bg-white p-10 text-center text-sm text-ink/50">
            Keranjang kamu masih kosong.
            <a href="/" class="ml-1 font-medium text-link underline decoration-link/35 underline-offset-4">Mulai belanja</a>
        </div>
    @else
        <form id="checkout-form" action="{{ route('checkout.show') }}" method="GET">
            <div class="mt-6 overflow-hidden rounded-xl border border-rule bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-rule text-left">
                        <tr>
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Produk</th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                            <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $pricing = app(\App\Services\Order\ShippingCalculatorService::class)
                                    ->calculateItemShipping($item->product, $item->variant);
                            @endphp
                            <tr class="border-b border-rule/50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" checked
                                           form="checkout-form" class="rounded border-rule">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-ink">{{ $item->product->name }}</p>
                                    @if ($item->variant)
                                        <p class="text-xs text-ink/55">Varian: {{ $item->variant->name }}</p>
                                    @endif
                                    @if ($item->bundle_group_id)
                                        <p class="text-[11px] text-violet-500">Paket Split Box</p>
                                    @endif
                                    <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">[{{ $item->product->game->name }}]</p>
                                </td>
                                <td class="px-4 py-3 text-ink/80">{{ format_rp($pricing['final_price_bersih']) }}</td>
                                <td class="px-4 py-3">
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="999"
                                           form="qty-form-{{ $item->id }}"
                                           class="w-16 rounded border border-rule px-2 py-1 text-sm text-ink">
                                    <button type="submit" form="qty-form-{{ $item->id }}"
                                            class="ml-1 rounded border border-rule px-2 py-1 text-xs text-ink/70 transition-colors hover:border-ink">
                                        Simpan
                                    </button>
                                </td>
                                <td class="px-4 py-3 font-medium text-ink">
                                    {{ format_rp($pricing['final_price_bersih'] * $item->qty) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="submit" form="delete-form-{{ $item->id }}"
                                            class="text-xs text-danger hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-rule bg-white p-4">
                <label for="customer_note" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Catatan untuk Mercatoria</label>
                <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                          form="checkout-form"
                          placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                          class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45"></textarea>
                <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Maksimal 500 karakter.</p>
            </div>

            <button type="submit" form="checkout-form"
                    class="mt-4 rounded-lg bg-black px-8 py-3 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
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
