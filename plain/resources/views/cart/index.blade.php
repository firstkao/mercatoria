@extends('layouts.app', ['title' => 'Keranjang Belanja'])

@section('content')
<section class="card" style="max-width: 900px;">
    <h1>Keranjang Belanja</h1>
    
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
                    <div style="display: flex; gap: 15px; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 15px;">
                        @if($item->variant->image_path)
                            <img src="{{ $item->variant->imageUrl() }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                        @else
                            <div style="width: 80px; height: 80px; background: var(--bg); display: flex; align-items:center; justify-content:center; border-radius: 8px;">IMG</div>
                        @endif
                        <div style="flex: 1;">
                            <strong style="display:block;">{{ $item->variant->product->name }}</strong>
                            <span class="muted" style="font-size: 13px;">Varian: {{ $item->variant->name }}</span>
                            <div style="color: var(--accent-strong); font-weight: bold; margin-top: 5px;">{{ \App\Support\PriceCalculator::formatRupiah($price) }} x {{ $item->quantity }}</div>
                        </div>
                        <div>
                            <button type="submit" form="delete-{{ $item->id }}" class="link-button" style="color: var(--danger);">Hapus</button>
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

        @foreach($cartItems as $item)
            <form id="delete-{{ $item->id }}" action="{{ route('cart.destroy', $item) }}" method="POST" hidden>
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endif
</section>
@endsection

@push('scripts')
<script>
    (function() {
        const subtotal = {{ $subtotal }};
        const maxCoinUse = {{ min($availableCoins, $maxCoinDiscount) }};
        const mps = @json($marketplaces);
        
        function formatRp(num) { return 'Rp' + num.toLocaleString('id-ID'); }

        function calculate() {
            const scheme = document.querySelector('input[name="payment_scheme"]:checked').value;
            const mpId = document.querySelector('input[name="marketplace_id"]:checked').value;
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            
            let discount = 0;
            if (discountType === 'coin') { discount = maxCoinUse; }
            // Note: Voucher is validated strictly on backend. For frontend preview, we ignore it to prevent false promises.
            
            let netTotal = subtotal - discount;
            let mp = mps.find(m => m.id == mpId);
            
            let payNow = 0; let remaining = 0; let mpFee = 0;
            if (scheme === 'FP') {
                payNow = netTotal;
                mpFee = parseInt(mp.fp_fee_idr);
            } else {
                payNow = Math.floor(netTotal / 2);
                remaining = netTotal - payNow;
                mpFee = Math.floor(remaining * (parseFloat(mp.dp_fee_percent) / 100));
            }

            document.getElementById('discount-row').hidden = discount === 0;
            document.getElementById('discount-val').textContent = formatRp(discount);
            document.getElementById('mp-fee-val').textContent = formatRp(mpFee);
            
            // Note: User rule Opsi B -> mpFee is NOT collected in web checkout, it is collected in marketplace later.
            // So Pay Now is just the web portion!
            document.getElementById('pay-now-val').textContent = formatRp(payNow);
            
            if (scheme === 'DP' || mpFee > 0) {
                document.getElementById('remaining-row').hidden = false;
                document.getElementById('remaining-val').textContent = formatRp(remaining + mpFee) + " (Termasuk Biaya Admin)";
            } else {
                document.getElementById('remaining-row').hidden = true;
            }

            document.getElementById('coin-estimate').textContent = Math.floor(netTotal * 0.01).toLocaleString('id-ID') + ' Koin';
        }

        document.querySelectorAll('#checkout-form input[type="radio"]').forEach(el => {
            el.addEventListener('change', calculate);
        });
        
        if (document.getElementById('checkout-form')) calculate();
    })();
</script>
@endpush
