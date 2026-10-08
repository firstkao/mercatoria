@extends('admin.layouts.app', ['title' => 'Laporan Penjualan'])

@section('filters')
    <form method="GET" action="{{ route('admin.reports.index') }}" class="filters">
        <label class="date-field"><span>Dari</span><input type="date" name="dari" value="{{ $from->toDateString() }}"></label>
        <label class="date-field"><span>Sampai</span><input type="date" name="sampai" value="{{ $until->toDateString() }}"></label>
        <button type="submit" class="btn">Terapkan</button>
        <a href="{{ route('admin.reports.export', ['dari' => $from->toDateString(), 'sampai' => $until->toDateString()]) }}" class="btn btn--primary">Export CSV</a>
    </form>
@endsection

@section('content')

    <p class="muted mb-4">Rentang: <strong class="text-strong">{{ $from->translatedFormat('j M Y') }} — {{ $until->translatedFormat('j M Y') }}</strong></p>

    {{-- ============ RINGKASAN ============ --}}
    <x-admin.card class="mb-6">
        <h2 class="section-label">Ringkasan</h2>
        <x-admin.metric-strip>
            <x-admin.metric :value="\App\Support\PriceCalculator::formatRupiah($totalRevenue)" label="Total Pendapatan" />
            <x-admin.metric :value="number_format($totalOrders, 0, ',', '.')" label="Total Pesanan" />
            <x-admin.metric :value="\App\Support\PriceCalculator::formatRupiah($avgOrderValue)" label="Rata-rata Nilai Pesanan" />
        </x-admin.metric-strip>
    </x-admin.card>

    {{-- ============ GRAFIK HARIAN ============ --}}
    @if (! empty($dailyRevenue))
        <x-admin.card class="mb-6">
            <h2 class="section-label">Pendapatan Harian</h2>
            <div class="chart">
                @php($max = max($dailyRevenue) ?: 1)
                @foreach ($dailyRevenue as $day => $total)
                    <div class="chart__bar" title="{{ $day }}: {{ \App\Support\PriceCalculator::formatRupiah($total) }}">
                        <div class="chart__fill" style="height: {{ round(($total / $max) * 100) }}%;"></div>
                        <span class="chart__label">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    @endif

    {{-- ============ SPLIT: STATUS + MARKETPLACE ============ --}}
    <div class="split-2">

        {{-- Per status --}}
        <x-admin.card>
            <h2 class="section-label">Pesanan per Status</h2>
            @if ($byStatus->isEmpty())
                <p class="muted">Belum ada pesanan pada rentang ini.</p>
            @else
                @php($statusMax = $byStatus->max() ?: 1)
                <ul class="bar-list">
                    @foreach ($byStatus as $status => $count)
                        <li class="bar-list__item">
                            <span class="bar-list__name">
                                {{ \App\Enums\OrderStatus::tryFrom($status)?->label() ?? $status }}
                            </span>
                            <span class="bar-list__count">{{ $count }}</span>
                            <div class="bar-list__bar">
                                <span style="width: {{ round(($count / $statusMax) * 100) }}%"></span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        {{-- Per marketplace --}}
        <x-admin.card>
            <h2 class="section-label">Per Marketplace</h2>
            @if ($byMarketplace->isEmpty())
                <p class="muted">Belum ada data pada rentang ini.</p>
            @else
                <x-admin.table>
                    <x-slot:head>
                        <tr>
                            <th>Marketplace</th>
                            <th class="text-right">Pesanan</th>
                            <th class="text-right">Pendapatan</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($byMarketplace as $name => $data)
                        <tr>
                            <td class="text-strong">{{ $name }}</td>
                            <td class="text-right">{{ number_format($data['count'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($data['revenue']) }}</td>
                        </tr>
                    @endforeach
                </x-admin.table>
            @endif
        </x-admin.card>

    </div>

    {{-- ============ TOP PRODUK ============ --}}
    <x-admin.card>
        <h2 class="section-label">Top 10 Produk Terjual</h2>
        @if ($topProducts->isEmpty())
            <x-admin.empty-state title="Belum ada produk terjual" hint="Tidak ada penjualan pada rentang ini." />
        @else
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th class="col-xs">#</th>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Pendapatan</th>
                    </tr>
                </x-slot:head>
                @foreach ($topProducts as $index => $row)
                    <tr>
                        <td class="muted font-mono-sm">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-strong">{{ $row->product_name_snapshot }}</td>
                        <td class="muted">{{ $row->variant_name_snapshot ?: '—' }}</td>
                        <td class="text-right">{{ number_format($row->total_qty, 0, ',', '.') }}</td>
                        <td class="text-right">{{ \App\Support\PriceCalculator::formatRupiah($row->total_revenue) }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        @endif
    </x-admin.card>

@endsection
