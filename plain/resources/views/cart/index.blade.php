@extends('layouts.app', ['title' => 'Keranjang Belanja'])

@section('main_class', 'main--default')

@section('content')
<section class="card" style="max-width: 900px;">
    <h1>Keranjang Belanja</h1>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert--danger" style="margin-bottom:16px;">
            @foreach ($errors->all() as $error)
                <p style="margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($cartItems->isEmpty())
        <div class="empty">
            <p>Keranjang masih kosong.</p>
            <a href="{{ route('catalog.index') }}" class="button">Kembali ke Katalog</a>
        </div>
    @else
        <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
            @csrf

            <div class="cart-items" style="margin-bottom: 30px;">
                @foreach($cartItems as $item)
                    @php($price = $item->variant->sellingPrice($calculator))
                    <div style="display: flex; gap: 15px; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 15px; align-items:center;">
                        @if($item->variant->image_path)
                            <img src="{{ $item->variant->imageUrl() }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" alt="">
                        @else
                            <div style="width: 80px; height: 80px; background: var(--bg); display: flex; align-items:center; justify-content:center; border-radius: 8px;">IMG</div>
                        @endif
                        <div style="flex: 1;">
                            <strong style="display:block;">{{ $item->variant->product->name }}</strong>
                            <span class="muted" style="font-size: 13px;">Varian: {{ $item->variant->name }}</span>
                            <div style="color: var(--accent-strong); font-weight: bold; margin-top: 5px;">
                                {{ \App\Support\PriceCalculator::formatRupiah($price) }} × {{ $item->quantity }}
                            </div>
                        </div>
                        <div>
                            <button type="button" class="link-button" style="color: var(--danger);"
                                    onclick="document.getElementById('delete-{{ $item->id }}').submit();">
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <fieldset style="margin-bottom: 20px;">
                <legend>Pilih Potongan (Opsional)</legend>
                <label class="check">
                    <input type="radio" name="discount_type" value="none" checked> Tanpa Potongan
                </label>
                <label class="check">
                    <input type="radio" name="discount_type" value="coin" @disabled($availableCoins == 0)>
                    Gunakan Koin (Maks {{ \App\Support\PriceCalculator::formatRupiah(min($availableCoins, $maxCoinDiscount)) }})
                </label>
                <label class="check">
                    <input type="radio" name="discount_type" value="voucher"> Kode Voucher:
                    <input type="text" name="voucher_code" style="padding: 4px; border: 1px solid var(--border); border-radius: 4px; width: 150px;">
                </label>
            </fieldset>

            <div class="field-row" style="margin-bottom: 20px;">
                <fieldset>
                    <legend>Skema Pembayaran</legend>
                    <label class="check"><input type="radio" name="payment_scheme" value="FP" checked> Full Payment (FP)</label>
                    <label class="check"><input type="radio" name="payment_scheme" value="DP"> Down Payment (DP 50%)</label>
                </fieldset>

                <fieldset>
                    <legend>Pengiriman Marketplace</legend>
                    @foreach($marketplaces as $mp)
                        <label class="check"><input type="radio" name="marketplace_id" value="{{ $mp->id }}" @checked($loop->first)> {{ $mp->name }}</label>
                    @endforeach
                </fieldset>
            </div>

            <div style="background: var(--primary-bg); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Ringkasan Pembayaran</h3>
                <dl class="details" style="margin-top: 0;">
                    <div><dt>Subtotal Produk</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($subtotal) }}</dd></div>
                    <div id="discount-row" hidden><dt>Potongan</dt><dd style="color: var(--danger);">- <span id="discount-val">Rp0</span></dd></div>
                    <div><dt>Biaya Marketplace</dt><dd id="mp-fee-val">Rp0</dd></div>
                    <hr style="border: 0; border-top: 1px solid #bae6fd; width: 100%;">
                    <div><dt>Total Bayar Sekarang</dt><dd style="font-size: 1.25rem; font-weight: bold; color: var(--accent-strong);" id="pay-now-val">Rp0</dd></div>
                    <div id="remaining-row" hidden><dt>Sisa Pelunasan Nanti</dt><dd id="remaining-val">Rp0</dd></div>
                </dl>
                <p class="muted" style="font-size: 12px; margin-top: 15px;">* Estimasi Cashback: <strong id="coin-estimate" style="color: var(--warning);">0 Koin</strong> (Masuk setelah pesanan Selesai)</p>
            </div>

            <button type="submit" class="button button--block">Checkout Sekarang</button>
        </form>

        {{-- Form delete terpisah (di luar form checkout, biar HTML-nya valid) --}}
        @foreach($cartItems as $item)
            <form id="delete-{{ $item->id }}" action="{{ route('cart.destroy', $item) }}" method="POST" hidden>
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
</section>
@endsection

@push('scripts')
<script>
    (function() {
        const subtotal = {{ (int) $subtotal }};
        const maxCoinUse = {{ (int) min($availableCoins, $maxCoinDiscount) }};
        const mps = @json($marketplaces);

        function formatRp(num) { return 'Rp' + num.toLocaleString('id-ID'); }

        const checkoutForm = document.getElementById('checkout-form');
        if (!checkoutForm) return;

        function calculate() {
            const scheme = document.querySelector('input[name="payment_scheme"]:checked')?.value || 'FP';
            const mpId = document.querySelector('input[name="marketplace_id"]:checked')?.value;
            const discountType = document.querySelector('input[name="discount_type"]:checked')?.value || 'none';

            let discount = 0;
            if (discountType === 'coin') { discount = maxCoinUse; }

            let netTotal = subtotal - discount;
            let mp = mps.find(m => m.id == mpId);
            if (!mp) return;

            let payNow = 0, remaining = 0, mpFee = 0;
            if (scheme === 'FP') {
                payNow = netTotal;
                mpFee = parseInt(mp.fp_fee_idr || 0);
            } else {
                payNow = Math.floor(netTotal / 2);
                remaining = netTotal - payNow;
                mpFee = Math.floor(remaining * (parseFloat(mp.dp_fee_percent || 0) / 100));
            }

            const discountRow = document.getElementById('discount-row');
            const discountVal = document.getElementById('discount-val');
            const mpFeeVal = document.getElementById('mp-fee-val');
            const payNowVal = document.getElementById('pay-now-val');
            const remainingRow = document.getElementById('remaining-row');
            const remainingVal = document.getElementById('remaining-val');
            const coinEstimate = document.getElementById('coin-estimate');

            if (discountRow) discountRow.hidden = discount === 0;
            if (discountVal) discountVal.textContent = formatRp(discount);
            if (mpFeeVal) mpFeeVal.textContent = formatRp(mpFee);
            if (payNowVal) payNowVal.textContent = formatRp(payNow);

            if (remainingRow) {
                if (scheme === 'DP' || mpFee > 0) {
                    remainingRow.hidden = false;
                    if (remainingVal) remainingVal.textContent = formatRp(remaining + mpFee) + " (Termasuk Biaya Admin)";
                } else {
                    remainingRow.hidden = true;
                }
            }

            if (coinEstimate) coinEstimate.textContent = Math.floor(netTotal * 0.01).toLocaleString('id-ID') + ' Koin';
        }

        checkoutForm.querySelectorAll('input[type="radio"]').forEach(el => {
            el.addEventListener('change', calculate);
        });

        calculate();
    })();
</script>
@endpush
