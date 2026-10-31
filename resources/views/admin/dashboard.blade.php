@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Back-Office</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Dashboard Admin</h1>
        </div>
        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink/50">{{ now()->format('d M Y') }} (WIB)</p>
    </div>

    {{-- ─── 4 Stats Card (DS 14.2) — Desktop 4 kolom, Mobile 2×2 ─── --}}
    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Total Pesanan</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($totalOrders) }}</p>
            <p class="mt-1 text-xs">
                <span class="inline-flex items-center gap-0.5 {{ ($orderGrowthPercent ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    <i class="bi bi-caret-{{ ($orderGrowthPercent ?? 0) >= 0 ? 'up' : 'down' }}-fill" style="font-size:0.65rem;"></i>
                    {{ ($orderGrowthPercent ?? 0) >= 0 ? '+' : '' }}{{ $orderGrowthPercent ?? 0 }}% bln ini
                </span>
            </p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pesanan Hari Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($todayOrders) }}</p>
            <p class="mt-1 text-xs">
                <span class="inline-flex items-center gap-0.5 {{ ($todayGrowth ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    <i class="bi bi-caret-{{ ($todayGrowth ?? 0) >= 0 ? 'up' : 'down' }}-fill" style="font-size:0.65rem;"></i>
                    {{ ($todayGrowth ?? 0) >= 0 ? '+' : '' }}{{ $todayGrowth ?? 0 }} dr kemarin
                </span>
            </p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pendapatan Bln Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ format_rp($monthRevenue) }}</p>
            <p class="mt-1 text-xs">
                <span class="inline-flex items-center gap-0.5 {{ ($revenueGrowthPercent ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    <i class="bi bi-caret-{{ ($revenueGrowthPercent ?? 0) >= 0 ? 'up' : 'down' }}-fill" style="font-size:0.65rem;"></i>
                    {{ ($revenueGrowthPercent ?? 0) >= 0 ? '+' : '' }}{{ $revenueGrowthPercent ?? 0 }}% bln ini
                </span>
            </p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">User Baru Mgg Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($newUsers) }}</p>
            <p class="mt-1 text-xs">
                <span class="inline-flex items-center gap-0.5 {{ ($userGrowthPercent ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    <i class="bi bi-caret-{{ ($userGrowthPercent ?? 0) >= 0 ? 'up' : 'down' }}-fill" style="font-size:0.65rem;"></i>
                    {{ ($userGrowthPercent ?? 0) >= 0 ? '+' : '' }}{{ $userGrowthPercent ?? 0 }}% mgg ini
                </span>
            </p>
        </div>
    </div>

    {{-- ─── Grafik 30 Hari (DS 14.2) ─── --}}
    <div class="mt-5 rounded-xl border border-rule bg-white p-5">
        <div class="flex items-center gap-2">
            <i class="bi bi-graph-up text-ink/60" style="font-size:0.95rem;"></i>
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Grafik Penjualan 30 Hari Terakhir</h2>
        </div>
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
                            var yLabels = [0, 0.5, 1].map(function (f) {
                                var y = H - pad - f * (H - pad * 2);
                                var val = Math.round(max * f);
                                var formatted = val >= 1000000 ? (val / 1000000).toFixed(1) + 'jt' : val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val;
                                return '<text x="' + (pad - 6) + '" y="' + (y + 3) + '" text-anchor="end" fill="#141B22" opacity="0.45" font-size="10" font-family="IBM Plex Mono,monospace">Rp ' + formatted + '</text>';
                            }).join('');
                            el.innerHTML = '<svg width="100%" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '">' + yLabels + grid + '<polyline fill="none" stroke="#4FC3F7" stroke-width="2" points="' + pts.join(' ') + '"/>' + dots + labels + '</svg>';
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

    {{-- ─── Menunggu Aksi + Top 5 Produk (DS 14.2) ─── --}}
    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Menunggu Aksi --}}
        <div class="rounded-xl border border-rule bg-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-circle text-amber-600" style="font-size:1.1rem;"></i>
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pesanan Menunggu Aksi</h2>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @php
                    $aksi = $pesananMenungguAksi ?? [
                        'verifikasi_bukti' => 0, 'refund_pending' => 0, 'spammer_baru' => 0,
                        'order_baru' => 0, 'hampir_hangus' => 0,
                    ];
                @endphp

                <a href="{{ route('office.payments.index') }}"
                   class="flex items-center justify-between rounded-xl border border-rule px-5 py-4 transition-colors hover:border-ink hover:shadow-sm">
                    <span class="flex items-center gap-3.5 text-ink/80">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-50">
                            <i class="bi bi-receipt text-blue-500" style="font-size:1.05rem;"></i>
                        </span>
                        <span class="text-sm">Bukti Bayar Verif</span>
                    </span>
                    <strong class="rounded-full bg-blue-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700">
                        {{ $aksi['verifikasi_bukti'] ?? 0 }} order
                    </strong>
                </a>

                <a href="{{ route('office.refunds.index') }}"
                   class="flex items-center justify-between rounded-xl border border-rule px-5 py-4 transition-colors hover:border-ink hover:shadow-sm">
                    <span class="flex items-center gap-3.5 text-ink/80">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-50">
                            <i class="bi bi-arrow-counterclockwise text-amber-500" style="font-size:1.05rem;"></i>
                        </span>
                        <span class="text-sm">Refund Pending</span>
                    </span>
                    <strong class="rounded-full bg-amber-50 px-3.5 py-1.5 text-xs font-semibold text-amber-700">
                        {{ $aksi['refund_pending'] ?? 0 }} order
                    </strong>
                </a>

                <a href="{{ route('office.spammers.index') }}"
                   class="flex items-center justify-between rounded-xl border border-rule px-5 py-4 transition-colors hover:border-ink hover:shadow-sm">
                    <span class="flex items-center gap-3.5 text-ink/80">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-50">
                            <i class="bi bi-shield-exclamation text-red-500" style="font-size:1.05rem;"></i>
                        </span>
                        <span class="text-sm">Spammer Baru</span>
                    </span>
                    <strong class="rounded-full bg-red-50 px-3.5 py-1.5 text-xs font-semibold text-red-700">
                        {{ $aksi['spammer_baru'] ?? 0 }} akun
                    </strong>
                </a>

                <a href="{{ route('office.orders.index', ['status' => 'menunggu_pembayaran']) }}"
                   class="flex items-center justify-between rounded-xl border border-rule px-5 py-4 transition-colors hover:border-ink hover:shadow-sm">
                    <span class="flex items-center gap-3.5 text-ink/80">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-50">
                            <i class="bi bi-bag-check text-green-600" style="font-size:1.05rem;"></i>
                        </span>
                        <span class="text-sm">Order Baru Masuk</span>
                    </span>
                    <strong class="rounded-full bg-green-50 px-3.5 py-1.5 text-xs font-semibold text-green-700">
                        {{ $aksi['order_baru'] ?? 0 }} order
                    </strong>
                </a>

                <a href="{{ route('office.orders.index') }}"
                   class="flex items-center justify-between rounded-xl border border-rule px-5 py-4 transition-colors hover:border-ink hover:shadow-sm">
                    <span class="flex items-center gap-3.5 text-ink/80">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-orange-50">
                            <i class="bi bi-clock-history text-orange-500" style="font-size:1.05rem;"></i>
                        </span>
                        <span class="text-sm">Hampir Hangus (7j)</span>
                    </span>
                    <strong class="rounded-full bg-orange-50 px-3.5 py-1.5 text-xs font-semibold text-orange-700">
                        {{ $aksi['hampir_hangus'] ?? 0 }} order
                    </strong>
                </a>
            </div>
        </div>

        {{-- Top 5 Produk Terjual Bln Ini --}}
        <div class="rounded-xl border border-rule bg-white p-6">
            <div class="flex items-center gap-2">
                <i class="bi bi-trophy text-amber-500" style="font-size:1.1rem;"></i>
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Top 5 Produk Terjual Bln Ini</h2>
            </div>
            <ol class="mt-5 space-y-1">
                @forelse ($topProducts as $i => $product)
                    <li class="flex items-center justify-between border-b border-rule/50 py-2.5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : ($i === 2 ? 'bg-orange-50 text-orange-600' : 'bg-paper text-ink/50')) }} text-xs font-semibold">
                                {{ $i + 1 }}
                            </span>
                            <span class="text-sm font-medium text-ink">
                                @if($product->game?->name)
                                    <span class="font-mono text-[10px] text-ink/50">[{{ \Illuminate\Support\Str::of($product->game->name)->substr(0, 3)->upper() }}]</span>
                                @endif
                                {{ \Illuminate\Support\Str::limit($product->name, 30) }}
                            </span>
                        </div>
                        <span class="rounded-full bg-paper px-3 py-1 font-mono text-xs text-ink/60">
                            ({{ $product->sold_count }}x)
                        </span>
                    </li>
                @empty
                    <li class="py-8 text-center">
                        <i class="bi bi-box-seam text-ink/20" style="font-size:2rem;"></i>
                        <p class="mt-2 text-sm text-ink/45">Belum ada penjualan bulan ini.</p>
                    </li>
                @endforelse
            </ol>
        </div>
    </div>

    {{-- ─── 5 Notifikasi Admin Terbaru (DS 14.2) ─── --}}
    <div class="mt-5 rounded-xl border border-rule bg-white p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bi bi-bell text-ink/60" style="font-size:1.1rem;"></i>
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
                    {{ ($notifikasiTerbaru ?? collect())->count() }} Notifikasi Admin Terbaru
                </h2>
            </div>
            @if(Route::has('office.notifications.read-all'))
                <form method="POST" action="{{ route('office.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-accent hover:underline">Tandai Semua Dibaca</button>
                </form>
            @endif
        </div>
        <div class="mt-4 space-y-2">
            @forelse ($notifikasiTerbaru ?? collect() as $notif)
                <div class="flex items-start gap-3.5 rounded-xl border border-rule/50 px-5 py-4 transition-colors hover:bg-paper/40">
                    @php
                        $notifType = $notif->type ?? $notif->data['type'] ?? 'info';
                        $notifIcon = match(true) {
                            str_contains($notifType, 'payment') || str_contains($notifType, 'bukti') => ['icon' => 'bi-receipt', 'color' => 'text-blue-500'],
                            str_contains($notifType, 'order')    => ['icon' => 'bi-bag-check', 'color' => 'text-green-500'],
                            str_contains($notifType, 'refund')   => ['icon' => 'bi-arrow-counterclockwise', 'color' => 'text-amber-500'],
                            str_contains($notifType, 'spammer')  => ['icon' => 'bi-shield-exclamation', 'color' => 'text-red-500'],
                            default                              => ['icon' => 'bi-info-circle', 'color' => 'text-gray-400'],
                        };
                    @endphp
                    <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-paper">
                        <i class="bi {{ $notifIcon['icon'] }} {{ $notifIcon['color'] }}" style="font-size:0.95rem;"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-ink/85">
                            <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/50">
                                [{{ str_replace('_', ' ', $notifType) }}]
                            </span>
                            {{ $notif->message ?? $notif->data['message'] ?? '-' }}
                        </p>
                        @if($notif->no_order ?? ($notif->data['no_order'] ?? null))
                            <span class="mt-0.5 inline-block font-mono text-[10px] text-ink/45">#{{ $notif->no_order ?? $notif->data['no_order'] }}</span>
                        @endif
                    </div>
                    <p class="whitespace-nowrap font-mono text-[10px] text-ink/40">
                        {{ $notif->created_at->diffForHumans(short: true) }}
                    </p>
                </div>
            @empty
                <div class="flex h-20 items-center justify-center rounded-lg border border-dashed border-rule bg-paper/30">
                    <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection