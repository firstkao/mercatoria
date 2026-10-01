@extends('admin.layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number, 'back' => route('admin.orders.index')])

@section('actions')
    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener" class="btn btn--small">
        🖨️ Invoice
    </a>
    <a href="{{ route('admin.orders.packing-slip', $order) }}" target="_blank" rel="noopener" class="btn btn--small">
        📦 Packing Slip
    </a>
    @include('admin.orders.partials.whatsapp-menu', ['order' => $order])
@endsection

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- Catatan Pembeli --}}
        @if ($order->customer_note)
            <section class="panel" style="background:#fffbeb;border-color:#fde68a;">
                <h3 style="margin:0 0 8px;display:flex;align-items:center;gap:8px;">📝 Catatan Pembeli</h3>
                <p style="margin:0;white-space:pre-wrap;line-height:1.7;font-size:14px;">{{ $order->customer_note }}</p>
            </section>
        @endif

        {{-- Modul Verifikasi Bukti Pembayaran --}}
        @if($order->paymentProofs->isNotEmpty())
            {{-- ✅ PERAPIAN: pakai helper latestPaymentProof() (urut uploaded_at) --}}
            {{-- alih-alih ->last() pada collection tanpa ordering eksplisit.      --}}
            @php($latestProof = $order->latestPaymentProof() ?? $order->paymentProofs->last())
            <section class="panel">
                <div style="display: flex; justify-content: space-between;">
                    <h2>Bukti Pembayaran</h2>
                    <span class="badge">{{ strtoupper($latestProof->status) }}</span>
                </div>

                <div style="margin-top: 15px;">
                    <a href="{{ asset('storage/' . $latestProof->proof_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $latestProof->proof_path) }}" alt="Bukti Transfer" style="max-height: 400px; border-radius: 8px; border: 1px solid var(--border);">
                    </a>
                    <p class="muted">Ditransfer via: <strong>{{ $latestProof->method->label ?? 'Unknown' }}</strong> | Nominal: <strong>{{ \App\Support\PriceCalculator::formatRupiah($latestProof->amount_idr) }}</strong></p>
                </div>

                {{-- ✅ BUG FIX: Tombol approve/reject hanya untuk order yang
                     masih di gerbang pembayaran (menunggu_pembayaran/ditahan).
                     Kalau admin sudah menggeser status manual, submit dari sini
                     dulu akan kena 422 "Status order tidak dapat diubah". --}}
                @if($latestProof->status === 'pending' && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                        <form method="POST" action="{{ route('admin.payments.approve', $latestProof->id) }}" style="margin-bottom: 15px;">
                            @csrf
                            <button type="submit" class="btn btn--primary"
                                    onclick="return confirm('Setujui pembayaran ini? Akun akan jadi Customer.');">
                                Terima & Verifikasi
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.payments.reject', $latestProof->id) }}">
                            @csrf
                            <div class="field">
                                <span>Atau Tolak Pembayaran (masukkan alasan)</span>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="reject_reason" required
                                           placeholder="Contoh: Mutasi belum masuk / Gambar buram"
                                           style="flex: 1;">
                                    <button type="submit" class="btn btn--danger"
                                            onclick="return confirm('Tolak bukti pembayaran ini?');">
                                        Tolak Bukti
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        @endif

        {{-- Rincian Produk --}}
        <section class="panel">
            <h2>Rincian Barang</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
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
                </tbody>
            </table>
        </section>

        {{-- Catatan Internal Admin --}}
        @include('admin.orders.partials.notes', ['order' => $order])
    </div>

    <aside class="form-grid__side stack">
        <section class="panel">
            <h2>Ubah Status Pesanan</h2>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                @csrf
                <div class="field" style="margin-bottom: 15px;">
                    <select name="status">
                        @foreach (\App\Enums\OrderStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Peringatan: Mengubah ke Selesai akan otomatis mencairkan koin cashback.');">
                    Update Status
                </button>
            </form>
        </section>

        <section class="panel" style="background: var(--primary-bg);">
            <h3 style="margin-top: 0;">Ringkasan Biaya</h3>
            <dl class="deflist">
                <div><dt>Subtotal</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</dd></div>
                <div><dt>Potongan</dt><dd style="color: var(--danger);">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</dd></div>
                <div><dt>Bayar Skrg ({{ $order->payment_scheme }})</dt><dd style="font-size: 1.2rem; color: var(--accent-strong);">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd></div>
                <div><dt>Marketplace</dt><dd>{{ $order->marketplace?->name ?? '—' }} (Fee: {{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }})</dd></div>
                <div><dt>Sisa Nanti</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</dd></div>
                <div><dt>Estimasi Koin</dt><dd style="color: var(--warning);">+ {{ $order->coin_estimate }}</dd></div>
            </dl>
        </section>
    </aside>
</div>
@endsection