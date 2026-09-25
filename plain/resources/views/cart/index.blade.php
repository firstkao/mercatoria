@extends('layouts.app', ['title' => 'Keranjang'])

@section('content')
    <section class="card">
        <h1>Keranjang</h1>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if ($items->isEmpty())
            <p>Keranjang masih kosong. Kembali ke <a href="{{ route('catalog.index') }}">katalog</a> untuk memilih produk.</p>
        @else
            <div class="cart-layout">
                <div class="cart-items">
                    @foreach ($items as $item)
                        <div class="cart-item">
                            <div>
                                <h2>{{ $item->product_name }}</h2>
                                <p class="muted">Varian: {{ $item->variant_name }}</p>
                            </div>

                            <div class="cart-item__meta">
                                <form method="POST" action="{{ route('cart.update', $item->cart_item_id) }}" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <label>
                                        Jumlah
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99">
                                    </label>
                                    <button type="submit" class="button button--small">Simpan</button>
                                </form>

                                <form method="POST" action="{{ route('cart.destroy', $item->cart_item_id) }}" class="inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-button">Hapus</button>
                                </form>
                            </div>

                            <div class="cart-item__price">
                                @if ($item->unit_price_idr)
                                    {{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}
                                @else
                                    Harga belum tersedia
                                @endif
                                <strong>{{ \App\Support\PriceCalculator::formatRupiah($item->line_total_idr) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                <aside class="cart-summary">
                    <h2>Ringkasan</h2>
                    <dl>
                        <div><dt>Subtotal</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($subtotal) }}</dd></div>
                    </dl>

                    <form method="POST" action="{{ route('orders.checkout') }}">
                        @csrf
                        <label>
                            Marketplace
                            <select name="marketplace_id" required>
                                @foreach ($marketplaces as $marketplace)
                                    <option value="{{ $marketplace->id }}">{{ $marketplace->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            Skema pembayaran
                            <select name="payment_scheme" required>
                                <option value="dp">DP 50%</option>
                                <option value="fp">Full Payment</option>
                            </select>
                        </label>

                        <button type="submit" class="button button--block">Buat order</button>
                    </form>
                </aside>
            </div>
        @endif
    </section>
@endsection
