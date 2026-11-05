@extends('layouts.app')

@section('title', 'Keranjang — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Keranjang — CSS biasa (tidak bergantung build Tailwind), selaras dengan au-* & hp-*/pp-*. */
        .cart-wrap, .cart-wrap *, .cart-wrap *::before, .cart-wrap *::after { box-sizing: border-box; }
        .cart-wrap { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .cart-wrap a { text-decoration: none; }

        .cart-notice {
            margin: 16px 0 0; padding: 14px 16px;
            background: #fff; border: 1px solid #e1e1e1; border-radius: 0;
            font-size: 13px; line-height: 20px; color: #e53935;
        }

        .cart-empty {
            margin: 24px 0 0; padding: 48px 24px;
            background: #fff; border: 1px solid #e1e1e1; border-radius: 0;
            text-align: center; font-size: 14px; line-height: 21px; color: #555;
        }
        .cart-empty .au-link { margin-left: 4px; }

        .cart-table-wrap { margin: 24px 0 0; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; overflow-x: auto; }
        .cart-table { width: 100%; border-collapse: collapse; background: #fff; font-size: 14px; line-height: 21px; }
        .cart-table th {
            padding: 12px 16px; border-bottom: 1px solid #e1e1e1; text-align: left;
            font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333;
        }
        .cart-table td { padding: 14px 16px; border-bottom: 1px solid #e1e1e1; vertical-align: top; color: #333; }
        .cart-table tbody tr:last-child td { border-bottom: 0; }
        .cart-col-check { width: 40px; }

        .cart-check { flex: none; width: 16px; height: 16px; margin: 2px 0 0; accent-color: #000; vertical-align: middle; }
        .cart-check-label {
            display: inline-flex; align-items: center; gap: 10px;
            font: inherit; letter-spacing: inherit; text-transform: inherit; color: inherit;
        }

        .cart-name { margin: 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #000; }
        .cart-variant { margin: 4px 0 0; font-size: 12px; line-height: 18px; color: #555; }
        .cart-bundle { margin: 4px 0 0; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #3986C6; }
        .cart-game { margin: 6px 0 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .12em; text-transform: uppercase; color: #888; }

        .cart-price { font-size: 14px; line-height: 21px; color: #02a1ea; white-space: nowrap; }
        .cart-price.is-sub { font-weight: 500; }

        .cart-qty {
            display: inline-block; width: 64px; height: 34px; margin: 0; padding: 0 10px;
            border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
            font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #000; outline: 0; transition: border-color .15s;
        }
        .cart-qty:focus { border-color: #000; }
        .cart-qty-static { font-size: 14px; line-height: 34px; color: #333; }
        .cart-save { margin-left: 8px; vertical-align: middle; }

        .cart-del {
            padding: 0; border: 0; background: none; cursor: pointer;
            font: 500 12px/18px 'Roboto', system-ui, sans-serif; color: #e53935;
        }
        .cart-del:hover { text-decoration: underline; }

        .cart-note { margin: 16px 0 0; padding: 20px; background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .cart-note .au-input { margin-top: 8px; }
        .cart-hint { margin: 8px 0 0; font-size: 11px; line-height: 16px; letter-spacing: .12em; text-transform: uppercase; color: #888; }

        .cart-checkout { display: inline-block; width: auto; margin-top: 16px; padding: 0 32px; }

        @media (max-width: 767px) {
            .cart-table th, .cart-table td { padding: 10px 12px; }
            .cart-checkout { width: 100%; padding: 0; }
        }
    </style>

    <div class="cart-wrap">
        <p class="au-eyebrow">Belanja</p>
        <h1 class="au-title">Keranjang</h1>

        @if (! empty($purgedNotice))
            <div class="cart-notice">{{ $purgedNotice }}</div>
        @endif

        @error('qty')
            <div class="cart-notice">{{ $message }}</div>
        @enderror

        @if ($items->isEmpty())
            <div class="cart-empty">
                Keranjang kamu masih kosong.
                <a href="/" class="au-link">Mulai belanja</a>
            </div>
        @else
            <form id="checkout-form" action="{{ route('checkout.show') }}" method="GET">
                <div class="cart-table-wrap">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th class="cart-col-check"></th>
                                <th>
                                    <label class="cart-check-label">
                                        <input type="checkbox" checked class="cart-check" onclick="document.querySelectorAll('input[name=&quot;items[]&quot;]').forEach(c=>c.checked=this.checked)">
                                        Pilih Semua
                                    </label>
                                </th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $pricing = app(\App\Services\Order\ShippingCalculatorService::class)
                                        ->calculateItemShipping($item->product, $item->variant);
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" name="items[]" value="{{ $item->id }}" checked
                                               form="checkout-form" class="cart-check"
                                               @if ($item->bundle_group_id) data-bundle="{{ $item->bundle_group_id }}"
                                               onchange="document.querySelectorAll('input[data-bundle=&quot;{{ $item->bundle_group_id }}&quot;]').forEach(c=>c.checked=this.checked)" @endif>
                                    </td>
                                    <td>
                                        <p class="cart-name">{{ $item->product->name }}</p>
                                        @if ($item->variant)
                                            <p class="cart-variant">Varian: {{ $item->variant->name }}</p>
                                        @endif
                                        @if ($item->bundle_group_id)
                                            <p class="cart-bundle">Paket Split Box</p>
                                        @endif
                                        <p class="cart-game">[{{ $item->product->game->name }}]</p>
                                    </td>
                                    <td class="cart-price">{{ format_rp($pricing['final_price_bersih']) }}</td>
                                    <td>
                                        @if ($item->bundle_group_id)
                                            <span class="cart-qty-static">{{ $item->qty }}</span>
                                        @else
                                            <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="999"
                                                   form="qty-form-{{ $item->id }}"
                                                   class="cart-qty">
                                            <button type="submit" form="qty-form-{{ $item->id }}"
                                                    class="au-btn is-ghost is-sm cart-save">
                                                Simpan
                                            </button>
                                        @endif
                                    </td>
                                    <td class="cart-price is-sub">
                                        {{ format_rp($pricing['final_price_bersih'] * $item->qty) }}
                                    </td>
                                    <td style="text-align:right">
                                        <button type="submit" form="delete-form-{{ $item->id }}"
                                                class="cart-del">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="cart-note">
                    <label for="customer_note" class="au-label">Catatan untuk Mercatoria</label>
                    <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                              form="checkout-form"
                              placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                              class="au-input"></textarea>
                    <p class="cart-hint">Maksimal 500 karakter.</p>
                </div>

                <button type="submit" form="checkout-form"
                        class="au-btn cart-checkout">
                    CHECKOUT ITEM TERPILIH
                </button>
            </form>

            @foreach ($items as $item)
                <form id="qty-form-{{ $item->id }}" method="POST" action="{{ route('cart.update', $item) }}" style="display:none">
                    @csrf @method('PATCH')
                </form>
                <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('cart.destroy', $item) }}" style="display:none">
                    @csrf @method('DELETE')
                </form>
            @endforeach
        @endif
    </div>
@endsection
