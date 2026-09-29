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
    <div class="stats">
        <div class="stat">
            <span class="stat__label">Total Pendapatan</span>
            <strong class="stat__value">{{ \App\Support\PriceCalculator::formatRupiah($totalRevenue) }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Total Pesanan</span>
            <strong class="stat__value">{{ number_format($totalOrders, 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Rata-rata Nilai Pesanan</span>
            <strong class="stat__value">{{ \App\Support\PriceCalculator::formatRupiah($avgOrderValue) }}</strong>
        </div>
    </div>

    {{-- Grafik harian --}}
    @if (! empty($dailyRevenue))
        <section class="panel">
            <h2>Pendapatan Harian</h2>
            <div class="chart-bars">
                @php($max = max($dailyRevenue) ?: 1)
                @foreach ($dailyRevenue as $day => $total)
                    <div class="chart-bar" title="{{ $day }}: {{ \App\Support\PriceCalculator::formatRupiah($total) }}">
                        <div class="chart-bar__fill" style="height: {{ round(($total / $max) * 100) }}%;"></div>
                        <span class="chart-bar__label">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid-2">
        {{-- Per status --}}
        <section class="panel">
            <h2>Pesanan per Status</h2>
            @if ($byStatus->isEmpty())
                <p class="muted">Belum ada pesanan.</p>
            @else
                <ul class="chip-list">
                    @foreach ($byStatus as $status => $count)
                        <li class="chip">
                            <span>{{ \App\Enums\OrderStatus::tryFrom($status)?->label() ?? $status }}</span>
                            <strong>{{ $count }}</strong>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Per marketplace --}}
        <section class="panel">
            <h2>Per Marketplace</h2>
            @if ($byMarketplace->isEmpty())
                <p class="muted">Belum ada data.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Marketplace</th>
                            <th>Pesanan</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byMarketplace as $name => $data)
                            <tr>
                                <td><strong>{{ $name }}</strong></td>
                                <td class="muted">{{ $data['count'] }}</td>
                                <td>{{ \App\Support\PriceCalculator::formatRupiah($data['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>

    {{-- Top produk --}}
    <section class="panel panel--flush">
        <h2 style="padding:1rem 1rem 0;">Top 10 Produk Terjual</h2>
        @if ($topProducts->isEmpty())
            <p class="muted" style="padding:1rem;">Belum ada data.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th>Qty Terjual</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topProducts as $index => $row)
                        <tr>
                            <td class="muted">{{ $index + 1 }}</td>
                            <td><strong>{{ $row->product_name_snapshot }}</strong></td>
                            <td class="muted">{{ $row->variant_name_snapshot }}</td>
                            <td>{{ number_format($row->total_qty, 0, ',', '.') }}</td>
                            <td>{{ \App\Support\PriceCalculator::formatRupiah($row->total_revenue) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection