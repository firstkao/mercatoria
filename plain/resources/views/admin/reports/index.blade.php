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

    {{-- ============ HEAD ============ --}}
    <header class="rpt-head">
        <div>
            <p class="rpt-head__eyebrow">Laporan</p>
            <h1 class="rpt-head__title">Penjualan</h1>
            <span class="rpt-head__range">
                {{ $from->translatedFormat('j M Y') }} — {{ $until->translatedFormat('j M Y') }}
            </span>
        </div>
    </header>

    {{-- ============ RINGKASAN ============ --}}
    <section class="rpt-card">
        <h2 class="rpt-card__label">Ringkasan</h2>
        <div class="rpt-metrics">
            <div class="rpt-metric">
                <span class="rpt-metric__value">{{ \App\Support\PriceCalculator::formatRupiah($totalRevenue) }}</span>
                <span class="rpt-metric__label">Total Pendapatan</span>
            </div>
            <div class="rpt-metric">
                <span class="rpt-metric__value">{{ number_format($totalOrders, 0, ',', '.') }}</span>
                <span class="rpt-metric__label">Total Pesanan</span>
            </div>
            <div class="rpt-metric">
                <span class="rpt-metric__value">{{ \App\Support\PriceCalculator::formatRupiah($avgOrderValue) }}</span>
                <span class="rpt-metric__label">Rata-rata Nilai Pesanan</span>
            </div>
        </div>
    </section>

    {{-- ============ GRAFIK HARIAN ============ --}}
    @if (! empty($dailyRevenue))
        <section class="rpt-card">
            <h2 class="rpt-card__label">Pendapatan Harian</h2>
            <div class="rpt-chart">
                @php($max = max($dailyRevenue) ?: 1)
                @foreach ($dailyRevenue as $day => $total)
                    <div class="rpt-chart__bar" title="{{ $day }}: {{ \App\Support\PriceCalculator::formatRupiah($total) }}">
                        <div class="rpt-chart__fill" style="height: {{ round(($total / $max) * 100) }}%;"></div>
                        <span class="rpt-chart__label">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ SPLIT: STATUS + MARKETPLACE ============ --}}
    <div class="rpt-split">

        {{-- Per status --}}
        <section class="rpt-card">
            <h2 class="rpt-card__label">Pesanan per Status</h2>
            @if ($byStatus->isEmpty())
                <p class="rpt-empty">Belum ada pesanan pada rentang ini.</p>
            @else
                @php($statusMax = $byStatus->max() ?: 1)
                <ul class="rpt-status">
                    @foreach ($byStatus as $status => $count)
                        <li class="rpt-status__item">
                            <span class="rpt-status__name">
                                {{ \App\Enums\OrderStatus::tryFrom($status)?->label() ?? $status }}
                            </span>
                            <span class="rpt-status__count">{{ $count }}</span>
                            <div class="rpt-status__bar">
                                <span style="width: {{ round(($count / $statusMax) * 100) }}%"></span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Per marketplace --}}
        <section class="rpt-card">
            <h2 class="rpt-card__label">Per Marketplace</h2>
            @if ($byMarketplace->isEmpty())
                <p class="rpt-empty">Belum ada data pada rentang ini.</p>
            @else
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>Marketplace</th>
                            <th class="rpt-table__right">Pesanan</th>
                            <th class="rpt-table__right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byMarketplace as $name => $data)
                            <tr>
                                <td class="rpt-table__name">{{ $name }}</td>
                                <td class="rpt-table__num rpt-table__right">{{ number_format($data['count'], 0, ',', '.') }}</td>
                                <td class="rpt-table__num rpt-table__right">{{ \App\Support\PriceCalculator::formatRupiah($data['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

    </div>

    {{-- ============ TOP PRODUK ============ --}}
    <section class="rpt-card">
        <h2 class="rpt-card__label">Top 10 Produk Terjual</h2>
        @if ($topProducts->isEmpty())
            <p class="rpt-empty">Belum ada produk terjual pada rentang ini.</p>
        @else
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th class="rpt-table__rank">#</th>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th class="rpt-table__right">Qty</th>
                        <th class="rpt-table__right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topProducts as $index => $row)
                        <tr>
                            <td class="rpt-table__rank">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="rpt-table__name">{{ $row->product_name_snapshot }}</td>
                            <td class="muted">{{ $row->variant_name_snapshot ?: '—' }}</td>
                            <td class="rpt-table__num rpt-table__right">{{ number_format($row->total_qty, 0, ',', '.') }}</td>
                            <td class="rpt-table__num rpt-table__right">{{ \App\Support\PriceCalculator::formatRupiah($row->total_revenue) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

@endsection
