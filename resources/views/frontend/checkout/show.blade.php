@extends('layouts.app')

@section('title', 'Checkout — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        /* Checkout — CSS biasa (tidak bergantung build Tailwind), selaras dengan beranda & halaman produk. */
        .ck, .ck *, .ck *::before, .ck *::after { box-sizing: border-box; }
        .ck { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .ck a { text-decoration: none; }

        .ck-card { background: #fff; border: 1px solid #e1e1e1; border-radius: 0; padding: 24px; }
        .ck-h2 { margin: 0 0 12px; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }

        .ck-addr-name { margin: 0; font-size: 14px; line-height: 21px; font-weight: 700; color: #000; }
        .ck-addr-line { margin: 2px 0 0; font-size: 14px; line-height: 21px; color: #555; }
        .ck-addr-link { display: inline-block; margin-top: 10px; font-size: 13px; line-height: 18px; color: #02a1ea; }
        .ck-addr-link:hover { text-decoration: underline; }

        .ck-table-wrap { background: #fff; border: 1px solid #e1e1e1; border-radius: 0; }
        .ck-table { width: 100%; border-collapse: collapse; }
        .ck-table th { padding: 12px 16px; border-bottom: 1px solid #e1e1e1; text-align: left; font-size: 11px; line-height: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #333; }
        .ck-table td { padding: 14px 16px; border-bottom: 1px solid #e1e1e1; font-size: 14px; line-height: 21px; color: #333; }
        .ck-table tbody tr:last-child td { border-bottom: 0; }
        .ck-item-name { margin: 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #000; }
        .ck-item-var { margin: 2px 0 0; font-size: 12px; line-height: 18px; color: #888; }
        .ck-price { color: #02a1ea; }
        .ck-strong { font-weight: 700; color: #000; }

        .ck-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .ck-fields { display: flex; flex-direction: column; gap: 20px; }

        .ck-sum { margin: 14px 0 0; }
        .ck-sum-row { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: 14px; line-height: 21px; color: #555; }
        .ck-sum-row dt { margin: 0; }
        .ck-sum-row dd { margin: 0; color: #333; }
        .ck-sum-row.is-danger, .ck-sum-row.is-danger dd { color: #e53935; }
        .ck-sum-row.is-muted, .ck-sum-row.is-muted dd { color: #555; }
        .ck-sum-row.is-main, .ck-sum-row.is-main dt, .ck-sum-row.is-main dd { font-weight: 500; color: #000; }
        .ck-sum-row.is-total { padding-top: 10px; border-top: 1px solid #e1e1e1; font-size: 16px; line-height: 24px; font-weight: 700; }
        .ck-sum-row.is-total, .ck-sum-row.is-total dt, .ck-sum-row.is-total dd { color: #000; }

        @media (max-width: 720px) {
            .ck-grid { grid-template-columns: 1fr; }
        }
    </style>

    @php
        $shipping = app(\App\Services\Order\ShippingCalculatorService::class);
        $initialSubtotal = collect($lines)->sum(fn ($l) => $l['final_price_bersih'] * $l['qty']);
    @endphp

    <div class="au ck">
        <p class="au-eyebrow">Belanja</p>
        <h1 class="au-title">Checkout</h1>

        @php
            $needsVerify = auth()->user() && ! auth()->user()->hasVerifiedEmail();
            $needsDob = auth()->user() && ! auth()->user()->tgl_lahir;
        @endphp
        @if ($needsVerify || $needsDob)
            <div class="au-note">
                <x-icons.exclamation-triangle-fill class="au-ico" width="16" height="16" />
                <span>
                    @if ($needsVerify)
                        Email kamu belum diverifikasi. Verifikasi dulu agar pesananmu bisa diproses.
                        <a href="{{ route('verification.notice') }}" class="au-link">Kirim ulang link verifikasi</a>
                    @endif
                    @if ($needsVerify && $needsDob)
                        <br>
                    @endif
                    @if ($needsDob)
                        Tanggal lahir belum diisi.
                        <a href="{{ route('profile.complete') }}" class="au-link">Lengkapi profil</a>
                    @endif
                </span>
            </div>
        @endif

        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="au-form">
            @csrf

            @if ($items->contains(fn ($i) => $i->bundle_group_id))
                <div class="au-note">
                    <i class="bi bi-exclamation-triangle au-ico"></i>
                    <span>Slot Split Box baru terkunci setelah bukti bayar diupload; slot bisa diambil orang lain sebelum itu.</span>
                </div>
            @endif

            {{-- Alamat Pengiriman (DS 8.2) --}}
            <div class="ck-card">
                <h2 class="ck-h2">Alamat Pengiriman</h2>
                <div>
                    <p class="ck-addr-name">{{ auth()->user()->name }}</p>
                    <p class="ck-addr-line">{{ auth()->user()->alamat ?? '—' }}</p>
                    <p class="ck-addr-line">{{ auth()->user()->no_hp ?? '—' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="ck-addr-link">Ubah di Edit Profil</a>
            </div>

            @foreach ($items as $item)
                <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
            @endforeach

            <div class="ck-table-wrap">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                            <tr>
                                <td>
                                    <p class="ck-item-name">{{ $item->product->name }}</p>
                                    @if ($item->variant)
                                        <p class="ck-item-var">Varian: {{ $item->variant->name }}</p>
                                    @endif
                                </td>
                                <td class="ck-price">{{ format_rp($lines[$index]['final_price_bersih']) }}</td>
                                <td>{{ $item->qty }}</td>
                                <td class="ck-price">
                                    {{ format_rp($lines[$index]['final_price_bersih'] * $item->qty) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ck-grid">
                <div class="ck-card">
                    <div class="ck-fields">
                        <div>
                            <label for="platform" class="au-label">Platform Marketplace</label>
                            <select name="platform" id="platform" class="au-input summary-trigger">
                                <option value="toco">Toco</option>
                                <option value="shopee">Shopee</option>
                            </select>
                        </div>

                        <div>
                            <label for="scheme" class="au-label">Skema Pembayaran</label>
                            <select name="scheme" id="scheme" class="au-input summary-trigger">
                                <option value="fp">Full Payment (100%)</option>
                                <option value="dp">Down Payment (50%)</option>
                            </select>
                        </div>

                        <div>
                            <label for="payment_method" class="au-label">Metode Bayar</label>
                            <select name="payment_method" id="payment_method" class="au-input summary-trigger">
                                @foreach ($methods as $method)
                                    <option value="{{ $method->code }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="ck-card">
                    <div class="ck-fields">
                        <div>
                            <label for="discount_type" class="au-label">Diskon Koin / Voucher (pilih salah satu)</label>
                            <select name="discount_type" id="discount_type" class="au-input summary-trigger">
                                <option value="none">Tanpa diskon</option>
                                <option value="coin">Pakai Koin (saldo {{ number_format($coinBalance) }} · maks {{ number_format($maxCoin) }})</option>
                                @if ($userVouchers->isNotEmpty())
                                    <option value="voucher">Pakai Voucher</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <input type="number" name="coin_amount" id="coin_amount" min="0" max="{{ min($coinBalance, $maxCoin) }}"
                                   placeholder="Jumlah koin (maks {{ number_format($maxCoin) }})"
                                   class="au-input summary-trigger">
                            @error('coin_amount')<p class="au-err">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <select name="user_voucher_id" id="user_voucher_id" class="au-input summary-trigger">
                                <option value="">— Pilih voucher —</option>
                                @foreach ($userVouchers as $userVoucher)
                                    <option value="{{ $userVoucher->id }}">
                                        {{ $userVoucher->voucher->code }} — potong {{ format_rp($userVoucher->voucher->nominal) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_voucher_id')<p class="au-err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="ck-card">
                <label for="customer_note" class="au-label">Catatan untuk Mercatoria</label>
                <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                          placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                          class="au-input"></textarea>
            </div>

            <div class="ck-card">
                <h2 class="ck-h2">Ringkasan Pembayaran</h2>
                <dl class="ck-sum">
                    <div class="ck-sum-row"><dt>Subtotal</dt><dd id="sum-subtotal">{{ format_rp($initialSubtotal) }}</dd></div>
                    <div class="ck-sum-row is-danger"><dt>Diskon</dt><dd id="sum-discount">- Rp0</dd></div>
                    <div class="ck-sum-row is-main"><dt>Total Harga Barang</dt><dd id="sum-total">{{ format_rp($initialSubtotal) }}</dd></div>
                    <div class="ck-sum-row is-muted"><dt>Fee QRIS</dt><dd id="sum-fee-qris">Rp0</dd></div>
                    <div class="ck-sum-row is-total"><dt>Bayar di Web</dt><dd id="sum-tagihan">{{ format_rp($initialSubtotal) }}</dd></div>
                    <div class="ck-sum-row is-muted"><dt>Estimasi CO Marketplace</dt><dd id="sum-estimasi">Rp0</dd></div>
                    <div class="ck-sum-row is-muted"><dt>Biaya CO Marketplace</dt><dd id="sum-fee-co">Rp0</dd></div>
                </dl>
                <p class="au-eyebrow" style="margin-top:14px">
                    Harga item sudah final (dibulatkan sekali saat tampil). Total tidak dibulatkan lagi.
                </p>
            </div>

            <button type="submit" class="au-btn">BUAT PESANAN</button>
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

                form.addEventListener('submit', function () {
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn || btn.disabled) return;
                    btn.disabled = true;
                    btn.dataset.originalText = btn.textContent;
                    btn.textContent = 'Memproses...';
                });
            })();
        </script>
    </div>
@endsection
