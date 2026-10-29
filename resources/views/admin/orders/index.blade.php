@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Orders</h1>
        </div>
        <a href="{{ route('office.orders.inject') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah Pesanan (Inject)</a>
    </div>

    {{-- Filter + Search --}}
    <form method="GET" class="mt-4 flex flex-wrap items-end gap-2">
        <div>
            <label for="filter-status" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Status</label>
            <select name="status" id="filter-status" onchange="this.form.submit()"
                    class="mt-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="">Semua Status</option>
                @foreach (['menunggu_pembayaran', 'ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen', 'menunggu_denda'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ order_status_label($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter-q" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Cari</label>
            <input type="text" name="q" id="filter-q" value="{{ request('q') }}" placeholder="No order / nama..."
                   class="mt-1 w-56 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
        </div>
        <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Terapkan</button>
        @if (request()->has('status') || request()->has('q'))
            <a href="{{ route('office.orders.index') }}" class="px-2 py-2 text-sm text-link underline decoration-link/35 underline-offset-4">Reset</a>
        @endif
    </form>

    <form method="POST" action="{{ route('office.orders.bulk-status') }}" class="mt-4">
        @csrf
        <div class="overflow-x-auto rounded-xl border border-rule bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="w-10 px-3 py-2"></th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">No Order</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Customer</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Total</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tanggal</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-b border-rule/50">
                            <td class="px-3 py-2"><input type="checkbox" name="no_orders[]" value="{{ $order->no_order }}" class="rounded border-rule"></td>
                            <td class="px-3 py-2 font-medium text-ink">{{ $order->no_order }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ $order->user->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ format_rp($order->total) }}</td>
                            <td class="px-3 py-2"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-3 py-2 font-mono text-xs text-ink/60">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('office.orders.show', $order->no_order) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-ink/45">Tidak ada order ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <label class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Ubah status terpilih:</label>
            <select name="status" class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                @foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai', 'pembayaran_gagal', 'hangus'] as $status)
                    <option value="{{ $status }}">{{ order_status_label($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Terapkan</button>
        </div>
    </form>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
