@extends('layouts.app')

@section('title', 'Checkout — Mercatoria')

@section('content')
    @php
        $shipping = app(\App\Services\Order\ShippingCalculatorService::class);
        $initialSubtotal = collect($lines)->sum(fn ($l) => $l['final_price_bersih'] * $l['qty']);
    @endphp

    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Belanja</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Checkout</h1>

    @php
        $needsVerify = auth()->user() && ! auth()->user()->hasVerifiedEmail();
        $needsDob = auth()->user() && ! auth()->user()->tgl_lahir;
    @endphp
    @if ($needsVerify || $needsDob)
        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3">
            <p class="flex items-start gap-2 text-sm text-amber-900">
                <x-icons.exclamation-triangle-fill class="mt-0.5 shrink-0" width="16" height="16" />
                <span>
                    @if ($needsVerify)
                        Email kamu belum diverifikasi. Verifikasi dulu agar pesananmu bisa diproses.
                        <a href="{{ route('verification.notice') }}" class="font-medium underline decoration-amber-400 underline-offset-4 hover:decoration-amber-900">Kirim ulang link verifikasi</a>
                    @endif
                    @if ($needsVerify && $needsDob)
                        <br>
                    @endif
                    @if ($needsDob)
                        Tanggal lahir belum diisi.
                        <a href="{{ route('profile.complete') }}" class="font-medium underline decoration-amber-400 underline-offset-4 hover:decoration-amber-900">Lengkapi profil</a>
                    @endif
                </span>
            </p>
        </div>
    @endif

    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="mt-6 space-y-6">
        @csrf

        @if ($items->contains(fn ($i) => $i->bundle_group_id))
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Slot Split Box baru terkunci setelah bukti bayar diupload; slot bisa diambil orang lain sebelum itu.
            </div>
        @endif

        {{-- Alamat Pengiriman (DS 8.2) --}}
        <div class="rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Alamat Pengiriman</h2>
            <div class="mt-2 text-sm text-ink/80">
                <p class="font-medium text-ink">{{ auth()->user()->name }}</p>
                <p class="text-ink/70">{{ auth()->user()->alamat ?? '—' }}</p>
                <p class="text-ink/60">{{ auth()->user()->no_hp ?? '—' }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="mt-2 inline-block text-xs font-medium text-link underline decoration-link/35 underline-offset-4">Ubah di Edit Profil</a>
        </div>

        @foreach ($items as $item)
            <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
        @endforeach

        <div class="overflow-hidden rounded-xl border border-rule bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Produk</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                        <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr class="border-b border-rule/50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-ink">{{ $item->product->name }}</p>
                                @if ($item->variant)
                                    <p class="text-xs text-ink/55">Varian: {{ $item->variant->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-ink/80">{{ format_rp($lines[$index]['final_price_bersih']) }}</td>
                            <td class="px-4 py-3 text-ink/80">{{ $item->qty }}</td>
                            <td class="px-4 py-3 font-medium text-ink">
                                {{ format_rp($lines[$index]['final_price_bersih'] * $item->qty) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-rule bg-white p-4">
                <label for="platform" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Platform Marketplace</label>
                <select name="platform" id="platform" class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="toco">Toco</option>
                    <option value="shopee">Shopee</option>
                </select>

                <label for="scheme" class="mt-4 block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Skema Pembayaran</label>
                <select name="scheme" id="scheme" class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="fp">Full Payment (100%)</option>
                    <option value="dp">Down Payment (50%)</option>
                </select>

                <label for="payment_method" class="mt-4 block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Metode Bayar</label>
                <select name="payment_method" id="payment_method" class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    @foreach ($methods as $method)
                        <option value="{{ $method->code }}">{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-rule bg-white p-4">
                <label for="discount_type" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Diskon Koin / Voucher (pilih salah satu)</label>
                <select name="discount_type" id="discount_type" class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="none">Tanpa diskon</option>
                    <option value="coin">Pakai Koin (saldo {{ number_format($coinBalance) }} · maks {{ number_format($maxCoin) }})</option>
                    @if ($userVouchers->isNotEmpty())
                        <option value="voucher">Pakai Voucher</option>
                    @endif
                </select>

                <input type="number" name="coin_amount" id="coin_amount" min="0" max="{{ min($coinBalance, $maxCoin) }}"
                       placeholder="Jumlah koin (maks {{ number_format($maxCoin) }})"
                       class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">

                <select name="user_voucher_id" id="user_voucher_id" class="summary-trigger mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                    <option value="">— Pilih voucher —</option>
                    @foreach ($userVouchers as $userVoucher)
                        <option value="{{ $userVoucher->id }}">
                            {{ $userVoucher->voucher->code }} — potong {{ format_rp($userVoucher->voucher->nominal) }}
                        </option>
                    @endforeach
                </select>

                @error('coin_amount')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
                @error('user_voucher_id')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl border border-rule bg-white p-4">
            <label for="customer_note" class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Catatan untuk Mercatoria</label>
            <textarea name="customer_note" id="customer_note" rows="2" maxlength="500"
                      placeholder="Contoh: bubble wrap extra, packing rapi, mau bonus A/B"
                      class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45"></textarea>
        </div>

        <div class="rounded-xl border border-rule bg-white p-5">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ringkasan Pembayaran</h2>
            <dl class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-ink/70">Subtotal</dt><dd id="sum-subtotal" class="text-ink">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-danger"><dt>Diskon</dt><dd id="sum-discount">- Rp0</dd></div>
                <div class="flex justify-between font-medium"><dt class="text-ink">Total Harga Barang</dt><dd id="sum-total" class="text-ink">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-ink/55"><dt>Fee QRIS</dt><dd id="sum-fee-qris">Rp0</dd></div>
                <div class="flex justify-between text-lg font-semibold"><dt class="text-ink">Bayar di Web</dt><dd id="sum-tagihan" class="text-ink">{{ format_rp($initialSubtotal) }}</dd></div>
                <div class="flex justify-between text-ink/55"><dt>Estimasi CO Marketplace</dt><dd id="sum-estimasi">Rp0</dd></div>
                <div class="flex justify-between text-ink/55"><dt>Biaya CO Marketplace</dt><dd id="sum-fee-co">Rp0</dd></div>
            </dl>
            <p class="mt-3 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">
                Harga item sudah final (dibulatkan sekali saat tampil). Total tidak dibulatkan lagi.
            </p>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-black py-3.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
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

            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                btn.dataset.originalText = btn.textContent;
                btn.textContent = 'Memproses...';
            });
        })();
    </script>
@endsection
