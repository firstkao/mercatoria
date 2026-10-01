@extends('layouts.app', ['title' => 'Detail Pesanan ' . $order->order_number])

@section('content')
<section class="card" style="max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
        <h1 style="margin: 0; font-family: monospace;">#{{ $order->order_number }}</h1>
        <span class="badge {{ $order->statusBadgeClass() }}" style="font-size: 14px;">{{ $order->statusLabel() }}</span>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    {{-- ✅ BUG FIX: dulu view ini TIDAK menampilkan pesan error upload sama sekali.
         Controller mengirim back()->withErrors(['proof' => ...]) (mis. bukti masih
         diverifikasi admin / gagal simpan), tapi karena tidak ada blok error,
         user hanya melihat halaman diam tanpa tahu apa yang terjadi. --}}
    @if ($errors->any())
        <div style="background: var(--danger-bg, #fdecea); border-left: 4px solid var(--danger, #d9534f); padding: 15px; border-radius: 4px; margin-bottom: 25px;">
            <strong style="color: var(--danger, #d9534f); display: block; margin-bottom: 8px;">Periksa kembali</strong>
            <ul style="margin: 0; padding-left: 20px; font-size: 14px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(in_array($order->status, ['menunggu_pembayaran', 'pembayaran_gagal']))
        <div style="background: var(--warning-bg); border-left: 4px solid var(--warning); padding: 15px; border-radius: 4px; margin-bottom: 25px;">
            <strong style="color: var(--warning-text); display: block; margin-bottom: 10px;">Menunggu Pembayaran</strong>
            <p style="margin: 0; font-size: 14px; color: var(--warning-text);">
                {{-- ✅ BUG FIX: payment_deadline_at bisa NULL (order lama sebelum kolom
                     ini ada, atau order yang deadline-nya di-reset). Dulu dipanggil
                     ->timezone() langsung → "Call to a member function timezone() on
                     null" → halaman detail pesanan error 500 setiap kali status
                     menunggu_pembayaran/pembayaran_gagal. Lihat invoice.blade.php:92
                     yang sudah benar memakai guard null. --}}
                @if ($order->payment_deadline_at)
                    Segera lakukan pembayaran sebesar <strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong> sebelum batas waktu
                    <strong>{{ $order->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</strong>.
                @else
                    Segera lakukan pembayaran sebesar <strong>{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</strong> dan unggah bukti transfer secepatnya.
                @endif
            </p>
        </div>

        {{-- ✅ BUG FIX: nama route upload bukti adalah 'account.orders.upload' --}}
        {{-- (lihat routes/web.php). Sebelumnya di sini tertulis 'orders.proof'   --}}
        {{-- yang tidak pernah terdaftar → RouteNotFoundException (halaman detail  --}}
        {{-- pesanan error 500 setiap kali status menunggu pembayaran/gagal).      --}}
        <form action="{{ route('account.orders.upload', $order->order_number) }}" method="POST" enctype="multipart/form-data" class="panel stack" style="margin-bottom: 30px;">
            @csrf
            <h2>Upload Bukti Transfer</h2>
            <div class="field-row">
                <label class="field">
                    <span>Metode Pembayaran / Transfer Ke</span>
                    <select name="payment_method_id" required>
                        <option value="">Pilih Rekening Tujuan</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->label }}{{ $pm->account_number ? ' - '.$pm->account_number : '' }}{{ $pm->account_name ? ' ('.$pm->account_name.')' : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    <span>File Bukti (JPG/PNG/WEBP maks 4MB)</span>
                    <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required style="padding: 5px;">
                </label>
            </div>
            <button type="submit" class="button button--block" style="margin-top: 10px;">Upload & Konfirmasi</button>
        </form>
    @endif

    <div class="grid-2">
        <div>
            <h3>Daftar Barang</h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($order->items as $item)
                    <li style="border-bottom: 1px solid var(--border); padding: 10px 0;">
                        <strong>{{ $item->product_name_snapshot }}</strong><br>
                        <span class="muted" style="font-size: 13px;">Varian: {{ $item->variant_name_snapshot }} | Qty: {{ $item->quantity }}</span><br>
                        <span style="color: var(--accent-strong); font-weight: 600;">{{ \App\Support\PriceCalculator::formatRupiah($item->unit_price_idr) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div style="background: var(--bg); padding: 20px; border-radius: 8px; height: fit-content;">
            <h3 style="margin-top: 0;">Rincian Transaksi</h3>
            <dl class="details" style="margin-top: 0;">
                <div><dt>Subtotal Produk</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->subtotal_idr) }}</dd></div>

                @if($order->discount_idr > 0)
                    <div><dt>Potongan ({{ ucfirst($order->discount_type) }})</dt><dd style="color: var(--danger);">- {{ \App\Support\PriceCalculator::formatRupiah($order->discount_idr) }}</dd></div>
                @endif

                <hr style="border: 0; border-top: 1px dashed #ccc; width: 100%;">

                <div><dt>Total Pesanan</dt><dd><strong>{{ \App\Support\PriceCalculator::formatRupiah($order->total_idr) }}</strong></dd></div>

                @if($order->payment_scheme === 'DP')
                    <div><dt>DP Dibayar Sekarang</dt><dd style="font-size: 1.25rem; font-weight: bold; color: var(--accent-strong);">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd></div>
                    <div style="margin-top: 15px;"><dt>Sisa Pelunasan (Di {{ $order->marketplace->name ?? 'marketplace' }})</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->remaining_idr) }}</dd></div>
                    <div><dt>Biaya Admin {{ $order->marketplace->name ?? '' }}</dt><dd>{{ \App\Support\PriceCalculator::formatRupiah($order->marketplace_fee_idr) }}</dd></div>
                @else
                    <div><dt>Dibayar Sekarang (Termasuk Admin {{ $order->marketplace->name ?? '' }})</dt><dd style="font-size: 1.25rem; font-weight: bold; color: var(--accent-strong);">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</dd></div>
                @endif
            </dl>
        </div>
    </div>
</section>

{{-- ✅ BUG FIX: dulu view ini meng-include 'orders.partials.timeline' yang
     tidak pernah ada (folder resources/views/orders/partials/ tidak ada)
     → setiap customer membuka detail pesanan = 500 View does not exist.
     File timeline yang benar berada di orders/timeline.blade.php. --}}
@include('orders.timeline', ['order' => $order])
@endsection