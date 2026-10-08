@extends('admin.layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number, 'back' => route('admin.orders.index')])

@section('actions')
    <x-admin.button :href="route('admin.orders.invoice', $order)" target="_blank" rel="noopener" size="sm">🖨️ Invoice</x-admin.button>
    <x-admin.button :href="route('admin.orders.packing-slip', $order)" target="_blank" rel="noopener" size="sm">📦 Packing Slip</x-admin.button>
    @include('admin.orders.partials.whatsapp-menu', ['order' => $order])
@endsection

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- Catatan Pembeli --}}
        @if ($order->customer_note)
            <x-admin.card class="panel--warning">
                <h3 class="mb-2">📝 Catatan Pembeli</h3>
                <p class="note-body">{{ $order->customer_note }}</p>
            </x-admin.card>
        @endif

        {{-- Modul Verifikasi Bukti Pembayaran --}}
        @if ($order->paymentProofs->isNotEmpty())
            {{-- ✅ PERAPIAN: pakai helper latestPaymentProof() (urut uploaded_at) --}}
            {{-- alih-alih ->last() pada collection tanpa ordering eksplisit.      --}}
            @php($latestProof = $order->latestPaymentProof() ?? $order->paymentProofs->last())
            <x-admin.card>
                <div class="panel__head">
                    <h2>Bukti Pembayaran</h2>
                    <x-admin.badge>{{ strtoupper($latestProof->status) }}</x-admin.badge>
                </div>

                <div class="mt-4">
                    <a href="{{ asset('storage/' . $latestProof->proof_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $latestProof->proof_path) }}" alt="Bukti Transfer" class="img-proof">
                    </a>
                    <p class="muted">Ditransfer via: <strong>{{ $latestProof->method->label ?? 'Unknown' }}</strong> | Nominal: <strong>{{ \App\Support\PriceCalculator::formatRupiah($latestProof->amount_idr) }}</strong></p>
                </div>

                {{-- ✅ BUG FIX: Tombol approve/reject hanya untuk order yang
                     masih di gerbang pembayaran (menunggu_pembayaran/ditahan).
                     Kalau admin sudah menggeser status manual, submit dari sini
                     dulu akan kena 422 "Status order tidak dapat diubah". --}}
                @if ($latestProof->status === 'pending' && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
                    <div class="divider-top">
                        <form method="POST" action="{{ route('admin.payments.approve', $latestProof->id) }}" class="mb-4">
                            @csrf
                            <button type="submit" class="btn btn--primary"
                                    onclick="return confirm('Setujui pembayaran ini? Akun akan jadi Customer.');">
                                Terima &amp; Verifikasi
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.payments.reject', $latestProof->id) }}">
                            @csrf
                            <div class="field">
                                <span>Atau Tolak Pembayaran (masukkan alasan)</span>
                                <div class="actions">
                                    <input type="text" name="reject_reason" required class="flex-1"
                                           placeholder="Contoh: Mutasi belum masuk / Gambar buram">
                                    <button type="submit" class="btn btn--danger"
                                            onclick="return confirm('Tolak bukti pembayaran ini?');">
                                        Tolak Bukti
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </x-admin.card>
        @endif

        {{-- Rincian Produk --}}
        <x-admin.card>
            <h2>Rincian Barang</h2>
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                    </tr>
                </x-slot:head>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name_snapshot }}</strong>
                            <div class="muted small">Varian: {{ $item->variant_name_snapshot }}</div>
                        </td>
                        <td>x{{ $item->quantity }}</td>
                        <td>{{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}</td>
                        <td><strong>{{ \App\Support\PriceCalculator::formatRupiah($item->line_total_idr) }}</strong></td>
                    </tr>
                @endforeach
            </x-admin.table>
        </x-admin.card>

        {{-- Catatan Internal Admin --}}
        @include('admin.orders.partials.notes', ['order' => $order])
    </div>

    <aside class="form-grid__side stack">
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
                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Peringatan: Mengubah ke Selesai akan otomatis mencairkan koin cashback.');">
                    Update Status
                </button>
            </form>
        </x-admin.card>

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
    </aside>
</div>
@endsection
