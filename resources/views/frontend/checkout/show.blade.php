@extends('layouts.app')

@section('title', 'Checkout — Mercatoria')

@section('content')
    @php
        $shipping = app(\App\Services\Order\ShippingCalculatorService::class);
        $initialSubtotal = collect($lines)->sum(fn ($l) => $l['final_price_bersih'] * $l['qty']);
    @endphp

    <h1 class="text-2xl font-semibold">Checkout</h1>

    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="mt-6 space-y-6">
        @csrf

        @foreach ($items as $item)
            <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
        @endforeach

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr class="border-b border-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $item->product->name }}</p>
                                @if ($item->variant)
                                    <p class="text-xs text-gray-500">Varian: {{ $item->variant->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ format_rp($lines[$index]['final_price_bersih']) }}</td>
                            <td class="px-4 py-3">{{ $item->qty }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ format_rp($lines[$index]['final_price_bersih'] * $item->qty) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <label for="platform" class="text-sm font-medium">Platform Marketplace</label>
                <select name="platform" id="platform" class="summary-trigger mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                    <option value="toco">Toco</option>
                    <option value="shopee">Shopee</option>
                </select>

                <label for="scheme" class="mt-4 block text-sm font-medium">Skema Pembayaran</label>
                <select name="scheme" id="scheme" class="summary-trigger mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                    <option value="fp">Full Payment (100%)</option>
                    <option value="dp">Down Payment (50%)</option>
                </select>

                <label for="payment_method" class="mt-4 block text-sm font-medium">Metode Bayar</label>
                <select name="payment_method" id="payment_method" class="summary-trigger mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                    @foreach ($methods as $method)
                        <option value="{{ $method->code }}">{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <label for="discount_type" class="text-sm font-medium">Diskon Koin / Voucher (pilih salah satu)</label>
                <select name="discount_type" id="discount_type" class="summary-trigger mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                    <option value="none">Tanpa diskon</option>
                    <option value="coin">Pakai Koin (saldo {{ number_format($coinBalance) }} · maks {{ number_format($maxCoin) }})</option>
                    @if ($userVouchers->isNotEmpty())
                        <option value="voucher">Pakai Voucher</option>
                    @endif
                </select>

                <input type="number" name="coin_amount" id="coin_amount" min="0" max="{{ min($coinBalance, $maxCoin) }}"
                       placeholder="Jumlah koin (maks {{ number_format($maxCoin) }})"
                       class="summary-trigger mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">

                <select name="user_voucher_id" id="user_voucher_id" class="summary-trigger mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                    <option value="">— Pilih voucher —</option>
                    @foreach ($userVouchers as $userVoucher)
                        <option value="{{ $userVoucher->id }}">
                            {{ $userVoucher->voucher->code }} — potong {{ format_rp($userVoucher->voucher->nominal) }}
                        </option>
                    @endforeach
                </select>

                @error('coin_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('user_voucher_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <label for="customer_note" class="text-sm font-medium">Catatan untuk Mercatoria</label>
            <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                      placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                      class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"></textarea>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold">Ringkasan Pembayaran</h2>
            <dl class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between"><dt>Subtotal</dt><dd id="sum-subtotal">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-red-600"><dt>Diskon</dt><dd id="sum-discount">- Rp0</dd></div>
                <div class="flex justify-between font-medium"><dt>Total Harga Barang</dt><dd id="sum-total">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-gray-500"><dt>Fee QRIS</dt><dd id="sum-fee-qris">Rp0</dd></div>
                <div class="flex justify-between text-lg font-semibold"><dt>Bayar di Web</dt><dd id="sum-tagihan">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-gray-500"><dt>Estimasi CO Marketplace</dt><dd id="sum-estimasi">Rp0</dd></div>
                <div class="flex justify-between text-gray-500"><dt>Biaya CO Marketplace</dt><dd id="sum-fee-co">Rp0</dd></div>
            </dl>
            <p class="mt-3 text-xs text-gray-400">
                Harga item sudah final (dibulatkan sekali saat tampil). Total tidak dibulatkan lagi.
            </p>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold text-white hover:bg-gray-800">
            BUAT PESANAN
        </button>
    </form>

    <script>
        (function () {
            const form = document.getElementById('checkout-form');
            const token = form.querySelector('input[name=_token]').value;
            const cartItemIds = @json($items->pluck('id'));
            const rp = (value) => 'Rp' + Number(value || 0).toLocaleString('id-ID');

            async function refreshSummary() {
                const payload = {
                    cart_item_ids: cartItemIds,
                    platform: form.platform.value,
                    scheme: form.scheme.value,
                    payment_method: form.payment_method.value,
                    discount_type: form.discount_type.value,
                    coin_amount: form.coin_amount.value ? Number(form.coin_amount.value) : null,
                    user_voucher_id: form.user_voucher_id.value ? Number(form.user_voucher_id.value) : null,
                };

                try {
                    const response = await fetch('{{ route('checkout.summary') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const s = await response.json();
                    document.getElementById('sum-subtotal').textContent = rp(s.subtotal);
                    document.getElementById('sum-discount').textContent = '- ' + rp(s.discount);
                    document.getElementById('sum-total').textContent = rp(s.total_barang);
                    document.getElementById('sum-fee-qris').textContent = rp(s.fee_qris);
                    document.getElementById('sum-tagihan').textContent = rp(s.tagihan_web);
                    document.getElementById('sum-estimasi').textContent = rp(s.estimasi_co_marketplace);
                    document.getElementById('sum-fee-co').textContent = rp(s.fee_co_marketplace);
                } catch (error) {
                    // Server tetap menghitung ulang saat "Buat Pesanan".
                }
            }

            form.querySelectorAll('.summary-trigger').forEach((el) => {
                el.addEventListener('change', refreshSummary);
                el.addEventListener('input', refreshSummary);
            });
        })();
    </script>
@endsection
