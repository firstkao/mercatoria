@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Back-Office</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Dashboard Admin</h1>
        </div>
        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink/50">{{ now()->format('d M Y') }} (WIB)</p>
    </div>

    {{-- 4 Stats Card (Design Spec 14.2) --}}
    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Total Pesanan</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pesanan Hari Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($todayOrders) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pendapatan Bulan Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ format_rp($monthRevenue) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">User Baru Minggu Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($newUsers) }}</p>
        </div>
    </div>

    {{-- Grafik 30 hari --}}
    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Grafik Penjualan 30 Hari</h2>
        @if (\Illuminate\Support\Facades\Route::has('office.reports.sales-chart'))
            <div id="sales-chart" class="mt-4 h-48" data-src="{{ route('office.reports.sales-chart') }}">
                <div class="flex h-full items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40">
                    <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Memuat grafik...</p>
                </div>
            </div>
            <script>
                (function () {
                    var el = document.getElementById('sales-chart');
                    if (!el) return;
                    fetch(el.dataset.src, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (rows) {
                            if (!rows || !rows.length) {
                                el.innerHTML = '<div class="flex h-full items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40"><p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Belum ada data</p></div>';
                                return;
                            }
                            var W = el.clientWidth || 800, H = 180, pad = 28;
                            var max = Math.max.apply(null, rows.map(function (d) { return Number(d.revenue) || 0; }));
                            if (max <= 0) max = 1;
                            var pts = [];
                            var dots = rows.map(function (d, i) {
                                var x = pad + (i / Math.max(1, rows.length - 1)) * (W - pad * 2);
                                var y = H - pad - (Number(d.revenue) || 0) / max * (H - pad * 2);
                                pts.push(x.toFixed(1) + ',' + y.toFixed(1));
                                var label = (d.date || '') + ': Rp ' + (Number(d.revenue) || 0).toLocaleString('id-ID');
                                return '<circle cx="' + x.toFixed(1) + '" cy="' + y.toFixed(1) + '" r="4" fill="#4FC3F7"><title>' + label + '</title></circle>';
                            }).join('');
                            var labels = [0, Math.floor(rows.length / 2), rows.length - 1].map(function (i) {
                                var d = rows[i]; if (!d) return '';
                                var x = pad + (i / Math.max(1, rows.length - 1)) * (W - pad * 2);
                                return '<text x="' + x + '" y="' + (H - 6) + '" text-anchor="middle" fill="#141B22" opacity="0.45" font-size="10" font-family="IBM Plex Mono,monospace">' + (d.date || '') + '</text>';
                            }).join('');
                            var grid = [0.25, 0.5, 0.75].map(function (f) {
                                var y = pad + f * (H - pad * 2);
                                return '<line x1="' + pad + '" y1="' + y + '" x2="' + (W - pad) + '" y2="' + y + '" stroke="#D8D2C4" stroke-width="0.5" stroke-dasharray="4,4"/>';
                            }).join('');
                            el.innerHTML = '<svg width="100%" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '">' + grid + '<polyline fill="none" stroke="#4FC3F7" stroke-width="2" points="' + pts.join(' ') + '"/>' + dots + labels + '</svg>';
                        })
                        .catch(function () {
                            el.innerHTML = '<div class="flex h-full items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40"><p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Gagal memuat</p></div>';
                        });
                })();
            </script>
        @else
            <div class="mt-4 flex h-48 items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Grafik menyusul</p>
            </div>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Pesanan Menunggu Aksi --}}
        <div class="rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pesanan Menunggu Aksi</h2>
            <div class="mt-3 space-y-2 text-sm">
                <a href="{{ route('office.payments.index') }}" class="flex items-center justify-between rounded-lg border border-rule px-3 py-2 transition-colors hover:border-ink">
                    <span class="text-ink/80">Pembayaran perlu verifikasi</span>
                    <strong class="text-ink">{{ $pendingVerifications }}</strong>
                </a>
                <a href="{{ route('office.orders.index', ['status' => 'menunggu_pembayaran']) }}" class="flex items-center justify-between rounded-lg border border-rule px-3 py-2 transition-colors hover:border-ink">
                    <span class="text-ink/80">Order baru</span>
                    <strong class="text-ink">{{ $newOrders }}</strong>
                </a>
            </div>
        </div>

        {{-- Top 5 Produk --}}
        <div class="rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Produk Terjual (Top 5)</h2>
            <ul class="mt-3 space-y-1 text-sm">
                @forelse ($topProducts as $product)
                    <li class="flex justify-between border-b border-rule/50 py-1.5">
                        <span class="text-ink/85">{{ $product->name }}</span>
                        <span class="font-mono text-xs text-ink/60">{{ $product->sold_count }} terjual</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-ink/45">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Notifikasi terbaru &mdash; slot kosong, data BE menyusul --}}
    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Notifikasi Admin Terbaru</h2>
        <div class="mt-4 flex h-24 items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Notifikasi menyusul</p>
        </div>
    </div>
@endsection
