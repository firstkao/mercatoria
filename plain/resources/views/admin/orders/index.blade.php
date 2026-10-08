@extends('admin.layouts.app', ['title' => 'Manajemen Pesanan'])

@push('head')
<style>
    /* Status tabs + search — sengaja di-inline supaya tidak perlu ubah
       stylesheet admin global kalau nanti desainnya berubah. */
    .order-tabs {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 2px 4px;
        padding: 14px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }
    .order-tabs a {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        color: #4b5563;
        text-decoration: none;
        transition: background .12s, color .12s;
        white-space: nowrap;
    }
    .order-tabs a:hover { background: #f3f4f6; color: #111827; }
    .order-tabs a.is-active { background: #111827; color: #fff; font-weight: 600; }
    .order-tabs a.is-active .count { opacity: .85; }
    .order-tabs .sep { color: #d1d5db; user-select: none; }
    .order-tabs .count { font-variant-numeric: tabular-nums; }

    .order-search {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }
    .order-search input[type="search"],
    .order-search select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font: inherit;
        font-size: 14px;
        background: #fff;
    }
    .order-search input[type="search"] {
        flex: 1 1 240px;
        min-width: 0;
    }
    .order-search input[type="search"]:focus,
    .order-search select:focus {
        outline: none;
        border-color: #111827;
        box-shadow: 0 0 0 3px rgba(17, 24, 39, .08);
    }
</style>
@endpush

@section('content')
<x-admin.card padding="flush">
    <div class="panel__head p-5">
        <h2>Pesanan</h2>
        @if (Route::has('admin.orders.create'))
            <a href="{{ route('admin.orders.create') }}" class="btn btn--small">Tambahkan pesanan</a>
        @endif
    </div>

    {{-- ========== STATUS TABS ========== --}}
    <nav class="order-tabs" aria-label="Filter status">
        <a href="{{ route('admin.orders.index', array_filter(['q' => $q ?: null])) }}"
           class="{{ ! $status ? 'is-active' : '' }}">
            Semua <span class="count">({{ $counts['all'] }})</span>
        </a>

        @foreach ($statuses as $key => $label)
            <span class="sep">|</span>
            <a href="{{ route('admin.orders.index', array_filter(['status' => $key, 'q' => $q ?: null])) }}"
               class="{{ $status === $key ? 'is-active' : '' }}">
                {{ $label }} <span class="count">({{ $counts[$key] ?? 0 }})</span>
            </a>
        @endforeach
    </nav>

    {{-- ========== SEARCH ========== --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="order-search">
        <input type="search"
               name="q"
               value="{{ $q }}"
               placeholder="Cari no. order, nama pelanggan, atau email..."
               autocomplete="off">

        <select name="status">
            <option value="">Semua</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn--small">Cari pesanan</button>

        @if ($q !== '' || $status)
            <a href="{{ route('admin.orders.index') }}" class="btn btn--small">Reset</a>
        @endif
    </form>

    {{-- ========== TABLE / MOBILE CARDS ========== --}}
    @if ($orders->isEmpty())
        <div class="empty">
            <p>
                @if ($q !== '')
                    Tidak ada pesanan yang cocok dengan pencarian "{{ $q }}".
                @elseif ($status)
                    Tidak ada pesanan dengan status ini.
                @else
                    Belum ada pesanan.
                @endif
            </p>
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Tagihan Saat Ini</th>
                    <th>Skema</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td class="mono"><strong>{{ $order->order_number }}</strong></td>
                        <td>
                            <strong>{{ $order->user->full_name ?? 'Data Dihapus' }}</strong>
                            <div class="muted small">{{ $order->user->email ?? '' }}</div>
                        </td>
                        <td class="text-price">{{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }}</td>
                        <td>{{ $order->payment_scheme }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'ditahan' ? 'badge--warn' : '' }} {{ $order->status === 'selesai' ? 'badge--on' : '' }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </td>
                        <td class="muted small">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn--small">Detail & Verifikasi</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <ul class="cards only-mobile">
            @foreach ($orders as $order)
                <li>
                    <a href="{{ route('admin.orders.show', $order) }}" class="card-row card-row--stack">
                        <div class="card-row__head">
                            <span class="card-row__title mono">{{ $order->order_number }}</span>
                            <span class="badge {{ $order->status === 'ditahan' ? 'badge--warn' : '' }} {{ $order->status === 'selesai' ? 'badge--on' : '' }}">{{ $order->statusLabel() }}</span>
                        </div>
                        <p class="card-row__meta">{{ $order->user->full_name ?? 'Data Dihapus' }}</p>
                        <p class="card-row__meta">Bayar sekarang: {{ \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr) }} · Skema {{ $order->payment_scheme }}</p>
                        <p class="card-row__meta">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="p-5">
            {{ $orders->links('admin.partials.pagination') }}
        </div>
    @endif
</x-admin.card>
@endsection
