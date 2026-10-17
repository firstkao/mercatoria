@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8">
    <h1 class="text-2xl font-bold text-navy-500">Checkout</h1>
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif
    <div class="mt-5 divide-y divide-border rounded-xl border border-border bg-white">
        @foreach ($lines as $line)
            <div class="flex items-start justify-between gap-4 p-4">
                <div>
                    <h2 class="font-semibold">{{ $line['cart']->product->name }}</h2>
                    @if ($line['cart']->variant)
                        <p class="text-sm text-text-muted">{{ $line['cart']->variant->name }}</p>
                    @endif
                    <p class="text-sm text-text-muted">{{ $line['cart']->qty }} × {{ format_rupiah($line['pricing']['final_price']) }}</p>
                </div>
                <p class="font-semibold">{{ format_rupiah($line['subtotal']) }}</p>
            </div>
        @endforeach
        <div class="space-y-2 p-4 text-sm">
            <div class="flex justify-between"><span>Subtotal item (termasuk ongkir)</span><span>{{ format_rupiah($subtotal) }}</span></div>
            <div class="flex justify-between"><span>Margin layanan ({{ number_format($profitPercent, 2, ',', '.') }}%)</span><span>{{ format_rupiah($subtotal * $profitPercent / 100) }}</span></div>
            <div class="flex justify-between border-t border-border pt-3 text-base font-bold"><span>Total dibulatkan</span><span>{{ format_rupiah($total) }}</span></div>
        </div>
    </div>

    <form method="POST" action="{{ route('cart.place-order') }}" class="mt-6 space-y-5 rounded-xl border border-border bg-white p-5">
        @csrf
        @foreach ($cartIds as $cartId)
            <input type="hidden" name="cart_ids[]" value="{{ $cartId }}">
        @endforeach

        <div>
            <label for="platform" class="mb-1 block text-sm font-medium">Platform pembelian</label>
            <select id="platform" name="platform" required class="w-full rounded-lg border-border">
                <option value="toco" @selected(old('platform') === 'toco')>Toco</option>
                <option value="shopee" @selected(old('platform') === 'shopee')>Shopee</option>
            </select>
        </div>
        <fieldset>
            <legend class="mb-2 text-sm font-medium">Skema pembayaran</legend>
            <div class="flex gap-5">
                <label class="inline-flex items-center gap-2"><input type="radio" name="payment_scheme" value="fp" @checked(old('payment_scheme', 'fp') === 'fp')> Full Payment</label>
                <label class="inline-flex items-center gap-2"><input type="radio" name="payment_scheme" value="dp" @checked(old('payment_scheme') === 'dp')> Down Payment</label>
            </div>
        </fieldset>
        <div>
            <label for="payment_method" class="mb-1 block text-sm font-medium">Metode pembayaran</label>
            <select id="payment_method" name="payment_method" required class="w-full rounded-lg border-border">
                <option value="bca" @selected(old('payment_method') === 'bca')>Transfer BCA</option>
                <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                <option value="shopeepay" @selected(old('payment_method') === 'shopeepay')>ShopeePay</option>
            </select>
        </div>
        <button type="submit" class="w-full rounded-lg bg-primary-600 px-5 py-3 font-semibold text-white hover:bg-primary-700">
            Buat Pesanan
        </button>
    </form>
</div>
@endsection
