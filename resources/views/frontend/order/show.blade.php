@extends('layouts.app')

@section('title', 'Pesanan '.$order->no_order.' — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pesanan</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Detail Pesanan</h1>

    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/50">Nomor Pesanan</p>
                <p class="mt-1 text-lg font-semibold tracking-wide text-ink">{{ $order->no_order }}</p>
                @if ($order->invoice_number)
                    <p class="mt-0.5 font-mono text-xs text-ink/55">{{ $order->invoice_number }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <x-order-status-badge :status="$order->status" size="lg" />
                <a href="{{ route('orders.invoice', $order->no_order) }}"
                   class="rounded-lg border border-rule px-3 py-1.5 text-sm text-ink transition-colors hover:border-ink">
                    Download Invoice PDF
                </a>
            </div>
        </div>

        <table class="mt-4 w-full text-sm">
            <thead class="border-b border-rule text-left">
                <tr>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Item</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                    <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    @php $snapshot = $item->snapshot; @endphp
                    <tr class="border-b border-rule/50">
                        <td class="py-2">
                            <p class="font-medium text-ink">{{ $snapshot->product_name }}</p>
                            @if ($snapshot->variant_name)
                                <p class="text-xs text-ink/55">Varian: {{ $snapshot->variant_name }}</p>
                            @endif
                            @if ($snapshot->store_name)
                                <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Store: {{ $snapshot->store_name }}</p>
                            @endif
                        </td>
                        <td class="py-2 text-ink/80">{{ format_rp($snapshot->final_price) }}</td>
                        <td class="py-2 text-ink/80">{{ $snapshot->qty }}</td>
                        <td class="py-2 text-ink/80">{{ format_rp($snapshot->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="mt-4 space-y-1 text-sm">
            <div class="flex justify-between"><dt class="text-ink/70">Subtotal</dt><dd class="text-ink">{{ format_rp($order->subtotal) }}</dd></div>
            @if ((float) $order->discount_amount > 0)
                <div class="flex justify-between text-danger">
                    <dt>Diskon {{ $order->discount_type === 'coin' ? 'Koin' : 'Voucher' }}</dt>
                    <dd>- {{ format_rp($order->discount_amount) }}</dd>
                </div>
            @endif
            <div class="flex justify-between font-medium"><dt class="text-ink">Total Harga Barang</dt><dd class="text-ink">{{ format_rp($order->total) }}</dd></div>
            <div class="flex justify-between text-ink/55"><dt>Fee QRIS</dt><dd>{{ format_rp($order->fee_qris) }}</dd></div>
            <div class="flex justify-between text-lg font-semibold"><dt class="text-ink">Bayar di Web</dt><dd class="text-ink">{{ format_rp($order->tagihan_web) }}</dd></div>
            @if ($order->estimasi_co_marketplace)
                <div class="flex justify-between text-ink/55"><dt>Estimasi CO Marketplace</dt><dd>{{ format_rp($order->estimasi_co_marketplace) }}</dd></div>
            @endif
        </dl>

        @if ($order->customer_note)
            <p class="mt-4 rounded-lg bg-paper p-3 text-sm text-ink/75">
                Catatan: {{ $order->customer_note }}
            </p>
        @endif

        @if ($order->note_to_customer)
            <div class="mt-4 rounded-lg border border-rule bg-white p-3">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pesan dari Admin</p>
                <p class="mt-1.5 text-sm text-ink/80">{{ $order->note_to_customer }}</p>
                @if ($order->note_to_customer_updated_at)
                    <p class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">
                        Diperbarui {{ $order->note_to_customer_updated_at->format('d/m/Y H:i') }}
                    </p>
                @endif
            </div>
        @endif

        @if ((float) $order->discount_amount > 0 && in_array($order->status, ['menunggu_pembayaran', 'ditahan'], true))
            <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                Koin atau voucher yang dipakai pada pesanan ini hangus permanen bila pembayaran gagal (PRD 9.3).
            </p>
        @endif
    </div>

    @if (in_array($order->status, ['menunggu_pembayaran', 'menunggu_denda'], true))
        @php $isDenda = $order->status === 'menunggu_denda'; @endphp
        <div class="mt-6 rounded-xl border border-rule bg-white p-6">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">{{ $isDenda ? 'Bayar & Unggah Bukti Denda' : 'Bayar & Unggah Bukti' }}</h2>
            <p class="mt-1 text-sm text-ink/70">
                {{ $isDenda ? 'Denda' : 'Tagihan' }}: <strong class="text-ink">{{ format_rp($isDenda ? $order->penalty_amount : $order->tagihan_web) }}</strong>
                @if ($isDenda && $order->penalty_deadline_at)
                    &middot; Batas waktu: {{ $order->penalty_deadline_at->format('d/m/Y H:i') }}
                @elseif ($order->payment_deadline_at)
                    &middot; Batas waktu: {{ $order->payment_deadline_at->format('d/m/Y H:i') }}
                @endif
            </p>

            <form method="POST" action="{{ route('orders.payments.store', $order->no_order) }}" enctype="multipart/form-data"
                  class="mt-4 space-y-3" id="upload-proof-form">
                @csrf
                <input type="file" name="proof" accept=".jpg,.jpeg,.png" required
                       class="block w-full text-sm text-ink/70 file:mr-3 file:rounded-lg file:border file:border-rule file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-ink">
                <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Format JPG/PNG, maksimal 1 MB. Hanya bisa diunggah 1 kali.</p>
                @error('proof')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                <button type="submit"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    UNGGAH BUKTI
                </button>
            </form>
            <script>
                (function () {
                    var form = document.getElementById('upload-proof-form');
                    if (!form) return;
                    form.addEventListener('submit', function () {
                        var btn = form.querySelector('button[type="submit"]');
                        if (!btn || btn.disabled) return;
                        btn.disabled = true;
                        btn.textContent = 'Memproses...';
                    });
                })();
            </script>
        </div>
    @elseif ($order->status === 'ditahan')
        <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-6">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-sky-900">Bukti Bayar Terkirim</h2>
            <p class="mt-1 text-sm text-sky-800">
                Menunggu verifikasi admin. Form upload terkunci sampai admin menyetujui atau menolak bukti kamu.
            </p>
        </div>
    @endif

    @if ($refundPending)
        <div class="mt-6 rounded-xl border border-rule bg-white p-4 text-sm text-ink/75">
            Permintaan refund sedang diproses admin.
        </div>
    @elseif ($canRequestRefund)
        <form method="POST" action="{{ route('orders.refund-request', $order->no_order) }}"
              class="mt-6 space-y-2 rounded-xl border border-rule bg-white p-4"
              data-confirm="Ajukan refund untuk pesanan ini?" data-confirm-title="Ajukan Refund" data-confirm-text="Ajukan">
            @csrf
            <label for="refund-reason" class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Ajukan Refund</label>
            <textarea id="refund-reason" name="reason" rows="2" maxlength="1000" required placeholder="Alasan refund"
                      class="w-full rounded-lg border border-rule px-3 py-2 text-sm">{{ old('reason') }}</textarea>
            @error('reason')
                <p class="text-xs text-danger">{{ $message }}</p>
            @enderror
            <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink hover:border-ink">Kirim Permintaan Refund</button>
        </form>
    @endif

    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Status</h2>
        <ol class="mt-3 space-y-3 border-l border-rule pl-4">
            @foreach ($order->statusLogs as $log)
                <li class="relative">
                    <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full {{ $loop->last ? 'bg-ink' : 'bg-rule' }}"></span>
                    <p class="text-sm {{ $loop->last ? 'font-semibold text-ink' : 'text-ink/75' }}">
                        <x-order-status-badge :status="$log->status" />
                    </p>
                    <p class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">
                        {{ $log->created_at->format('d/m/Y H:i') }} &middot; oleh {{ $log->changed_by }}
                        @if ($log->note) — {{ $log->note }} @endif
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
@endsection
