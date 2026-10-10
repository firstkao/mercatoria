<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Packing Slip {{ $order->order_number }}</title>
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

        /* === Catatan packing === */
        .packing-notes {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .packing-notes__block {
            padding: 12px 14px;
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 4px;
        }
        .packing-notes__label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #71717a;
            margin-bottom: 6px;
        }
        .packing-notes__body {
            margin: 0;
            font-size: 13px;
            color: #1a1a25;
            line-height: 1.55;
            white-space: pre-wrap;
        }
        .packing-notes__block--buyer {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .packing-notes__block--buyer .packing-notes__label { color: #92400e; }
        .packing-notes__block--buyer .packing-notes__body { color: #422006; }
    </style>
</head>
<body class="print-page">
    <div class="print-toolbar">
        <div>
            <strong>Packing Slip {{ $order->order_number }}</strong>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.print()" class="print-btn print-btn--primary">🖨️ Print / Save PDF</button>
            <a href="{{ route('admin.orders.show', $order) }}" class="print-btn">← Kembali</a>
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
                <h1 class="paper__doc-title">PACKING SLIP</h1>
                <table class="paper__doc-meta">
                    <tr><td>No. Order</td><td><strong>#{{ $order->order_number }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y') }}</td></tr>
                    <tr><td>Status</td><td>{{ $order->statusLabel() }}</td></tr>
                </table>
            </div>
        </header>

        <section class="paper__parties">
            <div class="paper__party grid-span-all">
                <div class="paper__party-label">KIRIM KE</div>
                <div class="paper__party-name">{{ $order->user?->full_name ?? '—' }}</div>
                <div class="paper__party-info">
                    @if ($order->user?->whatsapp)
                        <div>WhatsApp: +{{ $order->user->whatsapp }}</div>
                    @endif
                    @if ($order->user?->street_address)
                        <div>{{ $order->user->street_address }}</div>
                        <div>
                            Kec. {{ $order->user->district }}, {{ $order->user->city }}<br>
                            {{ $order->user->province }} {{ $order->user->postal_code }}
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <table class="paper-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th>Produk</th>
                    <th>Varian</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-qty-check">Cek ✓</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><strong>{{ $item->product_name_snapshot }}</strong></td>
                        <td>{{ $item->variant_name_snapshot ?: '—' }}</td>
                        <td class="text-center qty-lg">{{ $item->quantity }}</td>
                        <td class="text-center">
                            <span class="checkbox-box"></span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- === Catatan Packing === --}}
        <section class="paper__note">
            <div class="packing-notes">
                @if ($order->customer_note)
                    <div class="packing-notes__block packing-notes__block--buyer">
                        <span class="packing-notes__label">Catatan dari Pembeli</span>
                        <p class="packing-notes__body">{{ $order->customer_note }}</p>
                    </div>
                @endif
        </section>

        <footer class="paper__footer">
            <div>
                Terima kasih telah berbelanja di {{ $branding['store_name'] }}.
            </div>
            <div class="paper__footer-meta">
                Dicetak: {{ $generatedAt->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
            </div>
        </footer>
    </div>
</body>
</html>
