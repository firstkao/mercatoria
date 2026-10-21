@extends('layouts.app')

@section('title', 'Checkout Pesanan')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <h1 class="text-2xl font-bold text-navy-500">Checkout Pesanan</h1>
    <section class="mt-5 rounded-xl border border-border bg-white p-5"><h2 class="font-semibold">Alamat Pengiriman</h2><p class="mt-2 font-semibold">{{ auth()->user()->name }} · {{ auth()->user()->no_hp }}</p><p class="text-sm text-text-muted">{{ collect([auth()->user()->alamat_lengkap, auth()->user()->kecamatan, auth()->user()->kota, auth()->user()->provinsi, auth()->user()->kode_pos])->filter()->join(', ') }}</p><a href="{{ route('profile.edit') }}" class="mt-2 inline-block text-sm font-semibold text-navy-500 hover:underline">Ubah di Edit Profil →</a></section>
    @auth
        @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
            <x-profile-banner />
        @endif
    @endauth
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
            <div class="flex justify-between"><span>Subtotal item</span><span>{{ format_rupiah($quote['subtotal'] - ($quote['discount_amount'] - $quote['coin_discount_amount'] - $quote['voucher_discount_amount'])) }}</span></div>
            @if ($quote['discount_amount'] > 0)
                @if ($quote['coin_discount_amount'] > 0)
                    <div class="flex justify-between"><span>Diskon koin</span><span>-{{ format_rupiah($quote['coin_discount_amount']) }}</span></div>
                @endif
                @if ($quote['voucher_discount_amount'] > 0)
                    <div class="flex justify-between"><span>Diskon voucher</span><span>-{{ format_rupiah($quote['voucher_discount_amount']) }}</span></div>
                @endif
            @endif
            <div class="flex justify-between"><span>Fee marketplace</span><span>{{ format_rupiah($quote['fee_marketplace']) }}</span></div>
            @if ($quote['fee_qris'] > 0)<div class="flex justify-between"><span>Fee QRIS</span><span>{{ format_rupiah($quote['fee_qris']) }}</span></div>@endif
            <div class="flex justify-between border-t border-border pt-3 text-base font-bold"><span>Total dibulatkan</span><span>{{ format_rupiah($quote['total']) }}</span></div>
            @if (old('payment_scheme', request('payment_scheme', 'fp')) === 'dp')
                <div class="flex justify-between font-semibold"><span>DP 50% yang dibayar</span><span>{{ format_rupiah($quote['dp_amount']) }}</span></div>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('cart.checkout') }}" class="mt-6 space-y-5 rounded-xl border border-border bg-white p-5">
        @foreach ($cartIds as $cartId)
            <input type="hidden" name="cart_ids[]" value="{{ $cartId }}">
        @endforeach
        <h2 class="text-lg font-semibold">Pilihan pembayaran</h2>
        <div>
            <label for="platform" class="mb-1 block text-sm font-medium">Platform pembelian</label>
            <select id="platform" name="platform" required class="w-full rounded-lg border-border">
                <option value="toco" @selected(request('platform', 'toco') === 'toco')>Toco</option>
                <option value="shopee" @selected(request('platform') === 'shopee')>Shopee</option>
            </select>
        </div>
        <fieldset>
            <legend class="mb-2 text-sm font-medium">Skema pembayaran</legend>
            <div class="flex gap-5">
                <label class="inline-flex items-center gap-2"><input type="radio" name="payment_scheme" value="fp" @checked(request('payment_scheme', 'fp') === 'fp')> Full Payment</label>
                <label class="inline-flex items-center gap-2"><input type="radio" name="payment_scheme" value="dp" @checked(request('payment_scheme') === 'dp')> Down Payment</label>
            </div>
        </fieldset>
        <div>
            <label for="payment_method" class="mb-1 block text-sm font-medium">Metode pembayaran</label>
            <select id="payment_method" name="payment_method" required class="w-full rounded-lg border-border">
                <option value="bca" @selected(request('payment_method', 'bca') === 'bca')>Transfer BCA</option>
                <option value="qris" @selected(request('payment_method') === 'qris')>QRIS</option>
                <option value="shopeepay" @selected(request('payment_method') === 'shopeepay')>ShopeePay</option>
            </select>
        </div>
        <fieldset class="space-y-3 border-t border-border pt-4">
            <legend class="text-sm font-semibold">Diskon koin atau voucher</legend>
            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" name="use_coins" value="1" @checked(request()->boolean('use_coins')) @disabled($coinBalance <= 0) class="mt-1">
                <span>Gunakan koin (saldo {{ number_format($coinBalance, 0, ',', '.') }}). Maksimal 5% dari pesanan dan tidak dapat digabung dengan voucher.</span>
            </label>
            <div>
                <label for="user_voucher_id" class="mb-1 block text-sm font-medium">Voucher</label>
                <select id="user_voucher_id" name="user_voucher_id" class="w-full rounded-lg border-border">
                    <option value="">Tanpa voucher</option>
                    @foreach ($userVouchers as $userVoucher)
                        <option value="{{ $userVoucher->id }}" @selected((string) request('user_voucher_id') === (string) $userVoucher->id)>
                            {{ $userVoucher->voucher->code }} — {{ $userVoucher->voucher->name }}
                        </option>
                    @endforeach
                </select>
                @if ($userVouchers->isEmpty())
                    <p class="mt-1 text-xs text-text-muted">Belum ada voucher yang dapat digunakan.</p>
                @endif
            </div>
        </fieldset>
        <button type="submit" class="w-full rounded-lg border border-border px-5 py-3 font-semibold text-navy-500 hover:bg-surface-2">
            Perbarui Ringkasan
        </button>
    </form>

    <form method="POST" action="{{ route('cart.place-order') }}" class="mt-4 rounded-xl border border-border bg-white p-5">
        @csrf
        @foreach ($cartIds as $cartId)
            <input type="hidden" name="cart_ids[]" value="{{ $cartId }}">
        @endforeach
        <input type="hidden" name="platform" value="{{ request('platform', 'toco') }}">
        <input type="hidden" name="payment_scheme" value="{{ request('payment_scheme', 'fp') }}">
        <input type="hidden" name="payment_method" value="{{ request('payment_method', 'bca') }}">
        <input type="hidden" name="use_coins" value="{{ request()->boolean('use_coins') ? 1 : 0 }}">
        <input type="hidden" name="customer_note" value="{{ $customerNote }}">
        @if (request('user_voucher_id'))
            <input type="hidden" name="user_voucher_id" value="{{ request('user_voucher_id') }}">
        @endif
        <button type="submit"
                @disabled(auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
                class="w-full rounded-lg bg-[#4FC3F7] px-5 py-3 font-semibold text-white hover:brightness-95 disabled:cursor-not-allowed disabled:bg-primary-300 disabled:opacity-50">
            Buat Pesanan
        </button>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        if (typeof window.mercatoriaTrack === 'function') {
            window.mercatoriaTrack('begin_checkout', {
                currency: 'IDR',
                value: @js((float) $quote['total']),
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
