@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <h1 class="text-2xl font-bold text-navy-500">Keranjang Belanja</h1>
    @auth
        @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
            <x-profile-banner />
        @endif
    @endauth
    @if (session('success'))
        <p role="status" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif
    @if ($lines->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-border bg-white p-8 text-center">
            <p>Keranjang masih kosong.</p>
            <a href="{{ route('catalog.index') }}" class="mt-3 inline-block font-semibold text-navy-500 hover:underline">Jelajahi katalog</a>
        </div>
    @else
        <form id="cart-checkout-selection" method="GET" action="{{ route('cart.checkout') }}"></form>
        <div class="mt-6 space-y-4">
            @foreach ($lines as $line)
                @php($cart = $line['cart'])
                <article class="grid gap-4 rounded-xl border border-border bg-white p-4 sm:grid-cols-[auto_1fr_auto] sm:items-start">
                    <input form="cart-checkout-selection" type="checkbox" name="cart_ids[]" value="{{ $cart->id }}" aria-label="Pilih {{ $cart->product->name }}" class="mt-1 rounded border-border text-primary-600">
                    <div>
                        <h2 class="font-semibold">{{ $cart->product->name }}</h2>
                        @if ($cart->variant)
                            <p class="text-sm text-text-muted">{{ $cart->variant->name }}</p>
                        @endif
                        <p class="mt-1 text-sm font-semibold">{{ format_rupiah($line['pricing']['final_price']) }} / item</p>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:justify-end">
                        <form method="POST" action="{{ route('cart.update', $cart) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <label class="sr-only" for="qty-{{ $cart->id }}">Jumlah {{ $cart->product->name }}</label>
                            <input id="qty-{{ $cart->id }}" name="qty" type="number" min="1" max="99" value="{{ $cart->qty }}" class="w-20 rounded-lg border-border text-sm">
                            <label class="sr-only" for="note-{{ $cart->id }}">Catatan untuk {{ $cart->product->name }}</label>
                            <input id="note-{{ $cart->id }}" name="customer_note" type="text" maxlength="500" value="{{ $cart->customer_note }}" placeholder="Catatan (opsional, maks. 500)" class="w-56 rounded-lg border-border text-sm">
                            <button type="submit" class="rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-surface-2">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('cart.remove', $cart) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6 flex justify-end">
            <button form="cart-checkout-selection" type="submit"
                    @disabled(auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
                    class="rounded-lg bg-primary-600 px-5 py-3 font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:bg-primary-300 disabled:opacity-50">
                Checkout item terpilih
            </button>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        if (typeof window.mercatoriaTrack === 'function') {
            window.mercatoriaTrack('view_cart', {
                currency: 'IDR',
                value: @js((float) $lines->sum(fn ($line) => $line['pricing']['final_price'] * $line['cart']->qty)),
                items: @js($lines->map(fn ($line) => [
                    'item_id' => $line['cart']->product->sku,
                    'item_name' => $line['cart']->product->name,
                    'item_variant' => $line['cart']->variant?->name,
                    'price' => (float) $line['pricing']['final_price'],
                    'quantity' => $line['cart']->qty,
                ])->values())
            });
        }
    </script>
@endpush
