@extends('admin.layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number])

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">
        
        {{-- Modul Verifikasi Bukti Pembayaran --}}
        @if($order->paymentProofs->isNotEmpty())
            @php($latestProof = $order->paymentProofs->last())
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

                @if($latestProof->status === 'pending')
                    <form action="{{ route('admin.payments.verify', $latestProof) }}" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                        @csrf
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <button type="submit" name="action" value="accept" class="btn btn--primary" onclick="return confirm('Terima pembayaran ini dan ubah status akun menjadi Customer?');">Terima & Verifikasi</button>
                        </div>
                        <div class="field">
                            <label>Atau Tolak Pembayaran (Masukkan Alasan)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="reject_reason" placeholder="Contoh: Mutasi belum masuk / Gambar buram" style="flex: 1;">
                                <button type="submit" name="action" value="reject" class="btn btn--danger">Tolak Bukti</button>
                            </div>
                        </div>
                    </form>
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
    </div>

    <aside class="form-grid__side stack">
        {{-- Modul Update Status --}}
        <section class="panel">
            <h2>Ubah Status Pesanan</h2>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                @csrf
                <div class="field" style="margin-bottom: 15px;">
                    <select name="status">
                        <option value="menunggu_pembayaran" @selected($order->status === 'menunggu_pembayaran')>Menunggu Pembayaran</option>
                        <option value="ditahan" @selected($order->status === 'ditahan')>Ditahan (Menunggu Verifikasi)</option>
                        <option value="pembayaran_diterima" @selected($order->status === 'pembayaran_diterima')>Pembayaran Diterima</option>
                        <option value="sedang_diproses" @selected($order->status === 'sedang_diproses')>Sedang Diproses</option>
                        <option value="sampai_wh_cn" @selected($order->status === 'sampai_wh_cn')>Sampai WH China</option>
                        <option value="dikirim_ke_indonesia" @selected($order->status === 'dikirim_ke_indonesia')>Dikirim ke Indonesia</option>
                        <option value="bea_cukai" @selected($order->status === 'bea_cukai')>Bea Cukai</option>
                        <option value="sampai_wh_indonesia" @selected($order->status === 'sampai_wh_indonesia')>Sampai WH Indonesia</option>
                        <option value="selesai" @selected($order->status === 'selesai')>Selesai (Berikan Koin)</option>
                        <option value="dibatalkan" @selected($order->status === 'dibatalkan')>Dibatalkan</option>
                        <option value="dana_dikembalikan" @selected($order->status === 'dana_dikembalikan')>Dana Dikembalikan</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--block" onclick="return confirm('Peringatan: Mengubah ke Selesai akan otomatis mencairkan koin cashback.');">Update Status</button>
            </form>
        </section>

        {{-- Ringkasan Biaya --}}
        <section class="panel" style="background: var(--primary-bg);">
            <h3 style="margin-top: 0;">Ringkasan Biaya</h3>
            <dl class="deflist">
                <div><dt>Subtotal</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</dd></div>
                <div><dt>Potongan</dt><dd style="color: var(--danger);">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</dd></div>
                <div><dt>Bayar Skrg ({{ $order->payment_scheme }})</dt><dd style="font-size: 1.2rem; color: var(--accent-strong);">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd></div>
                <div><dt>Marketplace</dt><dd>{{ $order->marketplace->name }} (Fee: {{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }})</dd></div>
                <div><dt>Sisa Nanti</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</dd></div>
                <div><dt>Estimasi Koin</dt><dd style="color: var(--warning);">+ {{ $order->coin_estimate }}</dd></div>
            </dl>
        </section>
    </aside>
</div>
@endsection
