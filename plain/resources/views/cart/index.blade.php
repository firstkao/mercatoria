@extends('layouts.app', ['title' => 'Keranjang Belanja'])

@section('main_class', 'main--default')

@section('content')
<div class="account-page account-page--wide">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Keranjang</p>
            <h1 class="account-head__title">Keranjang Belanja</h1>
            <div class="account-head__meta">
                <span>{{ $cartItems->count() }} item</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="account-alert account-alert--danger" role="alert">
                <strong class="account-alert__title">Periksa kembali</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="account-section">
                <div class="account-empty">
                    <h2 class="account-empty__title">Keranjang masih kosong.</h2>
                    <p class="account-empty__sub">Tambahkan produk dulu sebelum checkout.</p>
                    <a href="{{ route('catalog.index') }}" class="account-empty__cta">Kembali ke katalog</a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
                @csrf

                {{-- ========== DAFTAR ITEM ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Daftar Item</h2>
                    </div>
                    <ul class="cart-ledger">
                        @foreach($cartItems as $item)
                            @php($price = $item->variant->sellingPrice($calculator))
                            <li class="cart-ledger__item">
                                <div class="cart-ledger__thumb">
                                    @if($item->variant->image_path)
                                        <img src="{{ $item->variant->imageUrl() }}" alt="">
                                    @else
                                        <div class="image-placeholder">IMG</div>
                                    @endif
                                </div>

                                <div class="cart-ledger__info">
                                    <strong class="cart-ledger__name">{{ $item->variant->product->name }}</strong>
                                    <span class="cart-ledger__meta">Varian: {{ $item->variant->name }}</span>
                                    <span class="cart-ledger__unit">
                                        {{ \App\Support\PriceCalculator::formatRupiah($price) }} / item
                                    </span>
                                </div>

                                <div class="cart-ledger__price">
                                    <span class="cart-ledger__amount">
                                        {{ \App\Support\PriceCalculator::formatRupiah($price * $item->quantity) }}
                                    </span>

                                    <div class="product-boxed-qty">
                                        <button type="submit"
                                                form="qty-form-{{ $item->id }}"
                                                name="quantity"
                                                value="{{ $item->quantity - 1 }}"
                                                class="product-boxed-qty-btn"
                                                aria-label="Kurangi jumlah">−</button>
                                        <input type="text"
                                               class="product-boxed-qty-input"
                                               value="{{ $item->quantity }}"
                                               readonly
                                               tabindex="-1"
                                               aria-label="Jumlah">
                                        <button type="submit"
                                                form="qty-form-{{ $item->id }}"
                                                name="quantity"
                                                value="{{ $item->quantity + 1 }}"
                                                class="product-boxed-qty-btn"
                                                @disabled($item->quantity >= 99)
                                                aria-label="Tambah jumlah">+</button>
                                    </div>
                                </div>

                                <div class="cart-ledger__actions">
                                    <button type="button" class="cart-ledger__remove"
                                            onclick="document.getElementById('delete-{{ $item->id }}').submit();">
                                        Hapus
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- ========== CATATAN PEMBELIAN ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Catatan Pembelian</h2>
                        <span class="account-section__hint">Opsional</span>
                    </div>
                    <div class="account-form">
                        <label class="field field--full">
                            <span>Catatan untuk Penjual</span>
                            <textarea name="notes"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Contoh: bubble wrap extra, no gift card, packing rapi.">{{ old('notes') }}</textarea>
                            <small class="hint">
                                Instruksi khusus soal packing, gift card, atau permintaan lain ke penjual. Boleh dikosongkan.
                            </small>
                            @include('partials.field-error', ['name' => 'notes'])
                        </label>
                    </div>
                </section>

                {{-- ========== POTONGAN ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Potongan</h2>
                        <span class="account-section__hint">Opsional</span>
                    </div>
                    <div class="cart-options">
                        <label class="cart-option">
                            <input type="radio" name="discount_type" value="none" checked>
                            <span class="cart-option__body">
                                <strong>Tanpa Potongan</strong>
                                <small>Bayar harga penuh</small>
                            </span>
                        </label>

                        <label class="cart-option @if($availableCoins == 0) is-disabled @endif">
                            <input type="radio" name="discount_type" value="coin" @disabled($availableCoins == 0)>
                            <span class="cart-option__body">
                                <strong>Gunakan Koin</strong>
                                <small>
                                    @if($availableCoins > 0)
                                        Maks {{ \App\Support\PriceCalculator::formatRupiah(min($availableCoins, $maxCoinDiscount)) }}
                                    @else
                                        Tidak ada koin tersedia
                                    @endif
                                </small>
                            </span>
                        </label>

                        <label class="cart-option cart-option--input">
                            <input type="radio" name="discount_type" value="voucher">
                            <span class="cart-option__body">
                                <strong>Kode Voucher</strong>
                                <input type="text" name="voucher_code" class="cart-option__input" placeholder="Masukkan kode">
                            </span>
                        </label>
                    </div>
                </section>

                {{-- ========== SKEMA PEMBAYARAN ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Skema Pembayaran</h2>
                    </div>
                    <div class="cart-options cart-options--inline">
                        <label class="cart-option cart-option--compact">
                            <input type="radio" name="payment_scheme" value="FP" checked>
                            <span class="cart-option__body">
                                <strong>Full Payment</strong>
                                <small>Lunas sekarang</small>
                            </span>
                        </label>
                        <label class="cart-option cart-option--compact">
                            <input type="radio" name="payment_scheme" value="DP">
                            <span class="cart-option__body">
                                <strong>Down Payment (50%)</strong>
                                <small>Sisa dibayar nanti</small>
                            </span>
                        </label>
                    </div>
                </section>

                {{-- ========== MARKETPLACE ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Pengiriman Marketplace</h2>
                    </div>
                    <div class="cart-options cart-options--inline">
                        @foreach($marketplaces as $mp)
                            <label class="cart-option cart-option--compact">
                                <input type="radio" name="marketplace_id" value="{{ $mp->id }}" @checked($loop->first)>
                                <span class="cart-option__body">
                                    <strong>{{ $mp->name }}</strong>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- ========== RINGKASAN ========== --}}
                <section class="account-section">
                    <div class="account-section__head">
                        <h2 class="account-section__title">Ringkasan Pembayaran</h2>
                    </div>
                    <dl class="order-summary">
                        <div class="order-summary__row">
                            <dt>Subtotal Produk</dt>
                            <dd>{{ \App\Support\PriceCalculator::formatRupiah($subtotal) }}</dd>
                        </div>
                        <div class="order-summary__row" id="discount-row" hidden>
                            <dt>Potongan</dt>
                            <dd class="is-discount">− <span id="discount-val">Rp0</span></dd>
                        </div>
                        <div class="order-summary__row">
                            <dt>Biaya Marketplace</dt>
                            <dd id="mp-fee-val">Rp0</dd>
                        </div>
                        <div class="order-summary__row order-summary__row--total">
                            <dt>Total Bayar Sekarang</dt>
                            <dd id="pay-now-val">Rp0</dd>
                        </div>
                        <div class="order-summary__row" id="remaining-row" hidden>
                            <dt>Sisa Pelunasan Nanti</dt>
                            <dd id="remaining-val">Rp0</dd>
                        </div>
                    </dl>
                    <p class="cart-summary__note">
                        * Estimasi Cashback:
                        <strong id="coin-estimate">0 Koin</strong>
                        (masuk setelah pesanan selesai)
                    </p>
                </section>

                <div class="account-section account-section--actions">
                    <button type="submit" class="account-btn account-btn--block">Checkout Sekarang</button>
                </div>
            </form>

            {{-- Form tersembunyi — WAJIB di luar form checkout --}}
            @foreach($cartItems as $item)
                <form id="qty-form-{{ $item->id }}"
                      method="POST"
                      action="{{ route('cart.update', $item) }}"
                      hidden>
                    @csrf
                    @method('PUT')
                </form>
                <form id="delete-{{ $item->id }}"
                      method="POST"
                      action="{{ route('cart.destroy', $item) }}"
                      hidden>
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    </div>
</div>
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
                mpFee = parseInt(mp.fp_fee_idr || 0);
                payNow = netTotal + mpFee;
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
                    if (remainingVal) remainingVal.textContent = formatRp(remaining + mpFee) + " (termasuk biaya admin)";
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
