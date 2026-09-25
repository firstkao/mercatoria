@extends('layouts.app', ['title' => 'Keranjang'])
@section('content')
<h1>Keranjang</h1>
@if (session('status')) <div class="notice">{{ session('status') }}</div> @endif
@if ($items->isEmpty())
    <p class="card">Keranjang masih kosong.</p>
@else
    <div class="card">
    @foreach ($items as $item)
        <div class="details">
            <div><dt>{{ $item->product_name }}</dt><dd>{{ $item->variant_name }}</dd></div>
            <div><dt>Jumlah</dt><dd><form method="POST" action="{{ route('cart.update', $item->id) }}">@csrf @method('PATCH')<input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99"><button class="button button--small">Simpan</button></form></dd></div>
            <div><dt></dt><dd><form method="POST" action="{{ route('cart.destroy', $item->id) }}">@csrf @method('DELETE')<button class="link-button">Hapus</button></form></dd></div>
        </div>
    @endforeach
    </div>
    <section class="card">
        <h2>Checkout</h2>
        <form method="POST" action="{{ route('orders.checkout') }}">@csrf
            <label>Marketplace<select name="marketplace_id" required>@foreach (\App\Models\Marketplace::where('is_active', true)->orderBy('name')->get() as $marketplace)<option value="{{ $marketplace->id }}">{{ $marketplace->name }}</option>@endforeach</select></label>
            <label>Skema pembayaran<select name="payment_scheme" required><option value="dp">DP 50%</option><option value="fp">Full Payment</option></select></label>
            <button class="button">Buat order</button>
        </form>
    </section>
@endif
@endsection
