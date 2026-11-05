@extends('layouts.app')

@section('title', 'Keranjang — MERCATORIA')

@section('content')
    {{-- Dipakai untuk font Roboto + background #f7f7f7 (sama dengan beranda/produk/auth) --}}
    @include('frontend.partials._auth-style')

    @php
        $calc = app(\App\Services\Order\ShippingCalculatorService::class);
        $rows = $items->map(fn ($i) => ['item' => $i, 'p' => $calc->calculateItemShipping($i->product, $i->variant)]);
        $subtotal = $rows->sum(fn ($r) => $r['p']['final_price_bersih'] * $r['item']->qty);
        $stores = $rows->pluck('p.store_name')->filter()->unique()->values();
        $province = auth()->user()->provinsi ?? null;
        // Koin yang didapat: 1 koin per Rp100 (sama dengan perhitungan di halaman produk)
        $coinEarn = intdiv((int) $subtotal, 100);
    @endphp

    <style>
        /* Keranjang — CSS biasa (tidak bergantung build Tailwind). Ukuran dari ss referensi 1920px. */
        .cart-box, .cart-box *, .cart-box *::before, .cart-box *::after { box-sizing: border-box; }
        .cart-box {
            position: relative; left: 50%; transform: translateX(-50%);
            width: min(1200px, calc(100vw - 48px)); margin: 28px 0 28px;
            padding: 47px 58px 90px; background: #fff;
            font-family: 'Roboto', system-ui, sans-serif; font-size: 16px; line-height: 24px; color: #26222f; -webkit-font-smoothing: antialiased;
        }
        .cart-box a { text-decoration: none; color: inherit; }
        .cart-title { margin: 0 0 0 -8px; font-size: 28px; line-height: 34px; font-weight: 500; color: #26222f; }

        /* Notice */
        .cart-coin { display: flex; align-items: center; gap: 17px; margin-top: 28px; padding: 0 23px; height: 58px;
                     border-top: 3px solid #26222f; background: #f7f6f7; font-size: 15px; line-height: 22px; color: #26222f; }
        .cart-coin svg { flex: none; }
        .cart-notice { margin-top: 20px; padding: 14px 18px; background: #fff5f5; border: 1px solid #f3c4c4; font-size: 14px; line-height: 20px; color: #c62828; }
        .cart-empty { margin-top: 32px; padding: 56px 24px; border: 1px solid #ddd; text-align: center; font-size: 16px; color: #555; }
        .cart-empty a { color: #02a1ea; margin-left: 4px; }
        .cart-empty a:hover { text-decoration: underline; }

        /* Tabel */
        .cart-table-wrap { margin-top: 32px; border: 1px solid #ddd; overflow-x: auto; }
        .cart-table { width: 100%; min-width: 760px; table-layout: fixed; border-collapse: collapse; }
        .cart-table th { height: 50px; padding: 0; background: #fbfbfb; border-bottom: 1px solid #ddd; text-align: left; font-size: 16px; line-height: 24px; font-weight: 500; color: #26222f; }
        .cart-table td { padding: 0; height: 93px; border-bottom: 1px solid #ddd; vertical-align: middle; color: #7a7a7a; }
        .cart-table tbody tr:last-child td { border-bottom: 0; }
        .cart-table .c-x { padding-left: 18px; }

        .cart-x { display: inline-flex; width: 20px; height: 20px; padding: 0; border: 0; background: none; color: #9a9a9a; cursor: pointer; }
        .cart-x:hover { color: #e53935; }

        .cart-thumb { position: relative; display: block; width: 70px; height: 70px; background: #f1f1f1; }
        .cart-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .cart-check { width: 16px; height: 16px; margin: 0; accent-color: #26222f; vertical-align: middle; }
        .cart-thumb .cart-check { position: absolute; top: 4px; left: 4px; background: #fff; }

        .cart-name { display: block; font-size: 16px; line-height: 24px; font-weight: 500; color: #26222f; }
        a.cart-name:hover { color: #02a1ea; }
        .cart-bundle { margin: 2px 0 0; font-size: 12px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #3986C6; }

        .cart-qty { display: flex; width: 118px; height: 36px; border: 1px solid #ddd; }
        .cart-qty > * { height: 34px; margin: 0; padding: 0; border: 0; background: #fff; font: 400 15px/34px 'Roboto', system-ui, sans-serif; color: #26222f; text-align: center; outline: 0; }
        .cart-qty button { width: 37px; cursor: pointer; }
        .cart-qty button:hover { background: #f7f7f7; }
        .cart-qty input { width: 42px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; color: #7a7a7a; -moz-appearance: textfield; }
        .cart-qty input::-webkit-outer-spin-button, .cart-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .cart-qty.is-static { justify-content: center; align-items: center; color: #7a7a7a; font-size: 15px; }

        .cart-foot { display: flex; justify-content: flex-end; padding: 12px 17px 26px; border-top: 1px solid #ddd; }
        .cart-btn { display: inline-block; height: 33px; padding: 0 18px; border: 0; border-radius: 2px; background: #26222f; color: #fff; cursor: pointer;
                    font: 700 12px/33px 'Roboto', system-ui, sans-serif; letter-spacing: .06em; text-transform: uppercase; }
        .cart-btn:hover { background: #3a3547; }
        .cart-btn:disabled { background: #918f96; cursor: default; }

        /* Bawah: catatan (kiri) + total (kanan) */
        .cart-lower { display: flex; align-items: flex-start; justify-content: space-between; gap: 32px; margin-top: 33px; }
        .cart-note { flex: 1; max-width: 440px; }
        .cart-note label { display: block; font-size: 14px; line-height: 20px; font-weight: 500; color: #26222f; }
        .cart-note textarea { display: block; width: 100%; margin-top: 8px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 0; background: #fff; resize: vertical;
                              font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #26222f; outline: 0; }
        .cart-note textarea:focus { border-color: #26222f; }
        .cart-note small { display: block; margin-top: 6px; font-size: 12px; color: #999; }

        .cart-total { width: 520px; flex: none; border: 1px solid #ddd; }
        .cart-total h2 { margin: 0; padding: 0 20px; height: 53px; border-bottom: 1px solid #ddd; background: #fbfbfb; font-size: 21px; line-height: 52px; font-weight: 400; color: #26222f; }
        .cart-total-body { padding: 0 21px 34px; }
        .cart-tr { display: flex; border-bottom: 1px solid #ddd; }
        .cart-tr > b { flex: none; width: 207px; font-weight: 500; color: #26222f; }
        .cart-tr > div { flex: 1; color: #7a7a7a; }
        .cart-tr.is-sub { padding: 30px 0 12px; }
        .cart-tr.is-ship { padding: 11px 0 30px; }
        .cart-tr.is-ship p { margin: 0; }
        .cart-tr.is-ship p + p { margin-top: 6px; }
        .cart-tr.is-ship p.is-link { margin-top: 16px; }
        .cart-tr.is-ship p strong { font-weight: 700; color: #4a4655; }
        .cart-tr.is-ship p.is-link a { color: #26222f; }
        .cart-tr.is-ship p.is-link a:hover { color: #02a1ea; }
        .cart-tr.is-all { padding: 11px 0; }
        .cart-go { display: block; width: 100%; height: 64px; margin-top: 22px; border: 0; border-radius: 2px; background: #26222f; color: #fff; cursor: pointer;
                   font: 500 18px/64px 'Roboto', system-ui, sans-serif; letter-spacing: .02em; text-transform: uppercase; text-align: center; }
        .cart-go:hover { background: #3a3547; }

        @media (max-width: 900px) {
            .cart-box { padding: 28px 18px 48px; }
            .cart-lower { flex-direction: column; }
            .cart-total { width: 100%; }
            .cart-note { max-width: none; width: 100%; }
        }
    </style>

    <div class="cart-box">
        <h1 class="cart-title">Keranjang</h1>

        @if (! empty($purgedNotice))
            <div class="cart-notice">{{ $purgedNotice }}</div>
        @endif

        @error('qty')
            <div class="cart-notice">{{ $message }}</div>
        @enderror

        @if ($items->isEmpty())
            <div class="cart-empty">
                Keranjang kamu masih kosong.
                <a href="/">Mulai belanja</a>
            </div>
        @else
            <form id="checkout-form" action="{{ route('checkout.show') }}" method="GET">

                <div class="cart-coin">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#26222f" stroke-width="1.6" aria-hidden="true"><rect x="1.5" y="2.5" width="13" height="11"/><path d="M1.5 5.5h13" stroke-width="3"/></svg>
                    <span>Selesaikan pesanan ini dan kumpulkan <b id="cart-coin-n">{{ number_format($coinEarn, 0, ',', '.') }}</b> Koin untuk potongan diskon di orderan berikutnya!</span>
                </div>

                <div class="cart-table-wrap" style="margin-top:32px">
                    <table class="cart-table">
                        <colgroup>
                            <col style="width:71px"><col style="width:106px"><col><col style="width:112px"><col style="width:162px"><col style="width:97px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th></th>
                                <th><input type="checkbox" checked class="cart-check" id="cart-all" title="Pilih semua" aria-label="Pilih semua"></th>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                @php
                                    $item = $r['item'];
                                    $price = $r['p']['final_price_bersih'];
                                    $img = $item->variant?->image_path ?? $item->product->image_path;
                                    $url = '/'.$item->product->game->slug.'/'.$item->product->slug;
                                @endphp
                                <tr data-row data-sub="{{ $price * $item->qty }}">
                                    <td class="c-x">
                                        <button type="submit" form="delete-form-{{ $item->id }}" class="cart-x" aria-label="Hapus {{ $item->product->name }}" title="Hapus">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><circle cx="10" cy="10" r="8.5"/><path d="m7 7 6 6m0-6-6 6"/></svg>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="cart-thumb">
                                            @if ($img)
                                                <img src="{{ \App\Services\Image\ImageOptimizerService::url($img, 'thumb') }}" alt="{{ $item->product->name }}">
                                            @endif
                                            <input type="checkbox" name="items[]" value="{{ $item->id }}" checked
                                                   form="checkout-form" class="cart-check" data-pick
                                                   @if ($item->bundle_group_id) data-bundle="{{ $item->bundle_group_id }}" @endif>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ $url }}" class="cart-name">{{ $item->product->name }}{{ $item->variant ? ' – '.$item->variant->name : '' }}</a>
                                        @if ($item->bundle_group_id)
                                            <p class="cart-bundle">Paket Split Box</p>
                                        @endif
                                    </td>
                                    <td>{{ format_rp($price) }}</td>
                                    <td>
                                        @if ($item->bundle_group_id)
                                            <div class="cart-qty is-static">{{ $item->qty }}</div>
                                        @else
                                            <div class="cart-qty">
                                                <button type="button" data-step="-1" aria-label="Kurangi">-</button>
                                                <input type="number" name="qty" value="{{ $item->qty }}" data-orig="{{ $item->qty }}" min="1" max="999"
                                                       form="qty-form-{{ $item->id }}" data-qty-input>
                                                <button type="button" data-step="1" aria-label="Tambah">+</button>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ format_rp($price * $item->qty) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="cart-foot">
                        <button type="button" class="cart-btn" id="cart-update" disabled>Perbarui Keranjang</button>
                    </div>
                </div>

                <div class="cart-lower">
                    <div class="cart-note">
                        <label for="customer_note">Catatan untuk Mercatoria</label>
                        <textarea name="customer_note" id="customer_note" rows="3" maxlength="500" form="checkout-form"
                                  placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"></textarea>
                        <small>Maksimal 500 karakter.</small>
                    </div>

                    <div class="cart-total">
                        <h2>Total keranjang belanja</h2>
                        <div class="cart-total-body">
                            <div class="cart-tr is-sub"><b>Subtotal</b><div id="cart-subtotal">{{ format_rp($subtotal) }}</div></div>
                            <div class="cart-tr is-ship">
                                <b>Pengiriman</b>
                                <div>
                                    <p>{{ $stores->isNotEmpty() ? $stores->implode(', ') : 'Standar' }}</p>
                                    @if ($province)
                                        <p>Dikirim ke <strong>{{ $province }}</strong>.</p>
                                    @endif
                                    <p class="is-link"><a href="{{ route('profile.edit') }}">Ubah alamat</a></p>
                                </div>
                            </div>
                            <div class="cart-tr is-all"><b>Total</b><div id="cart-total">{{ format_rp($subtotal) }}</div></div>
                            <button type="submit" form="checkout-form" class="cart-go">Lanjutkan ke checkout</button>
                        </div>
                    </div>
                </div>
            </form>

            @foreach ($items as $item)
                <form id="qty-form-{{ $item->id }}" method="POST" action="{{ route('cart.update', $item) }}" style="display:none">
                    @csrf @method('PATCH')
                </form>
                <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('cart.destroy', $item) }}" style="display:none">
                    @csrf @method('DELETE')
                </form>
            @endforeach

            <script>
                (function () {
                    var rp = function (n) { return 'Rp' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
                    var picks = document.querySelectorAll('[data-pick]');
                    var all = document.getElementById('cart-all');

                    // Total & koin mengikuti item yang dicentang
                    function recalc() {
                        var sum = 0;
                        document.querySelectorAll('[data-row]').forEach(function (tr) {
                            var c = tr.querySelector('[data-pick]');
                            if (c && c.checked) sum += parseFloat(tr.getAttribute('data-sub')) || 0;
                        });
                        document.getElementById('cart-subtotal').textContent = rp(sum);
                        document.getElementById('cart-total').textContent = rp(sum);
                        document.getElementById('cart-coin-n').textContent = Math.floor(sum / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        if (all) all.checked = Array.prototype.every.call(picks, function (c) { return c.checked; });
                    }

                    picks.forEach(function (c) {
                        c.addEventListener('change', function () {
                            // Item paket Split Box dicentang/dilepas bersamaan
                            var b = c.getAttribute('data-bundle');
                            if (b) document.querySelectorAll('[data-bundle="' + b + '"]').forEach(function (o) { o.checked = c.checked; });
                            recalc();
                        });
                    });
                    if (all) all.addEventListener('change', function () {
                        picks.forEach(function (c) { c.checked = all.checked; });
                        recalc();
                    });

                    // Stepper qty + tombol Perbarui Keranjang
                    var upd = document.getElementById('cart-update');
                    function changed() {
                        return Array.prototype.filter.call(document.querySelectorAll('[data-qty-input]'), function (i) {
                            return i.value !== i.getAttribute('data-orig');
                        });
                    }
                    function sync() { upd.disabled = changed().length === 0; }

                    document.querySelectorAll('[data-step]').forEach(function (b) {
                        b.addEventListener('click', function () {
                            var input = b.parentNode.querySelector('[data-qty-input]');
                            var n = (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-step'), 10);
                            input.value = Math.min(999, Math.max(1, n));
                            sync();
                        });
                    });
                    document.querySelectorAll('[data-qty-input]').forEach(function (i) { i.addEventListener('input', sync); });

                    upd.addEventListener('click', function () {
                        upd.disabled = true;
                        upd.textContent = 'Memperbarui...';
                        var jobs = changed().map(function (i) {
                            var form = document.getElementById(i.getAttribute('form'));
                            return fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        });
                        Promise.all(jobs).then(function () { window.location.reload(); })
                                         .catch(function () { window.location.reload(); });
                    });
                })();
            </script>
        @endif
    </div>
@endsection
