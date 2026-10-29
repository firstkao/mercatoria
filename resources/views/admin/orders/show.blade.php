@extends('admin.layouts.admin')

@section('title', 'Order '.$order->no_order)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">{{ $order->no_order }}</h1>
            @if ($order->invoice_number)
                <p class="mt-0.5 font-mono text-xs text-ink/55">{{ $order->invoice_number }}</p>
            @endif
            <p class="mt-1.5 text-sm text-ink/70">
                {{ $order->user->name ?? '-' }}
                @if ($order->user?->email) · {{ $order->user->email }} @endif
                @if ($order->user?->no_hp) · {{ $order->user->no_hp }} @endif
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-order-status-badge :status="$order->status" size="lg" />
                <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">{{ strtoupper($order->scheme) }} · {{ strtoupper($order->payment_method) }} · {{ ucfirst($order->platform) }}</span>
            </div>
        </div>
        <a href="{{ route('orders.invoice', $order->no_order) }}" target="_blank"
           class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">
            Invoice PDF
        </a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-[1fr_340px]">
        {{-- KONTEN UTAMA --}}
        <div class="space-y-4">
            {{-- Items (snapshot) --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Item (snapshot)</h2>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-rule text-left">
                            <tr>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Item</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Qty</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr class="border-b border-rule/50">
                                    <td class="py-1.5">
                                        <p class="font-medium text-ink">{{ $item->snapshot->product_name }}</p>
                                        @if ($item->snapshot->variant_name)
                                            <p class="text-xs text-ink/55">Varian: {{ $item->snapshot->variant_name }}</p>
                                        @endif
                                        @if ($item->snapshot->store_name)
                                            <p class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45">Store: {{ $item->snapshot->store_name }}</p>
                                        @endif
                                    </td>
                                    <td class="py-1.5 text-ink/80">{{ $item->snapshot->qty }}</td>
                                    <td class="py-1.5 text-ink/80">{{ format_rp($item->snapshot->final_price) }}</td>
                                    <td class="py-1.5 text-ink/80">{{ format_rp($item->snapshot->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ringkasan</h2>
                <dl class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Subtotal</dt><dd class="text-ink">{{ format_rp($order->subtotal) }}</dd></div>
                    @if ((float) $order->discount_amount > 0)
                        <div class="flex justify-between border-b border-rule/50 py-1 text-danger">
                            <dt>Diskon {{ $order->discount_type === 'coin' ? 'Koin' : 'Voucher' }}</dt>
                            <dd>- {{ format_rp($order->discount_amount) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Fee QRIS</dt><dd class="text-ink">{{ format_rp($order->fee_qris) }}</dd></div>
                    @if ($order->estimasi_co_marketplace)
                        <div class="flex justify-between border-b border-rule/50 py-1"><dt class="text-ink/70">Estimasi CO Marketplace</dt><dd class="text-ink">{{ format_rp($order->estimasi_co_marketplace) }}</dd></div>
                    @endif
                    <div class="flex justify-between border-b border-rule/50 py-1 font-medium"><dt class="text-ink">Total</dt><dd class="text-ink">{{ format_rp($order->total) }}</dd></div>
                    <div class="flex justify-between py-1 text-base font-semibold"><dt class="text-ink">Tagihan Web</dt><dd class="text-ink">{{ format_rp($order->tagihan_web) }}</dd></div>
                </dl>
                @if ($order->customer_note)
                    <p class="mt-3 rounded-lg bg-paper p-2.5 text-xs text-ink/70">Catatan pembeli: {{ $order->customer_note }}</p>
                @endif
            </div>

            {{-- Pesan dari Admin --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pesan dari Admin</h2>
                @if ($order->note_to_customer)
                    <div class="mt-2 rounded-lg border border-rule bg-paper px-3 py-2">
                        <p class="text-sm text-ink/80">{{ $order->note_to_customer }}</p>
                        @if ($order->note_to_customer_updated_at)
                            <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">Diperbarui {{ $order->note_to_customer_updated_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                @endif
                <form method="POST" action="{{ route('office.orders.note-to-customer', $order->no_order) }}" class="mt-3">
                    @csrf
                    <textarea name="note_to_customer" rows="3" maxlength="1000" required
                              placeholder="Pesan untuk customer (maks 1000 karakter)..."
                              class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">{{ old('note_to_customer', $order->note_to_customer) }}</textarea>
                    @error('note_to_customer')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
                    <button class="mt-2 rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Kirim Pesan</button>
                </form>
            </div>

            {{-- Refund --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Refund</h2>

                @php($refunds = \App\Models\Refund::where('order_id', $order->id)->latest()->get())
                @if ($refunds->isNotEmpty())
                    <div class="mt-2 space-y-2">
                        @foreach ($refunds as $refund)
                            <div class="rounded-lg border border-rule px-3 py-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/55">{{ strtoupper($refund->status) }}</span>
                                        <span class="ml-2 font-semibold text-ink">{{ format_rp($refund->amount) }}</span>
                                    </div>
                                    @if ($refund->status === 'requested')
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('office.refunds.complete', $refund) }}" class="inline">
                                                @csrf
                                                <button class="rounded-lg bg-[#43A047] px-3 py-1 text-xs font-semibold text-white">Proses Refund</button>
                                            </form>
                                            <form method="POST" action="{{ route('office.refunds.reject', $refund) }}" class="inline">
                                                @csrf
                                                <input type="text" name="rejection_reason" placeholder="Alasan tolak" required maxlength="1000"
                                                       class="w-40 rounded border border-rule px-2 py-1 text-xs">
                                                <button class="rounded-lg border border-danger/40 px-3 py-1 text-xs font-semibold text-danger">Tolak</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-ink/65">Alasan: {{ $refund->reason }}</p>
                                @if ($refund->rejection_reason)
                                    <p class="mt-0.5 text-xs text-danger">Ditolak: {{ $refund->rejection_reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    {{-- Ajukan Refund Full --}}
                    @if (\Illuminate\Support\Facades\Route::has('office.orders.refund.full'))
                        <form method="POST" action="{{ route('office.orders.refund.full', $order->no_order) }}" class="rounded-lg border border-rule p-3">
                            @csrf
                            <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Ajukan Refund (Full)</p>
                            <textarea name="reason" rows="2" required maxlength="1000" placeholder="Alasan refund..."
                                      class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                            <button class="mt-2 rounded-lg bg-black px-4 py-1.5 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Ajukan</button>
                        </form>
                    @endif

                    {{-- Ajukan Refund Partial --}}
                    @if (\Illuminate\Support\Facades\Route::has('office.orders.refund.partial'))
                        <form method="POST" action="{{ route('office.orders.refund.partial', $order->no_order) }}" class="rounded-lg border border-rule p-3">
                            @csrf
                            <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Ajukan Refund Partial</p>
                            <input type="number" step="0.01" name="amount" min="0.01" required placeholder="Nominal"
                                   class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                            <textarea name="reason" rows="2" required maxlength="1000" placeholder="Alasan refund..."
                                      class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                            <button class="mt-2 rounded-lg bg-black px-4 py-1.5 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Ajukan</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Pembayaran --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pembayaran</h2>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-rule text-left">
                            <tr>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tipe</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nominal</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Bukti</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                                <th class="py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->payments as $payment)
                                <tr class="border-b border-rule/50">
                                    <td class="py-1.5 text-ink/80">{{ $payment->type }}</td>
                                    <td class="py-1.5 text-ink/80">{{ format_rp($payment->amount) }}</td>
                                    <td class="py-1.5">
                                        @if ($payment->proof_path ?? $payment->file_path ?? null)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($payment->proof_path ?? $payment->file_path) }}" target="_blank" class="text-link underline decoration-link/35 underline-offset-4">Lihat</a>
                                        @else
                                            <span class="text-ink/40">—</span>
                                        @endif
                                    </td>
                                    <td class="py-1.5"><x-order-status-badge :status="$payment->status === 'verified' ? 'selesai' : ($payment->status === 'rejected' ? 'pembayaran_gagal' : 'menunggu_pembayaran')" /></td>
                                    <td class="py-1.5">
                                        @if ($payment->status === 'pending')
                                            <form method="POST" action="{{ route('office.payments.verify', [$order->no_order, $payment->id]) }}" class="inline">
                                                @csrf
                                                <button class="text-[#43A047] hover:underline">Verifikasi</button>
                                            </form>
                                            <form method="POST" action="{{ route('office.payments.reject', [$order->no_order, $payment->id]) }}" class="ml-2 inline">
                                                @csrf
                                                <button class="text-danger hover:underline">Tolak</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-ink/45">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Status</h2>
                <ol class="mt-3 space-y-2 border-l border-rule pl-4">
                    @foreach ($order->statusLogs as $log)
                        <li class="relative">
                            <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full {{ $loop->last ? 'bg-ink' : 'bg-rule' }}"></span>
                            <p class="text-sm {{ $loop->last ? 'font-semibold text-ink' : 'text-ink/75' }}">{{ order_status_label($log->status) }}</p>
                            <p class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45">
                                {{ $log->created_at->format('d/m/Y H:i') }} · oleh {{ $log->changed_by }}
                                @if ($log->note) — {{ $log->note }} @endif
                            </p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-4">
            {{-- Ubah Status --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ubah Status</h2>
                <form method="POST" action="{{ route('office.orders.status', $order->no_order) }}" class="mt-2 space-y-2">
                    @csrf @method('PATCH')
                    <select name="status" class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                        @foreach (['menunggu_pembayaran', 'ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen', 'menunggu_denda'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ order_status_label($status) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" placeholder="Alasan (opsional)" maxlength="255"
                           class="w-full rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                    <button class="w-full rounded-lg bg-black py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan</button>
                </form>
            </div>

            {{-- Settlement --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Catat Pelunasan</h2>
                <form method="POST" action="{{ route('office.orders.settlements', $order->no_order) }}" class="mt-2 flex gap-2">
                    @csrf
                    <input type="number" step="0.01" name="amount" required placeholder="Nominal"
                           class="flex-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                    <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Catat</button>
                </form>
            </div>

            {{-- Denda & Hangus --}}
            <div class="rounded-xl border border-rule bg-white p-4">
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Denda &amp; Hangus</h2>
                @if ($order->status === 'hangus')
                    <form method="POST" action="{{ route('office.orders.unlock-penalty', $order->no_order) }}" class="mt-2 flex gap-2">
                        @csrf
                        <input type="number" name="penalty_amount" min="0" placeholder="Nominal denda (opsional)"
                               class="flex-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                        <button class="rounded-lg bg-black px-3 py-2 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Buka Kunci</button>
                    </form>
                @endif
                @if (in_array($order->status, ['hangus', 'menunggu_denda'], true))
                    <form method="POST" action="{{ route('office.orders.hangus-permanent', $order->no_order) }}" class="mt-2"
                          onsubmit="return confirm('Tetapkan Hangus Permanen? Tidak dapat dibuka lagi.')">
                        @csrf
                        <button class="w-full rounded-lg border border-danger/40 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">
                            Hangus Permanen
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
