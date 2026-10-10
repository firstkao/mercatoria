@extends('admin.layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number, 'back' => route('admin.orders.index')])

@section('actions')
    <x-admin.button :href="route('admin.orders.invoice', $order)" target="_blank" rel="noopener" size="sm">🖨️ Invoice</x-admin.button>
    <x-admin.button :href="route('admin.orders.packing-slip', $order)" target="_blank" rel="noopener" size="sm">📦 Packing Slip</x-admin.button>
@endsection

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- ============================================================
             INFORMASI PESANAN — tanggal & status
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h2 class="m-0">Informasi Pesanan</h2>
                <span class="muted small">#{{ $order->order_number }}</span>
            </div>

            <dl class="deflist">
                <div>
                    <dt>Tanggal Order</dt>
                    <dd>
                        @if ($order->created_at)
                            {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                            <span class="muted small">({{ $order->created_at->diffForHumans() }})</span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Terakhir Update</dt>
                    <dd>
                        @if ($order->updated_at)
                            {{ $order->updated_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge {{ \App\Enums\OrderStatus::tryFrom($order->status)?->badgeClass() ?? '' }}">
                            {{ \App\Enums\OrderStatus::tryFrom($order->status)?->label() ?? $order->status }}
                        </span>
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        {{-- ============================================================
             PENERIMA, PENGIRIMAN & ASAL KUNJUNGAN
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h2 class="m-0">Penerima &amp; Pengiriman</h2>
                <div class="panel__head-actions">
                    <span class="muted small">#{{ $order->order_number }}</span>
                </div>
            </div>

            {{-- BARIS 1 — Penerima (kiri) + Alamat Pengiriman (kanan) --}}
            <div class="penerima-grid">
                {{-- Kolom Kiri: Penerima --}}
                <section>
                    <h3 class="mt-2 mb-2">👤 Penerima</h3>
                    <dl class="deflist">
                        <div>
                            <dt>Nama Penerima</dt>
                            <dd>
                                {{ $order->recipient_name ?? $order->user?->full_name ?? '—' }}
                                @if (
                                    $order->user?->full_name
                                    && $order->recipient_name
                                    && $order->recipient_name !== $order->user->full_name
                                )
                                    <span class="muted small">(akun: {{ $order->user->full_name }})</span>
                                @endif
                            </dd>
                        </div>

                       <div>
                            <dt>No. HP</dt>
                            <dd>
                                @php $phone = $order->recipient_phone ?? $order->user?->whatsapp; @endphp
                                @if ($phone)
                                    <a href="tel:{{ $phone }}">{{ $phone }}</a>
                        
                                    {{-- Tombol WA pemberitahuan barang tiba — hanya untuk status "sampai di Indonesia" --}}
                                    @php
                                        $waStatuses = ['dikirim_ke_indonesia', 'sampai_indonesia', 'tiba_di_indonesia'];
                                    @endphp
                                    @if (in_array($order->status, $waStatuses, true))
                                        @php
                                            $waNumber = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));
                                            $isDp = $order->payment_scheme === 'DP';
                        
                                            $checkoutLink = $isDp
                                                ? 'https://toco.id/listing/normal-checkout-1764687693020-4de1'
                                                : 'https://toco.id/listing/fast-checkout-1764687651698-e1b6';
                        
                                            $checkoutLabel = $isDp ? 'pelunasan' : 'checkout';
                        
                                            $waText = "Halo, barang pesanan *#{$order->order_number}* sudah tiba! 🎉\n\n"
                                                . "Silakan cek foto barang yang datang di WA Channel kami di sini: \n"
                                                . "👉 https://whatsapp.com/channel/0029VbB791V1XqucHUbqRg1N\n\n"
                                                . "Dan langsung lakukan {$checkoutLabel}/checkout lewat link berikut ya: \n"
                                                . "👉 {$checkoutLink}\n\n"
                                                . "⚠️ *Barang harus di-checkout dalam 7 hari setelah pemberitahuan ini. Jika lewat dari batas waktu, maka barang beserta pembayaran (DP/Penuh) hangus menjadi milik Mercatoria & tidak dapat diklaim kembali.*\n"
                                                . "Detail regulasi: https://mercatoria.id/faq\n\n"
                                                . "Terima kasih sudah berbelanja bersama kami! 🙏✨";
                                        @endphp
                        
                                        <a href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode($waText) }}"
                                           target="_blank" rel="noopener"
                                           class="wa-inline-btn"
                                           title="Kirim WA pemberitahuan barang tiba">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                            <span>Kirim WA</span>
                                        </a>
                                    @endif
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt>Email</dt>
                            <dd>
                                @php $email = $order->recipient_email ?? $order->user?->email; @endphp
                                @if ($email)
                                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Kolom Kanan: Alamat Pengiriman --}}
                <section>
                    <h3 class="mt-2 mb-2">📦 Alamat Pengiriman</h3>
                    <dl class="deflist">
                        <div>
                            <dt>Alamat</dt>
                            <dd>
                                @php
                                    $addressParts = array_filter([
                                        $order->shipping_address ?? null,
                                        $order->shipping_city ?? null,
                                        $order->shipping_province ?? null,
                                        $order->shipping_postal_code ?? null,
                                    ]);
                                @endphp
                                @if (! empty($addressParts))
                                    {!! nl2br(e(implode(",\n", $addressParts))) !!}
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </dd>
                        </div>

                        @if ($order->shipping_note)
                            <div>
                                <dt>Catatan Kirim</dt>
                                <dd>{{ $order->shipping_note }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>
            </div>

            {{-- BARIS 2 — Asal Kunjungan --}}
            @php
                $hasTracking = ! empty($order->source)
                    || ! empty($order->device_type)
                    || ! empty($order->landing_page)
                    || ! empty($order->referrer)
                    || ! empty($order->session_page_views)
                    || ! empty($order->ip_address);
            @endphp

            @if ($hasTracking)
                <h3 class="mt-4 mb-2">🌐 Asal Kunjungan</h3>
                <dl class="deflist tracking-grid">
                    @if (! empty($order->source))
                        <div>
                            <dt>Asal</dt>
                            <dd>
                                @php
                                    $sourceIcons = [
                                        'google'    => '🔍 Google',
                                        'instagram' => '📷 Instagram',
                                        'tiktok'    => '🎵 TikTok',
                                        'facebook'  => '📘 Facebook',
                                        'twitter'   => '🐦 Twitter',
                                        'shopee'    => '🛒 Shopee',
                                        'tokopedia' => '🟢 Tokopedia',
                                        'whatsapp'  => '💬 WhatsApp',
                                        'youtube'   => '▶️ YouTube',
                                        'direct'    => '🔗 Direct',
                                        'referral'  => '↗️ Referral',
                                    ];
                                @endphp
                                {{ $sourceIcons[strtolower($order->source)] ?? ucfirst($order->source) }}
                            </dd>
                        </div>
                    @endif

                    @if (! empty($order->device_type))
                        <div>
                            <dt>Jenis Perangkat</dt>
                            <dd>
                                @php
                                    $deviceIcons = [
                                        'mobile'  => '📱 Mobile',
                                        'tablet'  => '📱 Tablet',
                                        'desktop' => '💻 Desktop',
                                        'unknown' => '❓ Unknown',
                                    ];
                                @endphp
                                {{ $deviceIcons[strtolower($order->device_type)] ?? ucfirst($order->device_type) }}
                            </dd>
                        </div>
                    @endif

                    @if (! empty($order->session_page_views))
                        <div>
                            <dt>Kunjungan Halaman</dt>
                            <dd>{{ $order->session_page_views }} halaman dalam sesi ini</dd>
                        </div>
                    @endif

                    @if (! empty($order->landing_page))
                        <div>
                            <dt>Landing Page</dt>
                            <dd class="muted small" style="word-break: break-all;">
                                {{ $order->landing_page }}
                            </dd>
                        </div>
                    @endif

                    @if (! empty($order->referrer))
                        <div>
                            <dt>Referrer</dt>
                            <dd class="muted small" style="word-break: break-all;">
                                {{ $order->referrer }}
                            </dd>
                        </div>
                    @endif

                    @if (! empty($order->ip_address))
                        <div>
                            <dt>IP Address</dt>
                            <dd class="muted small">{{ $order->ip_address }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </x-admin.card>

        {{-- ============================================================
             CATATAN PEMBELI
             ============================================================ --}}
        @if ($order->customer_note)
            <x-admin.card class="panel--warning">
                <h3 class="mb-2">📝 Catatan Pembeli</h3>
                <p class="note-body">{{ $order->customer_note }}</p>
            </x-admin.card>
        @endif

        {{-- ============================================================
             RINCIAN BARANG
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h2 class="m-0">Rincian Barang</h2>
                <span class="muted small">{{ $order->items->count() }} item</span>
            </div>

            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th class="table__thumb"></th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </x-slot:head>

                @foreach ($order->items as $item)
                    @php
                        $effStatus = $item->item_status ?? $order->status;
                        $effLabel  = \App\Enums\OrderStatus::tryFrom($effStatus)?->label() ?? $effStatus;
                        $effBadge  = \App\Enums\OrderStatus::tryFrom($effStatus)?->badgeClass() ?? '';
                        $canCancel = \App\Enums\OrderStatus::rank($effStatus) < \App\Enums\OrderStatus::cancelLockRank();
                        $imgUrl    = $item->variant?->imageUrl();
                    @endphp
                    <tr>
                        <td class="table__thumb">
                            @if ($imgUrl)
                                <img src="{{ $imgUrl }}" alt="">
                            @else
                                <div class="thumb-empty">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                    </svg>
                                </div>
                            @endif
                        </td>

                        <td>
                            <strong>{{ $item->product_name_snapshot }}</strong>
                            <div class="muted small">Varian: {{ $item->variant_name_snapshot }}</div>
                        </td>

                        <td>x{{ $item->quantity }}</td>

                        <td>{{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}</td>

                        <td><strong>{{ \App\Support\PriceCalculator::formatRupiah($item->line_total_idr) }}</strong></td>

                        <td>
                            <form method="POST"
                                  action="{{ route('admin.orders.items.status', [$order, $item]) }}"
                                  class="item-status-form">
                                @csrf
                                @method('PUT')

                                <select name="item_status">
                                    <option value="" @selected($item->item_status === null)>
                                        Ikut status pesanan
                                    </option>
                                    @foreach (\App\Enums\OrderStatus::options() as $value => $label)
                                        @php
                                            $isFinal = in_array($value, ['dibatalkan', 'dana_dikembalikan'], true);
                                        @endphp
                                        <option value="{{ $value }}"
                                                @selected($item->item_status === $value)
                                                @disabled($isFinal && ! $canCancel)>
                                            {{ $label }}{{ ($isFinal && ! $canCancel) ? ' (sudah diproses)' : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn--small">Update</button>

                                <span class="item-status-form__badge">
                                    @if ($item->item_status !== null)
                                        <span class="badge {{ $effBadge }}">{{ $effLabel }}</span>
                                    @else
                                        <span class="text-muted">
                                            ikut: {{ \App\Enums\OrderStatus::tryFrom($order->status)?->label() ?? $order->status }}
                                        </span>
                                    @endif
                                </span>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            @if ($order->items->contains(fn ($i) => $i->item_status !== null))
                <p class="hint mt-4 mb-0">
                    <strong>Catatan:</strong> Beberapa item punya status sendiri.
                    Status pesanan = status item yang paling mundur.
                    Kalau kamu klik "Update Status (Semua Item)" di sidebar, semua status item akan di-reset.
                </p>
            @endif
        </x-admin.card>

    </div>

    <aside class="form-grid__side stack">

        {{-- ============================================================
             UBAH STATUS PESANAN
             ============================================================ --}}
        <x-admin.card>
            <h2>Ubah Status Pesanan</h2>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                @csrf
                <div class="field mb-4">
                    <select name="status">
                        @foreach (\App\Enums\OrderStatus::nextOptionsFor($order->status) as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <p class="hint mb-3">
                    Mengubah di sini akan <strong>set semua item</strong> ke status yang sama,
                    lalu status pesanan mengikuti otomatis.
                </p>

                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Set SEMUA item ke status ini?\n\nSemua override per-item akan di-reset. Mengubah ke Selesai akan mencairkan koin cashback.');">
                    Update Status (Semua Item)
                </button>
            </form>
        </x-admin.card>

        {{-- ============================================================
             RINGKASAN BIAYA
             ============================================================ --}}
        <x-admin.card class="panel--accent">
            <h3>Ringkasan Biaya</h3>
            <dl class="deflist">
                <div><dt>Subtotal</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</dd></div>
                <div><dt>Potongan</dt><dd class="text-danger">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</dd></div>
                <div><dt>Bayar Skrg ({{ $order->payment_scheme }})</dt><dd class="text-price">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd></div>
                <div><dt>Marketplace</dt><dd>{{ $order->marketplace?->name ?? '—' }} (Fee: {{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }})</dd></div>
                <div><dt>Sisa Nanti</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</dd></div>
                <div><dt>Estimasi Koin</dt><dd class="text-warning">+ {{ $order->coin_estimate }}</dd></div>
            </dl>
        </x-admin.card>

        {{-- ============================================================
             RIWAYAT PELANGGAN
             ============================================================ --}}
        @if (! empty($customerStats))
            <x-admin.card>
                <div class="panel__head">
                    <h3 class="m-0">Riwayat Pelanggan</h3>
                    @if ($customerStats['is_repeat'])
                        <x-admin.badge>REPEAT</x-admin.badge>
                    @endif
                </div>

                <dl class="deflist">
                    <div>
                        <dt>Total Pesanan</dt>
                        <dd><strong>{{ $customerStats['total_orders'] }}x</strong></dd>
                    </div>
                    <div>
                        <dt>Total Pendapatan</dt>
                        <dd class="text-price">
                            {{ \App\Support\PriceCalculator::formatRupiah($customerStats['total_revenue']) }}
                        </dd>
                    </div>
                    <div>
                        <dt>Rata-rata Pesanan</dt>
                        <dd>{{ \App\Support\PriceCalculator::formatRupiah($customerStats['avg_order']) }}</dd>
                    </div>
                    <div>
                        <dt>Pesanan Selesai</dt>
                        <dd>
                            {{ $customerStats['completed_orders'] }}x
                            @if ($customerStats['total_orders'] > 0)
                                <span class="muted small">
                                    ({{ (int) round($customerStats['completed_orders'] / $customerStats['total_orders'] * 100) }}%)
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Pelanggan Sejak</dt>
                        <dd>
                            @if ($customerStats['first_order_at'])
                                {{ $customerStats['first_order_at']->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($order->user?->email)
                    <a href="{{ route('admin.orders.index', ['q' => $order->user->email]) }}"
                       class="btn btn--outline btn--block mt-3">
                        Lihat Semua Pesanan Pelanggan →
                    </a>
                @endif
            </x-admin.card>
        @endif

        {{-- ============================================================
             BUKTI PEMBAYARAN
             ============================================================ --}}
        @if ($order->paymentProofs->isNotEmpty())
            @php($latestProof = $order->latestPaymentProof() ?? $order->paymentProofs->last())
            <x-admin.card>
                <div class="panel__head">
                    <h3 class="m-0">Bukti Pembayaran</h3>
                    <x-admin.badge>{{ strtoupper($latestProof->status) }}</x-admin.badge>
                </div>

                <div class="mt-4">
                    <a href="{{ $latestProof->url() }}" target="_blank" rel="noopener">
                        <img src="{{ $latestProof->url() }}"
                             alt="Bukti Transfer"
                             class="img-proof">
                    </a>
                </div>

                <dl class="deflist mt-4">
                    <div><dt>Metode</dt><dd>{{ $latestProof->method->label ?? 'Unknown' }}</dd></div>
                    <div><dt>Nominal</dt><dd class="text-price">{{ \App\Support\PriceCalculator::formatRupiah($latestProof->amount_idr) }}</dd></div>
                    <div><dt>Diunggah</dt><dd class="muted small">{{ $latestProof->uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') ?? '—' }}</dd></div>
                </dl>

                @if ($latestProof->status === 'pending' && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
                    <div class="divider-top stack stack--tight">
                        <form method="POST" action="{{ route('admin.payments.approve', $latestProof->id) }}">
                            @csrf
                            <button type="submit" class="btn btn--primary btn--block"
                                    onclick="return confirm('Setujui pembayaran ini? Akun akan jadi Customer.');">
                                Terima &amp; Verifikasi
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.payments.reject', $latestProof->id) }}" class="stack stack--tight">
                            @csrf
                            <input type="text" name="reject_reason" required
                                   class="input-compact"
                                   placeholder="Alasan tolak (mis. mutasi belum masuk)">
                            <button type="submit" class="btn btn--danger-outline btn--block"
                                    onclick="return confirm('Tolak bukti pembayaran ini?');">
                                Tolak Bukti
                            </button>
                        </form>
                    </div>
                @endif
            </x-admin.card>
        @endif

    </aside>
</div>
@endsection
