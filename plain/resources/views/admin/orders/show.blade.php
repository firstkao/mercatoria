@extends('admin.layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number, 'back' => route('admin.orders.index')])

@section('actions')
    <x-admin.button :href="route('admin.orders.invoice', $order)" target="_blank" rel="noopener" size="sm">🖨️ Invoice</x-admin.button>
    <x-admin.button :href="route('admin.orders.packing-slip', $order)" target="_blank" rel="noopener" size="sm">📦 Packing Slip</x-admin.button>
    @include('admin.orders.partials.whatsapp-menu', ['order' => $order])
@endsection

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- ============================================================
             Catatan Pembeli
             ============================================================ --}}
        @if ($order->customer_note)
            <x-admin.card class="panel--warning">
                <h3 class="mb-2">📝 Catatan Pembeli</h3>
                <p class="note-body">{{ $order->customer_note }}</p>
            </x-admin.card>
        @endif

        {{-- ============================================================
             INFORMASI PESANAN — tanggal + penerima + pengiriman
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h2 class="m-0">Informasi Pesanan</h2>
                <span class="muted small">#{{ $order->order_number }}</span>
            </div>

            {{-- --- Tanggal & Status --- --}}
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

            {{-- --- Penerima & Pengiriman --- --}}
            <h3 class="mt-4">Penerima &amp; Pengiriman</h3>
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
                    <dd>{{ $order->recipient_phone ?? $order->user?->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>
                        @if ($order->user?->email)
                            <a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Alamat Pengiriman</dt>
                    <dd>
                        @php
                            $addressParts = array_filter([
                                $order->shipping_address ?? null,
                                $order->shipping_city ?? null,
                                $order->shipping_postal_code ?? null,
                            ]);
                        @endphp
                        @if (! empty($addressParts))
                            {{ implode(', ', $addressParts) }}
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
        </x-admin.card>

        {{-- ============================================================
             Rincian Barang
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
                    @endphp
                    <tr>
                        <td class="table__thumb">
                            @if ($item->variant?->image_path)
                                <img src="{{ $item->variant->imageUrl() }}" alt="">
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
             Ubah Status Pesanan (GLOBAL — set semua item)
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
             Ringkasan Biaya
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
             Riwayat Pelanggan
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
             Asal Kunjungan (opsional — butuh kolom tracking)
             ============================================================ --}}
        @php
            $hasTracking = ! empty($order->source)
                || ! empty($order->device_type)
                || ! empty($order->landing_page)
                || ! empty($order->referrer)
                || ! empty($order->session_page_views);
        @endphp

        @if ($hasTracking)
            <x-admin.card>
                <h3>Asal Kunjungan</h3>
                <dl class="deflist">
                    @if (! empty($order->source))
                        <div><dt>Asal</dt><dd>{{ $order->source }}</dd></div>
                    @endif
                    @if (! empty($order->device_type))
                        <div><dt>Jenis Perangkat</dt><dd>{{ $order->device_type }}</dd></div>
                    @endif
                    @if (! empty($order->session_page_views))
                        <div><dt>Kunjungan Halaman</dt><dd>{{ $order->session_page_views }} halaman</dd></div>
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
                </dl>
            </x-admin.card>
        @endif

        {{-- ============================================================
             Bukti Pembayaran
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
