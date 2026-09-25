@extends('admin.layouts.app', ['title' => 'Produk'])

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn--primary">@include('admin.partials.icon', ['name' => 'plus']) <span>Tambah produk</span></a>
@endsection

@section('filters')
    <form method="GET" action="{{ route('admin.products.index') }}" class="filters">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama atau SKU" aria-label="Cari nama atau SKU">
        <select name="status" aria-label="Status">
            <option value="">Semua status</option>
            <option value="tayang" @selected($filters['status'] === 'tayang')>Tayang</option>
            <option value="draf" @selected($filters['status'] === 'draf')>Draf</option>
            <option value="habis" @selected($filters['status'] === 'habis')>Semua varian habis</option>
        </select>
        <select name="game" aria-label="Game">
            <option value="">Semua game</option>
            @foreach ($games as $game)
                <option value="{{ $game->id }}" @selected($filters['game'] === $game->id)>{{ $game->name }}</option>
            @endforeach
        </select>
        <select name="developer" aria-label="Developer">
            <option value="">Semua developer</option>
            @foreach ($developers as $developer)
                <option value="{{ $developer->id }}" @selected($filters['developer'] === $developer->id)>{{ $developer->name }}</option>
            @endforeach
        </select>
        <select name="tag" aria-label="Tag">
            <option value="">Semua tag</option>
            @foreach (\App\Models\Product::TAGS as $value => $label)
                <option value="{{ $value }}" @selected($filters['tag'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn">Terapkan</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.products.index') }}" class="link">Reset</a>
        @endif
    </form>
@endsection

@section('content')
    @if ($products->isEmpty())
        <div class="empty">
            <p>Belum ada produk{{ array_filter($filters) ? ' yang cocok dengan filter' : '' }}.</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn--primary">Tambah produk</a>
        </div>
    @else
        @php
            $rows = $products->map(function ($product) use ($calculator) {
                $prices = $product->variants->map(fn ($variant) => $variant->sellingPrice($calculator))->filter();

                return [
                    'product' => $product,
                    'price' => $prices->isEmpty() ? '—' : (
                        $prices->min() === $prices->max()
                            ? \App\Support\PriceCalculator::formatRupiah($prices->min())
                            : \App\Support\PriceCalculator::formatRupiah($prices->min()).' – '.\App\Support\PriceCalculator::formatRupiah($prices->max())
                    ),
                    'available' => $product->variants->filter->isAvailable()->count(),
                ];
            });
        @endphp

        {{-- Desktop: table --}}
        <div class="panel panel--flush only-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Produk</th>
                        <th>SKU</th>
                        <th>Game</th>
                        <th>Developer</th>
                        <th>Tag</th>
                        <th>Tier</th>
                        <th>Harga</th>
                        <th>Varian</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="table__thumb">
                                @if ($row['product']->images->isNotEmpty())
                                    <img src="{{ $row['product']->images->first()->url() }}" alt="">
                                @else
                                    <span class="thumb-empty">@include('admin.partials.icon', ['name' => 'image'])</span>
                                @endif
                            </td>
                            <td class="table__name"><a href="{{ route('admin.products.edit', $row['product']) }}" class="table__title">{{ $row['product']->name }}</a></td>
                            <td class="muted table__sku" title="{{ $row['product']->sku }}">{{ $row['product']->sku ?: '—' }}</td>
                            <td class="muted table__meta">{{ $row['product']->game?->name ?? '—' }}</td>
                            <td class="muted table__meta">{{ $row['product']->developer?->name ?? '—' }}</td>
                            <td>
                                @if ($row['product']->tagLabel())
                                    <span class="tag-pill tag-pill--{{ $row['product']->tag }}">{{ $row['product']->tagLabel() }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="muted">{{ $row['product']->shippingTier->code }}</td>
                            <td class="nowrap">{{ $row['price'] }}</td>
                            <td class="muted nowrap">{{ $row['available'] }}/{{ $row['product']->variants->count() }}</td>
                            <td>
                                <span @class(['badge', 'badge--on' => $row['product']->is_published])>{{ $row['product']->is_published ? 'Tayang' : 'Draf' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <ul class="cards only-mobile">
            @foreach ($rows as $row)
                <li>
                    <a href="{{ route('admin.products.edit', $row['product']) }}" class="card-row">
                        @if ($row['product']->images->isNotEmpty())
                            <img src="{{ $row['product']->images->first()->url() }}" alt="" class="card-row__thumb">
                        @else
                            <span class="card-row__thumb thumb-empty">@include('admin.partials.icon', ['name' => 'image'])</span>
                        @endif
                        <span class="card-row__body">
                            <span class="card-row__title">{{ $row['product']->name }}</span>
                            <span class="card-row__meta">{{ $row['price'] }}</span>
                            <span class="card-row__meta">{{ collect([$row['product']->developer?->name, $row['product']->sku])->filter()->join(' · ') ?: '—' }}</span>
                            <span class="card-row__meta">
                                <span @class(['badge', 'badge--on' => $row['product']->is_published])>{{ $row['product']->is_published ? 'Tayang' : 'Draf' }}</span>
                                @if ($row['product']->tagLabel())
                                    <span class="tag-pill tag-pill--{{ $row['product']->tag }}">{{ $row['product']->tagLabel() }}</span>
                                @endif
                                {{ $row['available'] }}/{{ $row['product']->variants->count() }} tersedia
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        @include('admin.partials.pagination', ['paginator' => $products])
    @endif
@endsection