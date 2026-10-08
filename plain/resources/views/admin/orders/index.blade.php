@extends('admin.layouts.app', ['title' => 'Manajemen Pesanan'])

@push('head')
<style>
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

    /* ===== BULK ACTION BAR ===== */
    .bulk-bar {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: #111827;
        color: #fff;
        font-size: 13px;
        border-bottom: 1px solid #1f2937;
    }
    .bulk-bar[hidden] { display: none !important; }
    .bulk-bar__count { font-weight: 600; }
    .bulk-bar__count strong { font-variant-numeric: tabular-nums; }
    .bulk-bar__spacer { flex: 1; }
    .bulk-bar select {
        padding: 6px 10px;
        border: 1px solid #374151;
        border-radius: 4px;
        background: #1f2937;
        color: #fff;
        font: inherit;
        font-size: 13px;
    }
    .bulk-bar select:focus { outline: 2px solid #60a5fa; }
    .bulk-bar .btn {
        padding: 6px 12px;
        font-size: 13px;
    }
    .bulk-bar .btn--danger {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    .bulk-bar .btn--danger:hover { background: #b91c1c; }
    .bulk-bar__cancel {
        background: transparent;
        border: 1px solid #4b5563;
        color: #d1d5db;
        padding: 6px 10px;
        border-radius: 4px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }
    .bulk-bar__cancel:hover { background: #1f2937; color: #fff; }

    /* Checkbox cell — kolom sempit di paling kiri */
    .table .col-check,
    .table th.col-check,
    .table td.col-check {
        width: 40px;
        text-align: center;
        padding-left: 12px;
        padding-right: 6px;
    }
    .table input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #111827;
    }
    .table tr.is-selected { background: #f3f4f6; }
    .table tr.is-selected td { box-shadow: inset 3px 0 0 #111827; }
</style>
@endpush

@section('content')
<form id="bulk-form"
      method="POST"
      action="{{ route('admin.orders.bulk') }}"
      data-confirm-msg="Yakin jalankan aksi bulk untuk pesanan terpilih?">
    @csrf
    <input type="hidden" name="action" value="status">

    <x-admin.card padding="flush">
        <div class="panel__head p-5">
            <h2>Pesanan</h2>
            @if (Route::has('admin.orders.create'))
                <a href="{{ route('admin.orders.create') }}" class="btn btn--small">Tambahkan pesanan</a>
            @endif
        </div>

        {{-- STATUS TABS --}}
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

        {{-- SEARCH --}}
        <div class="order-search">
            <input type="search"
                   form="filter-form"
                   name="q"
                   value="{{ $q }}"
                   placeholder="Cari no. order, nama pelanggan, atau email..."
                   autocomplete="off">

            <select name="status" form="filter-form" onchange="document.getElementById('filter-form').submit()">
                <option value="">Semua</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" form="filter-form" class="btn btn--small">Cari pesanan</button>

            @if ($q !== '' || $status)
                <a href="{{ route('admin.orders.index') }}" class="btn btn--small">Reset</a>
            @endif
        </div>

        {{-- BULK ACTION BAR (muncul saat ada yang dicentang) --}}
        <div class="bulk-bar" data-bulk-bar hidden>
            <span class="bulk-bar__count">
                <strong data-bulk-count>0</strong> pesanan dipilih
            </span>

            <span class="bulk-bar__spacer"></span>

            <label for="bulk-status" style="margin-right:4px;">Ubah ke:</label>
            <select name="status" id="bulk-status" form="bulk-form">
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" form="bulk-form" class="btn btn--small">Terapkan</button>
            <button type="button" class="bulk-bar__cancel" data-bulk-cancel>Batal</button>
        </div>

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
                        <th class="col-check">
                            <input type="checkbox" data-select-all aria-label="Pilih semua">
                        </th>
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
                        <tr data-row>
                            <td class="col-check">
                                <input type="checkbox"
                                       name="ids[]"
                                       value="{{ $order->id }}"
                                       form="bulk-form"
                                       data-row-checkbox
                                       aria-label="Pilih pesanan {{ $order->order_number }}">
                            </td>
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

            {{-- Mobile cards — checkbox di kiri, link di kanan --}}
            <ul class="cards only-mobile">
                @foreach ($orders as $order)
                    <li style="display:flex; align-items:flex-start; gap:10px; padding:12px 16px; border-bottom:1px solid #e5e7eb;">
                        <input type="checkbox"
                               name="ids[]"
                               value="{{ $order->id }}"
                               form="bulk-form"
                               data-row-checkbox
                               aria-label="Pilih pesanan {{ $order->order_number }}"
                               style="margin-top:4px;">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="card-row card-row--stack"
                           style="flex:1; min-width:0; padding:0;">
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
</form>

{{-- Form filter TERPISAH dari bulk-form supaya submit tidak saling ganggu --}}
<form id="filter-form" method="GET" action="{{ route('admin.orders.index') }}" hidden></form>
@endsection

@push('scripts')
<script>
(function () {
    var bulkForm   = document.getElementById('bulk-form');
    var bulkBar    = document.querySelector('[data-bulk-bar]');
    var bulkCount  = document.querySelector('[data-bulk-count]');
    var cancelBtn  = document.querySelector('[data-bulk-cancel]');
    var selectAll  = document.querySelector('[data-select-all]');
    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('[data-row-checkbox]'));

    if (! bulkForm || ! bulkBar || checkboxes.length === 0) return;

    function refresh() {
        var checked = checkboxes.filter(function (c) { return c.checked; });
        var n = checked.length;

        bulkCount.textContent = n;
        bulkBar.hidden = n === 0;

        // Highlight baris yang dipilih
        checkboxes.forEach(function (c) {
            var row = c.closest('tr');
            if (row) row.classList.toggle('is-selected', c.checked);
        });

        // Sinkronkan state select-all
        if (selectAll) {
            selectAll.checked = n === checkboxes.length;
            selectAll.indeterminate = n > 0 && n < checkboxes.length;
        }
    }

    // Pasang listener per checkbox
    checkboxes.forEach(function (c) {
        c.addEventListener('change', refresh);
    });

    // Select all / deselect all
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (c) { c.checked = selectAll.checked; });
            refresh();
        });
    }

    // Tombol batal: lepas semua centang
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            checkboxes.forEach(function (c) { c.checked = false; });
            if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
            refresh();
        });
    }

    // Konfirmasi sebelum submit bulk
    bulkForm.addEventListener('submit', function (e) {
        var n = checkboxes.filter(function (c) { return c.checked; }).length;
        if (n === 0) {
            e.preventDefault();
            return;
        }
        var statusSel = document.getElementById('bulk-status');
        var statusLabel = statusSel ? statusSel.options[statusSel.selectedIndex].text : '—';
        var ok = confirm('Yakin ubah ' + n + ' pesanan ke status "' + statusLabel + '"?\n\n' +
                         'Kalau ada pesanan yang transisinya tidak sah, akan dilewati otomatis.');
        if (! ok) e.preventDefault();
    });

    // Initial state
    refresh();
})();
</script>
@endpush
