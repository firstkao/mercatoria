<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $order->order_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}?v=1">
</head>
<body class="print-page">
    <div class="print-toolbar">
        <div>
            <strong>Invoice {{ $order->order_number }}</strong>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.print()" class="print-btn print-btn--primary">🖨️ Print / Save PDF</button>
            <a href="{{ route('account.orders.show', $order->order_number) }}" class="print-btn">← Kembali</a>
        </div>
    </div>

    <div class="paper">
        <header class="paper__header">
            <div class="paper__brand">
                @if ($branding['has_logo'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['store_name'] }}" class="paper__logo">
                @else
                    <div class="paper__brand-name">{{ $branding['store_name'] }}</div>
                @endif
                <div class="paper__brand-info">
                    @if ($branding['store_address'])
                        <div>{{ $branding['store_address'] }}</div>
                    @endif
                    @if ($branding['contact_email'])
                        <div>{{ $branding['contact_email'] }}</div>
                    @endif
                    @if ($branding['contact_whatsapp'])
                        <div>{{ $branding['contact_whatsapp'] }}</div>
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

        <table class="paper-table">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center;">#</th>
                    <th>Item</th>
                    <th style="width:60px;text-align:center;">Qty</th>
                    <th style="width:110px;text-align:right;">Harga</th>
                    <th style="width:120px;text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $i => $item)
                    <tr>
                        <td style="text-align:center;">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name_snapshot }}</strong>
                            @if ($item->variant_name_snapshot)
                                <div class="paper-table__sub">Varian: {{ $item->variant_name_snapshot }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}</td>
                        <td style="text-align:right;">{{ \App\Support\PriceCalculator::formatRupiah($item->line_total_idr) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">Subtotal</td>
                    <td style="text-align:right;">{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</td>
                </tr>
                @if ($order->discount_idr > 0)
                    <tr>
                        <td colspan="4" style="text-align:right;">
                            Diskon
                            @if ($order->discount_type && $order->discount_type !== 'none')
                                ({{ ucfirst($order->discount_type) }})
                            @endif
                        </td>
                        <td style="text-align:right;">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</td>
                    </tr>
                @endif
                @if ($order->marketplace_fee_idr > 0)
                    <tr>
                        <td colspan="4" style="text-align:right;">Biaya Marketplace ({{ $order->marketplace?->name ?? '—' }})</td>
                        <td style="text-align:right;">{{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }}</td>
                    </tr>
                @endif
                <tr class="paper-table__total">
                    <td colspan="4" style="text-align:right;"><strong>Total</strong></td>
                    <td style="text-align:right;"><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->total_idr) }}</strong></td>
                </tr>
                @if ($order->payment_scheme === 'DP')
                    <tr>
                        <td colspan="4" style="text-align:right;">DP Dibayar Sekarang</td>
                        <td style="text-align:right;"><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;">Sisa Pelunasan</td>
                        <td style="text-align:right;">{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>

        <footer class="paper__footer">
            <div>Terima kasih telah berbelanja di {{ $branding['store_name'] }}.</div>
            <div class="paper__footer-meta">Dicetak: {{ $generatedAt->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</div>
        </footer>
    </div>
</body>
</html>