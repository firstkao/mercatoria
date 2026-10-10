<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $order->order_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}?v=1">
    <style>
        /* === Kontak brand (di bawah logo) === */
        .brand-contact {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.4;
        }
        .brand-contact__item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: inherit;
            text-decoration: none;
        }
        .brand-contact__icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            display: inline-block;
            vertical-align: middle;
        }
        .brand-contact__item--wa .brand-contact__icon { color: #25D366; }
        .brand-contact__item--email .brand-contact__icon { color: #0299e7; }
    </style>
</head>
<body class="print-page">
    {{-- No-print toolbar --}}
    <div class="print-toolbar">
        <div>
            <strong>Invoice {{ $order->order_number }}</strong>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.print()" class="print-btn print-btn--primary">🖨️ Print / Save PDF</button>
            <a href="{{ route('admin.orders.show', $order) }}" class="print-btn">← Kembali</a>
        </div>
    </div>

    {{-- Paper --}}
    <div class="paper">
        <header class="paper__header">
            <div class="paper__brand">
                @if ($branding['has_logo'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['store_name'] }}" class="paper__logo">
                @else
                    <div class="paper__brand-name">{{ $branding['store_name'] }}</div>
                @endif
                <div class="paper__brand-info brand-contact">
                    @if ($branding['contact_whatsapp'])
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $branding['contact_whatsapp']) }}"
                           class="brand-contact__item brand-contact__item--wa"
                           target="_blank" rel="noopener">
                            <svg class="brand-contact__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <span>{{ $branding['contact_whatsapp'] }}</span>
                        </a>
                    @endif

                    @if ($branding['contact_email'])
                        <a href="mailto:{{ $branding['contact_email'] }}"
                           class="brand-contact__item brand-contact__item--email">
                            <svg class="brand-contact__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <span>{{ $branding['contact_email'] }}</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="paper__doc">
                <h1 class="paper__doc-title">INVOICE</h1>
                <table class="paper__doc-meta">
                    <tr>
                        <td>No. Invoice</td>
                        <td><strong>#{{ $order->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>{{ $order->statusLabel() }}</td>
                    </tr>
                </table>
            </div>
        </header>

        <section class="paper__parties">
            <div class="paper__party">
                <div class="paper__party-label">DITAGIHKAN KEPADA</div>
                <div class="paper__party-name">{{ $order->user?->full_name ?? '—' }}</div>
                <div class="paper__party-info">
                    @if ($order->user?->email)
                        <div>{{ $order->user->email }}</div>
                    @endif
                    @if ($order->user?->whatsapp)
                        <div>+{{ $order->user->whatsapp }}</div>
                    @endif
                    @if ($order->user?->street_address)
                        <div>{{ $order->user->street_address }}</div>
                        <div>
                            {{ $order->user->district }}, {{ $order->user->city }}<br>
                            {{ $order->user->province }} {{ $order->user->postal_code }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="paper__party paper__party--right">
                <div class="paper__party-label">DETAIL PESANAN</div>
                <table class="paper__party-table">
                    <tr>
                        <td>Metode</td>
                        <td><strong>{{ $order->payment_scheme }}</strong></td>
                    </tr>
                    <tr>
                        <td>Marketplace</td>
                        <td>{{ $order->marketplace?->name ?? '—' }}</td>
                    </tr>
                    @if ($order->payment_deadline_at && in_array($order->status, ['menunggu_pembayaran', 'pembayaran_gagal']))
                        <tr>
                            <td>Batas Bayar</td>
                            <td>{{ $order->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</td>
                        </tr>
                    @endif
                    @if ($order->paid_at)
                        <tr>
                            <td>Dibayar</td>
                            <td>{{ $order->paid_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</td>
                        </tr>
                    @endif
                </table>
            </div>
        </section>

        {{-- Items --}}
        <table class="paper-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th>Item</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-money">Harga</th>
                    <th class="col-money-lg">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name_snapshot }}</strong>
                            @if ($item->variant_name_snapshot)
                                <div class="paper-table__sub">Varian: {{ $item->variant_name_snapshot }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}</td>
                        <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($item->line_total_idr) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Subtotal</td>
                    <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</td>
                </tr>
                @if ($order->discount_idr > 0)
                    <tr>
                        <td colspan="4" class="text-right">
                            Diskon
                            @if ($order->discount_type && $order->discount_type !== 'none')
                                ({{ ucfirst($order->discount_type) }})
                            @endif
                        </td>
                        <td class="text-right">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</td>
                    </tr>
                @endif
                @if ($order->marketplace_fee_idr > 0)
                    <tr>
                        <td colspan="4" class="text-right">Biaya Marketplace ({{ $order->marketplace?->name ?? '—' }})</td>
                        <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }}</td>
                    </tr>
                @endif
                <tr class="paper-table__total">
                    <td colspan="4" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->total_idr) }}</strong></td>
                </tr>
                @if ($order->payment_scheme === 'DP')
                    <tr>
                        <td colspan="4" class="text-right">DP Dibayar Sekarang</td>
                        <td class="text-right"><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right">Sisa Pelunasan</td>
                        <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>

        @if ($order->paymentProofs->isNotEmpty())
            @php($last = $order->paymentProofs->last())
            <section class="paper__note">
                <strong>Bukti Pembayaran:</strong>
                {{ $last->method?->label ?? '—' }} ·
                Status: {{ ucfirst($last->status) }} ·
                Diunggah: {{ $last->uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
            </section>
        @endif

        <footer class="paper__footer">
            <div>Terima kasih telah berbelanja di {{ $branding['store_name'] }}.</div>
            <div class="paper__footer-meta">Dicetak: {{ $generatedAt->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</div>
        </footer>
    </div>
</body>
</html>
