@extends('admin.layouts.admin')

@section('title', 'Stores')

@section('content')

@php
    $stats = ['total' => $stores->count()];
@endphp

<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
        <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Stores</h1>
    </div>
    <div class="flex items-center gap-2">
        <span class="rounded-full bg-black px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['total'] }} Store</span>
    </div>
</div>

{{-- ═══ FORM TAMBAH ═══ --}}
<div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
    <div class="border-b border-rule px-5 py-2">
        <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tambah Store</h2>
    </div>
    <form method="POST" action="{{ route('office.stores.store') }}">
        @csrf
        <div class="px-5 py-4">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="flex items-center gap-2 text-xs font-semibold text-red-700">
                        <i class="bi bi-exclamation-circle"></i> Gagal menyimpan:
                    </p>
                    <ul class="mt-1.5 space-y-0.5 text-xs text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>&bull; {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-end gap-3">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="cth: Official Store"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <div class="w-[180px]">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Min Belanja (RMB) <span class="text-danger">*</span></label>
                    <input type="text" name="min_belanja_rmb" required value="{{ old('min_belanja_rmb') }}" placeholder="100"
                           inputmode="numeric" pattern="[0-9]*"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <div class="w-[170px]">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Ongkir Lokal (%) <span class="text-danger">*</span></label>
                    <input type="text" name="ongkir_lokal_cn_percent" required value="{{ old('ongkir_lokal_cn_percent') }}" placeholder="10"
                           inputmode="numeric" pattern="[0-9]*"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <button class="h-11 shrink-0 rounded-lg bg-black px-5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    + Tambah
                </button>
            </div>
            <p class="mt-2 flex items-center gap-1 text-[10px] text-ink/40">
                <i class="bi bi-info-circle" style="font-size:0.7rem;"></i>
                Angka bulat tanpa desimal &middot; Store digunakan sebagai tier ongkir di produk &amp; game
            </p>
        </div>
    </form>
</div>

{{-- ═══ BULK ACTIONS + LIST ═══ --}}
<div class="mt-4 overflow-hidden rounded-xl border border-rule bg-white">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-2.5">
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-ink/70">
                <input type="checkbox" id="store-select-all" class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                Pilih Semua
            </label>
            <span class="text-xs text-ink/45" id="store-selected-info">0 terpilih</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="bulk-save"
                    class="rounded-lg bg-black px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink disabled:opacity-40">
                <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan
            </button>
            <button type="button" id="bulk-delete"
                    class="rounded-lg border border-danger/30 px-3.5 py-2 text-xs font-semibold text-danger transition-colors hover:border-danger hover:bg-red-50 disabled:opacity-40">
                <i class="bi bi-trash me-1" style="font-size:0.85rem;"></i> Hapus Terpilih
            </button>
        </div>
    </div>

    <div id="stores-list">
        @if ($stores->isNotEmpty())
            <div class="flex items-center gap-3 border-y border-rule bg-[#F8F9FA] px-5 py-2">
                <div class="w-7"></div>
                <div class="flex-1 font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Nama</div>
                <div class="w-[170px] font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Min Belanja (RMB)</div>
                <div class="w-[160px] font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Ongkir Lokal (%)</div>
                <div class="w-[140px] font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Info</div>
                <div class="w-[70px] text-right font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Status</div>
            </div>

            @foreach ($stores as $store)
                <div class="flex items-center gap-3 border-b border-rule/50 px-5 py-3 transition-colors hover:bg-paper/15" data-store-row>
                    <div class="w-7">
                        <input type="checkbox" class="store-checkbox h-4 w-4 rounded border-rule" data-id="{{ $store->id }}" style="accent-color:#43A047">
                    </div>

                    {{-- Name — EDITABLE: ada border terlihat --}}
                    <div class="min-w-0 flex-1">
                        <input type="text" value="{{ $store->name }}" data-field="name" data-orig="{{ $store->name }}"
                               class="w-full rounded-md border border-rule bg-white px-2.5 py-1.5 text-sm text-ink transition-colors hover:border-ink/50 focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent/30">
                    </div>

                    {{-- Min Belanja — EDITABLE: border terlihat, tanpa spinner, tanpa .00 --}}
                    <div class="w-[170px]">
                        <input type="text" value="{{ (int) $store->min_belanja_rmb }}"
                               inputmode="numeric" pattern="[0-9]*"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               data-field="min_belanja_rmb" data-orig="{{ (int) $store->min_belanja_rmb }}"
                               class="w-full rounded-md border border-rule bg-white px-2.5 py-1.5 text-sm text-ink transition-colors hover:border-ink/50 focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent/30">
                    </div>

                    {{-- Ongkir Lokal — EDITABLE: border terlihat, tanpa spinner, tanpa .00 --}}
                    <div class="w-[160px]">
                        <input type="text" value="{{ (int) $store->ongkir_lokal_cn_percent }}"
                               inputmode="numeric" pattern="[0-9]*"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               data-field="ongkir_lokal_cn_percent" data-orig="{{ (int) $store->ongkir_lokal_cn_percent }}"
                               class="w-full rounded-md border border-rule bg-white px-2.5 py-1.5 text-sm text-ink transition-colors hover:border-ink/50 focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent/30">
                    </div>

                    {{-- Info — NON-EDITABLE: flat text, tanpa border, lebih redup --}}
                    <div class="w-[140px]">
                        <span class="text-[11px] text-ink/40 select-none">{{ $store->products_count }} produk &middot; {{ $store->games_count }} game</span>
                    </div>

                    <div class="w-[70px] text-right">
                        <span class="text-[10px] text-ink/45 store-status" data-id="{{ $store->id }}"></span>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-xs text-ink/50"><strong class="text-ink/70">{{ $stores->count() }}</strong> store</p>
                <p class="text-[10px] text-ink/35">Edit langsung di baris &rarr; Simpan Perubahan</p>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-14">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                    <i class="bi bi-shop text-ink/25" style="font-size:2rem;"></i>
                </div>
                <p class="text-sm font-medium text-ink/60">Belum ada store</p>
                <p class="text-xs text-ink/40">Tambahkan store pertama di form atas.</p>
            </div>
        @endif
    </div>
</div>

<div class="mt-4 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
    <p class="flex items-start gap-2 text-xs text-ink/65">
        <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
        Store digunakan sebagai <strong class="mx-0.5">tier ongkir</strong> di produk &amp; game. Store yang masih punya produk/game tidak bisa dihapus.
    </p>
</div>

@php
    $storeCfg = ['csrf' => csrf_token(), 'baseUrl' => route('office.stores.index')];
@endphp
<script type="application/json" id="store-page-config">{!! json_encode($storeCfg) !!}</script>

<script>
(function () {
    var config = JSON.parse(document.getElementById('store-page-config').textContent);

    var list = document.getElementById('stores-list');
    var selectAll = document.getElementById('store-select-all');
    var info = document.getElementById('store-selected-info');

    function updateCounter() {
        if (!list || !info) return;
        info.textContent = list.querySelectorAll('.store-checkbox:checked').length + ' terpilih';
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            list.querySelectorAll('.store-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
            updateCounter();
        });
    }
    if (list) {
        list.addEventListener('change', function (e) {
            if (e.target.classList.contains('store-checkbox')) updateCounter();
        });
    }

    var saveBtn = document.getElementById('bulk-save');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            var rows = list.querySelectorAll('[data-store-row]');
            var pending = [];

            rows.forEach(function (row) {
                var fields = {}; var changed = false;
                row.querySelectorAll('[data-field]').forEach(function (el) {
                    var val = el.value;
                    if (val !== el.getAttribute('data-orig')) changed = true;
                    fields[el.getAttribute('data-field')] = val;
                });
                if (changed) {
                    pending.push({ row: row, id: row.querySelector('.store-checkbox').getAttribute('data-id'), fields: fields });
                }
            });

            if (!pending.length) { alert('Tidak ada perubahan.'); return; }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1">&#10227;</span> Menyimpan...';
            var done = 0;

            pending.forEach(function (item) {
                var form = new FormData();
                form.append('_token', config.csrf);
                form.append('_method', 'PUT');
                form.append('name', item.fields.name);
                form.append('min_belanja_rmb', item.fields.min_belanja_rmb);
                form.append('ongkir_lokal_cn_percent', item.fields.ongkir_lokal_cn_percent);

                fetch(config.baseUrl + '/' + item.id, {
                    method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                })
                .then(function (r) {
                    if (r.ok) {
                        item.row.querySelectorAll('[data-field]').forEach(function (el) { el.setAttribute('data-orig', el.value); });
                        var s = item.row.querySelector('.store-status');
                        s.textContent = '\u2713'; s.className = 'text-[10px] text-[#43A047] store-status';
                    } else {
                        var s = item.row.querySelector('.store-status');
                        s.textContent = '\u2717'; s.className = 'text-[10px] text-danger store-status';
                    }
                })
                .catch(function () {
                    var s = item.row.querySelector('.store-status');
                    s.textContent = '\u2717'; s.className = 'text-[10px] text-danger store-status';
                })
                .finally(function () {
                    done++;
                    if (done === pending.length) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan';
                    }
                });
            });
        });
    }

    var delBtn = document.getElementById('bulk-delete');
    if (delBtn) {
        delBtn.addEventListener('click', function () {
            var checked = list.querySelectorAll('.store-checkbox:checked');
            if (!checked.length) { alert('Pilih store yang mau dihapus.'); return; }
            if (!confirm('Hapus ' + checked.length + ' store terpilih?')) return;

            delBtn.disabled = true;
            var done = 0;

            checked.forEach(function (cb) {
                var form = new FormData();
                form.append('_token', config.csrf);
                form.append('_method', 'DELETE');

                fetch(config.baseUrl + '/' + cb.getAttribute('data-id'), {
                    method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                })
                .then(function (r) {
                    if (r.ok) cb.closest('[data-store-row]').remove();
                    else {
                        var s = cb.closest('[data-store-row]').querySelector('.store-status');
                        if (s) { s.textContent = '\u2717 ditolak'; s.className = 'text-[10px] text-danger store-status'; }
                    }
                })
                .finally(function () {
                    done++;
                    if (done === checked.length) {
                        delBtn.disabled = false;
                        alert('Selesai.');
                    }
                });
            });
        });
    }
})();
</script>
@endsection