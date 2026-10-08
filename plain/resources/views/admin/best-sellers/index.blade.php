@extends('admin.layouts.app', ['title' => 'Produk Terlaris'])

@section('actions')
    <form method="POST" action="{{ route('admin.best-sellers.refresh') }}" class="inline-form">
        @csrf
        <button type="submit" class="btn btn--primary" onclick="return confirm('Hitung ulang ranking best seller sekarang?');">
            🔄 Refresh Sekarang
        </button>
    </form>
@endsection

@section('filters')
    <form method="GET" action="{{ route('admin.best-sellers.index') }}" class="filters">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari produk…">
        <select name="filter" aria-label="Filter">
            <option value="">Semua produk</option>
            <option value="ranked" @selected($filter === 'ranked')>Hanya yang ranked</option>
            <option value="excluded" @selected($filter === 'excluded')>Yang dikecualikan</option>
        </select>
        <button type="submit" class="btn">Terapkan</button>
        @if ($search || $filter)
            <a href="{{ route('admin.best-sellers.index') }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')

    <div class="stats">
        <div class="stat">
            <span class="stat__label">Produk Ranked</span>
            <strong class="stat__value">{{ $stats['total_ranked'] }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Total Terjual</span>
            <strong class="stat__value">{{ number_format($stats['total_sold'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Periode</span>
            <strong class="stat__value">{{ $stats['period_days'] }} hari</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Tampil di Home</span>
            <strong class="stat__value">Top {{ $stats['limit'] }}</strong>
        </div>
    </div>

    <p class="hint intro">
        Ranking dihitung dari total qty terjual dalam <strong>{{ $stats['period_days'] }} hari terakhir</strong> (status pembayaran sah).
        Hanya <strong>top {{ $stats['limit'] }}</strong> yang tampil di homepage. Refresh otomatis tiap hari 04:00 WIB.
        @if ($stats['last_updated'])
            Terakhir diperbarui: <strong>{{ \Illuminate\Support\Carbon::parse($stats['last_updated'])->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</strong>.
        @endif
    </p>

    @if ($products->isEmpty())
        <div class="empty"><p>Tidak ada produk yang cocok.</p></div>
    @else
        <div class="panel panel--flush">
            <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="col-xs">Rank</th>
                        <th>Produk</th>
                        <th>Game</th>
                        <th>Terjual ({{ $stats['period_days'] }}hr)</th>
                        <th>Status</th>
                        <th>Exclude</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="{{ $product->exclude_best_seller ? 'row-muted' : '' }}">
                            <td>
                                @if ($product->best_seller_rank)
                                    <span class="rank-badge rank-badge--{{ $product->best_seller_rank <= 3 ? 'top' : 'normal' }}">
                                        #{{ $product->best_seller_rank }}
                                    </span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="table__name">
                                <a href="{{ route('admin.products.edit', $product) }}" class="table__title">{{ $product->name }}</a>
                                @if ($product->is_published)
                                    <span class="badge badge--on small ms-2">Tayang</span>
                                @else
                                    <span class="badge badge--muted small ms-2">Draf</span>
                                @endif
                            </td>
                            <td class="muted">{{ $product->game?->name ?? '—' }}</td>
                            <td>
                                <strong>{{ number_format($product->best_seller_score, 0, ',', '.') }}</strong>
                                @if ($product->best_seller_score < $stats['min_sales'])
                                    <span class="muted small">(di bawah minimal)</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->best_seller_rank)
                                    <span class="badge badge--on">🏆 Best Seller</span>
                                @elseif ($product->best_seller_score > 0)
                                    <span class="badge badge--muted">Tidak masuk top {{ $stats['limit'] }}</span>
                                @else
                                    <span class="badge badge--muted">Belum terjual</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.best-sellers.toggle-exclude', $product) }}" class="inline-form">
                                    @csrf
                                    <button type="submit" class="btn btn--small {{ $product->exclude_best_seller ? 'btn--primary' : '' }}"
                                            onclick="return confirm('{{ $product->exclude_best_seller ? 'Kembalikan produk ini ke best seller?' : 'Kecualikan produk ini dari best seller?' }}');">
                                        {{ $product->exclude_best_seller ? 'Kembalikan' : 'Kecualikan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        @include('admin.partials.pagination', ['paginator' => $products])
    @endif
@endsection