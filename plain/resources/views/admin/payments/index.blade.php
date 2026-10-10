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

    /* Checkbox cell */
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

    /* ===== MODAL KONFIRMASI ===== */
    .confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .confirm-modal[hidden] { display: none !important; }
    .confirm-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .6);
        border: 0;
        padding: 0;
        cursor: pointer;
    }
    .confirm-modal__panel {
        position: relative;
        width: 100%;
        max-width: 480px;
        max-height: 90dvh;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }
    .confirm-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
    }
    .confirm-modal__head h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .confirm-modal__close {
        background: none;
        border: 0;
        padding: 4px;
        color: #64748b;
        cursor: pointer;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
    }
    .confirm-modal__close:hover { background: #f1f5f9; color: #0f172a; }

    .confirm-modal__body {
        padding: 22px;
        font-size: 14px;
        color: #1e293b;
        line-height: 1.6;
    }

    .confirm-modal__hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        text-align: center;
    }
    .confirm-modal__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        flex-shrink: 0;
    }
    .confirm-modal__icon--info {
        background: #f0f9ff;
        color: #0284c7;
    }
    .confirm-modal__title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -.01em;
        line-height: 1.3;
    }

    .confirm-modal__lead {
        margin: 0 0 16px;
        color: #475569;
        line-height: 1.65;
    }
    .confirm-modal__lead strong {
        color: #0f172a;
        font-weight: 600;
    }

    .confirm-modal__list {
        list-style: none;
        margin: 0;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .confirm-modal__list li {
        position: relative;
        padding-left: 20px;
        font-size: 13.5px;
        color: #334155;
        line-height: 1.55;
    }
    .confirm-modal__list li::before {
        content: "";
        position: absolute;
        left: 2px;
        top: 8px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #94a3b8;
    }
    .confirm-modal__list li strong { color: #0f172a; font-weight: 600; }

    .confirm-modal__foot {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 22px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }
    .confirm-modal__foot .btn { min-width: 100px; }

    @media (max-width: 640px) {
        .confirm-modal__foot { flex-direction: column-reverse; }
        .confirm-modal__foot .btn { width: 100%; }
        .confirm-modal__icon { width: 48px; height: 48px; font-size: 22px; }
        .confirm-modal__title { font-size: 16px; }
    }
</style>
@endpush

@section('content')
<form id="bulk-form"
      method="POST"
      action="{{ route('admin.orders.bulk') }}">
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

        {{-- BULK ACTION BAR --}}
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

            {{-- Mobile cards --}}
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

{{-- ============================================================
     MODAL KONFIRMASI BULK
     ============================================================ --}}
<div class="confirm-modal" id="bulk-confirm-modal" hidden>
    <button type="button" class="confirm-modal__backdrop" data-modal-close aria-label="Tutup"></button>

    <div class="confirm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="bulk-confirm-title">
        <div class="confirm-modal__head">
            <h2 id="bulk-confirm-title">Konfirmasi</h2>
            <button type="button" class="confirm-modal__close" data-modal-close aria-label="Tutup">✕</button>
        </div>

        <div class="confirm-modal__body">
            <div class="confirm-modal__hero">
                <div class="confirm-modal__icon confirm-modal__icon--info">ℹ</div>
                <h3 class="confirm-modal__title">Ubah Status Pesanan?</h3>
            </div>

            <p class="confirm-modal__lead" id="bulk-confirm-lead"></p>

            <ul class="confirm-modal__list">
                <li>Hanya pesanan dengan transisi <strong>valid</strong> yang akan diubah</li>
                <li>Pesanan dengan transisi tidak sah akan <strong>dilewati otomatis</strong></li>
                <li>Notifikasi akan dikirim ke setiap pelanggan</li>
            </ul>
        </div>

        <div class="confirm-modal__foot">
            <button type="button" class="btn" data-modal-close>Batal</button>
            <button type="button" class="btn btn--primary" id="bulk-confirm-action">Ya, Terapkan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var bulkForm       = document.getElementById('bulk-form');
    var bulkBar        = document.querySelector('[data-bulk-bar]');
    var bulkCount      = document.querySelector('[data-bulk-count]');
    var cancelBtn      = document.querySelector('[data-bulk-cancel]');
    var selectAll      = document.querySelector('[data-select-all]');
    var checkboxes     = Array.prototype.slice.call(document.querySelectorAll('[data-row-checkbox]'));

    // Modal elements
    var modal          = document.getElementById('bulk-confirm-modal');
    var modalLead      = document.getElementById('bulk-confirm-lead');
    var modalAction    = document.getElementById('bulk-confirm-action');

    if (! bulkForm || ! bulkBar || checkboxes.length === 0) return;

    /* ============================================================
       BULK BAR — checkbox logic
       ============================================================ */
    function refresh() {
        var checked = checkboxes.filter(function (c) { return c.checked; });
        var n = checked.length;

        bulkCount.textContent = n;
        bulkBar.hidden = n === 0;

        checkboxes.forEach(function (c) {
            var row = c.closest('tr');
            if (row) row.classList.toggle('is-selected', c.checked);
        });

        if (selectAll) {
            selectAll.checked = n === checkboxes.length;
            selectAll.indeterminate = n > 0 && n < checkboxes.length;
        }
    }

    checkboxes.forEach(function (c) {
        c.addEventListener('change', refresh);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (c) { c.checked = selectAll.checked; });
            refresh();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            checkboxes.forEach(function (c) { c.checked = false; });
            if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
            refresh();
        });
    }

    /* ============================================================
       MODAL — open / close
       ============================================================ */
    function openModal(leadHtml) {
        modalLead.innerHTML = leadHtml;
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { modalAction.focus(); }, 50);
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
            closeModal();
        }
    });

    modalAction.addEventListener('click', function () {
        closeModal();
        bulkForm.submit();
    });

    /* ============================================================
       FORM SUBMIT — intercept, tampilkan modal
       ============================================================ */
    bulkForm.addEventListener('submit', function (e) {
        var n = checkboxes.filter(function (c) { return c.checked; }).length;

        if (n === 0) {
            e.preventDefault();
            return;
        }

        e.preventDefault();

        var statusSel   = document.getElementById('bulk-status');
        var statusLabel = statusSel ? statusSel.options[statusSel.selectedIndex].text : '—';

        var lead =
            'Yakin ubah <strong>' + n + ' pesanan</strong> ke status ' +
            '<strong>"' + statusLabel + '"</strong>?';

        openModal(lead);
    });

    // Initial state
    refresh();
})();
</script>
@endpush
