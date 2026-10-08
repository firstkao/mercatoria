@extends('admin.layouts.app', ['title' => 'Tambah Pesanan Manual', 'back' => route('admin.orders.index')])

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn--small">Batal</a>
@endsection

@push('head')
<style>
    .mo-picker { position: relative; }
    .mo-picker__input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font: inherit;
        font-size: 14px;
        background: #fff;
    }
    .mo-picker__input:focus {
        outline: none;
        border-color: #111827;
        box-shadow: 0 0 0 3px rgba(17, 24, 39, .08);
    }
    .mo-picker__results {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 30;
        max-height: 320px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .12);
        padding: 4px;
    }
    .mo-picker__results[hidden] { display: none; }
    .mo-picker__item {
        display: block;
        width: 100%;
        padding: 8px 12px;
        text-align: left;
        background: none;
        border: 0;
        border-radius: 4px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
        color: #111827;
    }
    .mo-picker__item:hover,
    .mo-picker__item:focus { background: #f3f4f6; outline: none; }
    .mo-picker__item strong { display: block; font-weight: 600; }
    .mo-picker__item small { color: #6b7280; font-size: 12px; }
    .mo-picker__empty { padding: 12px; text-align: center; color: #9ca3af; font-size: 13px; }

    /* Thumbnail di dropdown picker */
    .mo-picker__item--with-thumb {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
    }
    .mo-picker__thumb {
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 4px;
        object-fit: cover;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }
    .mo-picker__thumb--empty {
        display: grid;
        place-items: center;
        font-size: 9px;
        font-weight: 700;
        color: #9ca3af;
        letter-spacing: .06em;
    }
    .mo-picker__text {
        display: block;
        min-width: 0;
        flex: 1;
    }
    .mo-picker__text strong {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .mo-picker__text small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mo-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }
    .mo-selected__info strong { display: block; font-size: 14px; color: #111827; }
    .mo-selected__info small { color: #6b7280; font-size: 12.5px; }

    .mo-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px 20px;
    }
    .mo-form-grid .is-full { grid-column: 1 / -1; }
    .mo-field { display: flex; flex-direction: column; gap: 6px; }
    .mo-field > span {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
    }
    .mo-field input,
    .mo-field select,
    .mo-field textarea {
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font: inherit;
        font-size: 14px;
        background: #fff;
    }
    .mo-field input:focus,
    .mo-field select:focus,
    .mo-field textarea:focus {
        outline: none;
        border-color: #111827;
        box-shadow: 0 0 0 3px rgba(17, 24, 39, .08);
    }

    .mo-items-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .mo-items-table th {
        text-align: left;
        padding: 8px 8px 10px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
    }
    .mo-items-table td { padding: 10px 8px 10px 0; vertical-align: top; border-bottom: 1px solid #f3f4f6; }
    .mo-items-table input[type="number"] {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font: inherit;
        font-size: 13px;
    }
    .mo-items-table .col-variant { width: 40%; }
    .mo-items-table .col-qty     { width: 70px; }
    .mo-items-table .col-price   { width: 130px; }
    .mo-items-table .col-total   { width: 130px; text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
    .mo-items-table .col-action  { width: 40px; text-align: right; }
    .mo-item-variant-name { font-size: 12.5px; color: #6b7280; }

    /* Thumbnail di tabel item */
    .mo-item-product {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .mo-item-thumb {
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        border-radius: 4px;
        object-fit: cover;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }
    .mo-item-thumb--empty {
        display: grid;
        place-items: center;
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        letter-spacing: .06em;
    }
    .mo-item-product__info {
        min-width: 0;
        flex: 1;
    }
    .mo-item-product__info strong {
        display: block;
        font-size: 13.5px;
        color: #111827;
        line-height: 1.3;
    }

    .mo-summary {
        display: grid;
        gap: 8px;
        font-size: 13.5px;
    }
    .mo-summary__row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
    }
    .mo-summary__row dt { color: #6b7280; }
    .mo-summary__row dd { margin: 0; font-variant-numeric: tabular-nums; color: #111827; font-weight: 500; }
    .mo-summary__row--total {
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-weight: 700;
    }
    .mo-summary__row--total dd { font-size: 15px; }
    .mo-summary__row--emphasis dd { font-size: 18px; font-weight: 700; color: #111827; }
    .mo-summary__note {
        margin-top: 12px;
        font-size: 11.5px;
        color: #9ca3af;
        line-height: 1.5;
    }
    .mo-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13.5px;
        color: #374151;
        line-height: 1.5;
        cursor: pointer;
    }
    .mo-checkbox input[type="checkbox"] {
        width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; accent-color: #111827;
    }
    .mo-checkbox small { display: block; color: #9ca3af; font-size: 12px; margin-top: 2px; }

    @media (max-width: 720px) {
        .mo-form-grid { grid-template-columns: 1fr; }
        .mo-items-table thead { display: none; }
        .mo-items-table tr { display: block; padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
        .mo-items-table td { display: block; padding: 4px 0; border: 0; }
        .mo-items-table td::before {
            content: attr(data-label) ': ';
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .06em;
            margin-right: 6px;
        }
    }
</style>
@endpush

@section('content')
<form id="mo-form" method="POST" action="{{ route('admin.orders.store') }}">
    @csrf

    <div class="form-grid">
        <div class="form-grid__main stack">

            {{-- PELANGGAN --}}
            <x-admin.card>
                <h2>Pelanggan</h2>
                <p class="muted small" style="margin-top:-6px;margin-bottom:12px;">
                    Cari berdasarkan nama, email, atau nomor WhatsApp.
                </p>

                <div id="user-section">
                    <div id="user-picker-wrapper" class="mo-picker">
                        <input type="text"
                               id="user-search-input"
                               class="mo-picker__input"
                               placeholder="Ketik min. 2 karakter untuk cari..."
                               autocomplete="off">
                        <div id="user-results" class="mo-picker__results" hidden></div>
                    </div>

                    <div id="user-selected" class="mo-selected" hidden>
                        <div class="mo-selected__info">
                            <strong id="user-selected-name">—</strong>
                            <small>
                                <span id="user-selected-email"></span>
                                <span id="user-selected-wa"></span>
                            </small>
                        </div>
                        <button type="button" id="user-change-btn" class="btn btn--small">Ganti</button>
                    </div>

                    <input type="hidden" name="user_id" id="user-id-input" value="{{ old('user_id') }}" required>
                </div>

                @error('user_id')<p class="text-danger small">{{ $message }}</p>@enderror
            </x-admin.card>

            {{-- INFO PESANAN --}}
            <x-admin.card>
                <h2>Info Pesanan</h2>
                <div class="mo-form-grid">

                    <label class="mo-field">
                        <span>Tanggal Order</span>
                        <input type="datetime-local"
                               name="created_at"
                               value="{{ old('created_at', $defaultCreatedAt) }}"
                               required>
                    </label>

                    <label class="mo-field">
                        <span>Nomor Order</span>
                        <input type="text"
                               name="order_number"
                               value="{{ old('order_number') }}"
                               placeholder="Kosongkan = auto-generate">
                        <small class="muted small">Format otomatis: <code>MER-XXXXX-NNNN</code></small>
                    </label>

                    <label class="mo-field">
                        <span>Marketplace</span>
                        <select name="marketplace_id" required>
                            <option value="">Pilih Marketplace</option>
                            @foreach ($marketplaces as $mp)
                                <option value="{{ $mp->id }}" @selected(old('marketplace_id') == $mp->id)>
                                    {{ $mp->name }}
                                    @if ($mp->fp_fee_idr > 0) — Fee FP: Rp{{ number_format($mp->fp_fee_idr, 0, ',', '.') }} @endif
                                    @if ($mp->dp_fee_percent > 0) — Fee DP: {{ $mp->dp_fee_percent }}% @endif
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="mo-field">
                        <span>Status</span>
                        <select name="status" id="status-select" required>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $defaultStatus) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="mo-field">
                        <span>Skema Pembayaran</span>
                        <select name="payment_scheme" id="scheme-select" required>
                            <option value="FP" @selected(old('payment_scheme', 'FP') === 'FP')>Full Payment (FP)</option>
                            <option value="DP" @selected(old('payment_scheme') === 'DP')>Down Payment (DP 50%)</option>
                        </select>
                    </label>

                    <label class="mo-field">
                        <span>Batas Bayar (opsional)</span>
                        <input type="datetime-local"
                               name="payment_deadline_at"
                               value="{{ old('payment_deadline_at', $defaultDeadline) }}">
                    </label>

                    <label class="mo-field is-full">
                        <span>Catatan Pembeli (opsional)</span>
                        <textarea name="customer_note" rows="2" maxlength="1000">{{ old('customer_note') }}</textarea>
                    </label>
                </div>
            </x-admin.card>

            {{-- ITEM --}}
            <x-admin.card>
                <h2>Item</h2>
                <p class="muted small" style="margin-top:-6px;margin-bottom:12px;">
                    Cari produk, klik untuk menambah. Harga bisa diubah manual (khusus order lama dengan kurs berbeda).
                </p>

                <div class="mo-picker" style="margin-bottom:16px;">
                    <input type="text"
                           id="variant-search-input"
                           class="mo-picker__input"
                           placeholder="Cari nama produk, varian, atau SKU..."
                           autocomplete="off">
                    <div id="variant-results" class="mo-picker__results" hidden></div>
                </div>

                <table class="mo-items-table" id="items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="col-qty">Qty</th>
                            <th class="col-price">Harga Satuan</th>
                            <th class="col-total">Total</th>
                            <th class="col-action"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>

                <p class="muted small" id="items-empty" style="padding:20px 0;text-align:center;">
                    Belum ada item. Cari produk di atas untuk menambah.
                </p>

                @error('items')<p class="text-danger small">{{ $message }}</p>@enderror
            </x-admin.card>

            {{-- OPSI TAMBAHAN --}}
            <x-admin.card>
                <h2>Opsi Tambahan</h2>
                <div class="stack" style="gap:14px;">
                    <label class="mo-checkbox">
                        <input type="checkbox" name="effects" value="1" @checked(old('effects', true))>
                        <span>
                            <strong>Jalankan efek samping</strong>
                            <small>Kalau status = Selesai, kasih koin cashback &amp; bonus pertama (kalau belum pernah).</small>
                        </span>
                    </label>
                    <label class="mo-checkbox">
                        <input type="checkbox" name="notify" value="1" @checked(old('notify', false))>
                        <span>
                            <strong>Kirim notifikasi ke customer</strong>
                            <small>Email + in-app notification. Default: tidak dikirim (untuk order lama).</small>
                        </span>
                    </label>
                </div>
            </x-admin.card>

        </div>

        {{-- SIDEBAR --}}
        <aside class="form-grid__side stack">
            <x-admin.card class="panel--accent">
                <h3>Ringkasan</h3>

                <dl class="mo-summary" style="margin-top:12px;">
                    <div class="mo-summary__row">
                        <dt>Subtotal</dt>
                        <dd id="sum-subtotal">Rp0</dd>
                    </div>
                    <div class="mo-summary__row">
                        <dt>Potongan</dt>
                        <dd id="sum-discount">− Rp0</dd>
                    </div>
                    <div class="mo-summary__row mo-summary__row--total">
                        <dt>Total</dt>
                        <dd id="sum-total">Rp0</dd>
                    </div>
                    <div class="mo-summary__row mo-summary__row--emphasis">
                        <dt>Bayar Sekarang</dt>
                        <dd id="sum-pay-now">Rp0</dd>
                    </div>
                    <div class="mo-summary__row">
                        <dt>Sisa Nanti</dt>
                        <dd id="sum-remaining">Rp0</dd>
                    </div>
                    <div class="mo-summary__row">
                        <dt>Est. Koin</dt>
                        <dd id="sum-coins">+0</dd>
                    </div>
                </dl>

                <div class="mo-field" style="margin-top:14px;">
                    <span>Potongan Manual (Rp)</span>
                    <input type="number"
                           name="discount_idr"
                           id="discount-input"
                           value="{{ old('discount_idr', 0) }}"
                           min="0"
                           step="500">
                </div>

                <p class="mo-summary__note">
                    Kalkulasi pay-now &amp; sisa memakai fee marketplace yang dipilih.
                    Mengubah skema akan otomatis recalculate.
                </p>

                <button type="submit" class="btn btn--primary btn--block" style="margin-top:16px;"
                        onclick="return confirm('Simpan order manual ini? Pastikan data pelanggan & item sudah benar.');">
                    Simpan Pesanan
                </button>
            </x-admin.card>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const state = {
        items: [],
    };

    // marketplacesJs disiapkan di controller, karena Blade directive json
    // memecah argumen pakai explode dan array literal berisi koma akan
    // memotong ekspresi jadi PHP invalid.
    const marketplaces = {!! json_encode($marketplacesJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};

    const coinEarnPercent = {{ (int) \App\Models\Setting::integer('coin_earn_percent', 1) }};
    const routes = {
        searchUsers:    {!! json_encode(route('admin.orders.search-users')) !!},
        searchVariants: {!! json_encode(route('admin.orders.search-variants')) !!},
    };

    function rupiah(n) {
        if (! n || isNaN(n)) return 'Rp0';
        return 'Rp' + Math.round(n).toLocaleString('id-ID');
    }
    function debounce(fn, ms) {
        let t;
        return function () {
            clearTimeout(t);
            const args = arguments;
            t = setTimeout(() => fn.apply(null, args), ms);
        };
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        })[c]);
    }

    // ============================================================
    // USER PICKER
    // ============================================================
    const userInput      = document.getElementById('user-search-input');
    const userResults    = document.getElementById('user-results');
    const userSelected   = document.getElementById('user-selected');
    const userPickerWrap = document.getElementById('user-picker-wrapper');
    const userIdInput    = document.getElementById('user-id-input');

    function showUserSelected(user) {
        document.getElementById('user-selected-name').textContent = user.name;
        document.getElementById('user-selected-email').textContent = user.email || '—';
        document.getElementById('user-selected-wa').textContent =
            user.whatsapp ? ' · +' + user.whatsapp : '';
        userIdInput.value = user.id;
        userPickerWrap.hidden = true;
        userSelected.hidden = false;
        userResults.hidden = true;
    }

    function resetUserPicker() {
        userIdInput.value = '';
        userPickerWrap.hidden = false;
        userSelected.hidden = true;
        userInput.value = '';
        userInput.focus();
    }

    document.getElementById('user-change-btn').addEventListener('click', resetUserPicker);

    const doSearchUsers = debounce(async function (q) {
        try {
            const res = await fetch(routes.searchUsers + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' },
            });
            if (! res.ok) return;
            const data = await res.json();

            if (! data.length) {
                userResults.innerHTML = '<div class="mo-picker__empty">Tidak ada user ditemukan.</div>';
                userResults.hidden = false;
                return;
            }

            userResults.innerHTML = data.map(u => `
                <button type="button" class="mo-picker__item" data-user='${escapeHtml(JSON.stringify(u))}'>
                    <strong>${escapeHtml(u.name)}</strong>
                    <small>${escapeHtml(u.email || '—')}${u.whatsapp ? ' · +' + escapeHtml(u.whatsapp) : ''}</small>
                </button>
            `).join('');
            userResults.hidden = false;

            userResults.querySelectorAll('[data-user]').forEach(btn => {
                btn.addEventListener('click', () => {
                    showUserSelected(JSON.parse(btn.dataset.user));
                });
            });
        } catch (e) {
            // silent
        }
    }, 220);

    userInput.addEventListener('input', e => {
        const q = e.target.value.trim();
        if (q.length < 2) { userResults.hidden = true; return; }
        doSearchUsers(q);
    });

    document.addEventListener('click', e => {
        if (! e.target.closest('#user-picker-wrapper')) userResults.hidden = true;
        if (! e.target.closest('#variant-results') && ! e.target.closest('#variant-search-input')) {
            document.getElementById('variant-results').hidden = true;
        }
    });

    // ============================================================
    // VARIANT PICKER (dengan thumbnail)
    // ============================================================
    const variantInput   = document.getElementById('variant-search-input');
    const variantResults = document.getElementById('variant-results');

    const doSearchVariants = debounce(async function (q) {
        try {
            const res = await fetch(routes.searchVariants + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' },
            });
            if (! res.ok) return;
            const data = await res.json();

            if (! data.length) {
                variantResults.innerHTML = '<div class="mo-picker__empty">Tidak ada produk ditemukan.</div>';
                variantResults.hidden = false;
                return;
            }

            variantResults.innerHTML = data.map(v => `
                <button type="button" class="mo-picker__item mo-picker__item--with-thumb" data-variant='${escapeHtml(JSON.stringify(v))}'>
                    ${v.image_url
                        ? `<img src="${escapeHtml(v.image_url)}" alt="" class="mo-picker__thumb" loading="lazy">`
                        : `<span class="mo-picker__thumb mo-picker__thumb--empty">IMG</span>`}
                    <span class="mo-picker__text">
                        <strong>${escapeHtml(v.product_name)}</strong>
                        <small>${escapeHtml(v.variant_name || '—')}${v.sku ? ' · SKU: ' + escapeHtml(v.sku) : ''} · ${rupiah(v.selling_price_idr)}</small>
                    </span>
                </button>
            `).join('');
            variantResults.hidden = false;

            variantResults.querySelectorAll('[data-variant]').forEach(btn => {
                btn.addEventListener('click', () => {
                    addItem(JSON.parse(btn.dataset.variant));
                    variantInput.value = '';
                    variantResults.hidden = true;
                });
            });
        } catch (e) {
            // silent
        }
    }, 220);

    variantInput.addEventListener('input', e => {
        const q = e.target.value.trim();
        if (q.length < 2) { variantResults.hidden = true; return; }
        doSearchVariants(q);
    });

    // ============================================================
    // ITEMS
    // ============================================================
    const itemsBody  = document.getElementById('items-body');
    const itemsEmpty = document.getElementById('items-empty');

    function addItem(variant) {
        const existing = state.items.find(i => i.variant_id === variant.id);
        if (existing) {
            existing.qty += 1;
            renderItems();
            return;
        }

        state.items.push({
            variant_id:    variant.id,
            product_name:  variant.product_name,
            variant_name:  variant.variant_name,
            sku:           variant.sku,
            qty:           1,
            unit_price:    variant.selling_price_idr || 0,
            price_yuan:    variant.price_yuan,
            weight_grams:  variant.weight_grams,
            image_url:     variant.image_url || null,
        });
        renderItems();
    }

    function removeItem(idx) {
        state.items.splice(idx, 1);
        renderItems();
    }

    function renderItems() {
        if (state.items.length === 0) {
            itemsBody.innerHTML = '';
            itemsEmpty.hidden = false;
            recalcSummary();
            return;
        }

        itemsEmpty.hidden = true;
        itemsBody.innerHTML = state.items.map((item, idx) => `
            <tr data-idx="${idx}">
                <td data-label="Produk">
                    <div class="mo-item-product">
                        ${item.image_url
                            ? `<img src="${escapeHtml(item.image_url)}" alt="" class="mo-item-thumb" loading="lazy">`
                            : `<span class="mo-item-thumb mo-item-thumb--empty">IMG</span>`}
                        <div class="mo-item-product__info">
                            <strong>${escapeHtml(item.product_name)}</strong>
                            <div class="mo-item-variant-name">
                                ${escapeHtml(item.variant_name || '—')}
                                ${item.sku ? ' · SKU: ' + escapeHtml(item.sku) : ''}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${idx}][variant_id]" value="${item.variant_id}">
                </td>
                <td data-label="Qty" class="col-qty">
                    <input type="number"
                           name="items[${idx}][quantity]"
                           value="${item.qty}"
                           min="1" max="999"
                           data-item-qty
                           data-idx="${idx}">
                </td>
                <td data-label="Harga" class="col-price">
                    <input type="number"
                           name="items[${idx}][unit_price_idr]"
                           value="${item.unit_price}"
                           min="0" step="500"
                           data-item-price
                           data-idx="${idx}">
                </td>
                <td data-label="Total" class="col-total">
                    <span data-item-total>${rupiah(item.unit_price * item.qty)}</span>
                </td>
                <td class="col-action">
                    <button type="button" class="btn btn--small" data-item-remove data-idx="${idx}"
                            style="padding:4px 8px;background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                        ×
                    </button>
                </td>
            </tr>
        `).join('');

        itemsBody.querySelectorAll('[data-item-qty]').forEach(el => {
            el.addEventListener('input', e => {
                const idx = parseInt(e.target.dataset.idx);
                state.items[idx].qty = Math.max(1, parseInt(e.target.value) || 1);
                e.target.value = state.items[idx].qty;
                updateItemTotal(idx);
                recalcSummary();
            });
        });

        itemsBody.querySelectorAll('[data-item-price]').forEach(el => {
            el.addEventListener('input', e => {
                const idx = parseInt(e.target.dataset.idx);
                state.items[idx].unit_price = Math.max(0, parseInt(e.target.value) || 0);
                updateItemTotal(idx);
                recalcSummary();
            });
        });

        itemsBody.querySelectorAll('[data-item-remove]').forEach(el => {
            el.addEventListener('click', e => {
                removeItem(parseInt(e.target.dataset.idx));
            });
        });

        recalcSummary();
    }

    function updateItemTotal(idx) {
        const tr = itemsBody.querySelector(`tr[data-idx="${idx}"]`);
        if (! tr) return;
        const span = tr.querySelector('[data-item-total]');
        if (span) span.textContent = rupiah(state.items[idx].unit_price * state.items[idx].qty);
    }

    // ============================================================
    // SUMMARY
    // ============================================================
    const discountInput = document.getElementById('discount-input');
    const schemeSelect  = document.getElementById('scheme-select');
    const mpSelect      = document.querySelector('select[name="marketplace_id"]');

    function recalcSummary() {
        const subtotal = state.items.reduce((s, i) => s + i.unit_price * i.qty, 0);
        const discount = Math.max(0, parseInt(discountInput.value) || 0);
        const net      = Math.max(0, subtotal - discount);

        const scheme = schemeSelect.value;
        const mpId   = mpSelect.value;
        const mp     = marketplaces[mpId] || { fp_fee_idr: 0, dp_fee_percent: 0 };

        let mpFee = 0, payNow = 0, remaining = 0;
        if (scheme === 'FP') {
            mpFee = parseInt(mp.fp_fee_idr) || 0;
            payNow = net + mpFee;
            remaining = 0;
        } else {
            payNow = Math.floor(net / 2);
            const remainingBase = net - payNow;
            mpFee = Math.floor(remainingBase * (parseFloat(mp.dp_fee_percent) || 0) / 100);
            remaining = remainingBase + mpFee;
        }

        const coins = Math.floor(net * coinEarnPercent / 100);

        document.getElementById('sum-subtotal').textContent  = rupiah(subtotal);
        document.getElementById('sum-discount').textContent  = '− ' + rupiah(discount);
        document.getElementById('sum-total').textContent     = rupiah(net);
        document.getElementById('sum-pay-now').textContent   = rupiah(payNow);
        document.getElementById('sum-remaining').textContent = rupiah(remaining);
        document.getElementById('sum-coins').textContent     = '+' + coins.toLocaleString('id-ID');
    }

    discountInput.addEventListener('input', recalcSummary);
    schemeSelect.addEventListener('change', recalcSummary);
    mpSelect.addEventListener('change', recalcSummary);

    renderItems();
    recalcSummary();
})();
</script>
@endpush
