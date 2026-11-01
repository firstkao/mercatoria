@extends('admin.layouts.admin')

@section('title', 'Stores')

@section('content')
    <style>
        .store-row { border-bottom: 1.5px solid #f5f5f5 !important; }
        .store-thead { border-top: 1.5px solid #f5f5f5 !important; border-bottom: 1px solid #f5f5f5 !important; }
    </style>

    @php
        $stats = ['total' => $stores->count()];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Stores</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#43A047] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['total'] }} Store</span>
        </div>
    </div>

    {{-- ═══ FORM TAMBAH ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
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
                    <div class="w-[220px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}" placeholder="cth: Official Store"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="w-[180px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Min Belanja (RMB) <span class="text-danger">*</span></label>
                        <input type="text" name="min_belanja_rmb" required value="{{ old('min_belanja_rmb') }}" placeholder="100"
                               inputmode="numeric" pattern="[0-9]*"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="w-[170px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Ongkir Lokal (RMB) <span class="text-danger">*</span></label>
                        <input type="number" name="ongkir_rmb" required min="0" step="0.01" value="{{ old('ongkir_rmb', '0') }}" placeholder="7"
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

    {{-- ═══ BULK ACTIONS + TABLE ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
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

        @if ($stores->isNotEmpty())
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-9">
                    <col>
                    <col class="w-[170px]">
                    <col class="w-[160px]">
                    <col class="w-[140px]">
                    <col class="w-[60px]">
                </colgroup>
                <thead>
                    <tr class="store-thead bg-[#F8F9FA]">
                        <th class="px-2 py-2"></th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Nama
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Min Belanja (RMB)
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Ongkir Lokal (%)
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Info</th>
                        <th class="px-2 py-2 text-center font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Aktif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stores as $store)
                        <tr class="store-row transition-colors hover:bg-paper/15" data-store-row>
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" class="store-checkbox h-4 w-4 rounded border-rule" data-id="{{ $store->id }}" style="accent-color:#43A047">
                            </td>

                            {{-- Nama --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $store->name }}" data-field="name" data-orig="{{ $store->name }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Min Belanja --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ (int) $store->min_belanja_rmb }}"
                                       inputmode="numeric" pattern="[0-9]*"
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                       data-field="min_belanja_rmb" data-orig="{{ (int) $store->min_belanja_rmb }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Ongkir Lokal --}}
                            <td class="px-2 py-3">
                                <input type="number" min="0" step="0.01" value="{{ $store->ongkir_rmb }}"
                                       data-field="ongkir_rmb" data-orig="{{ $store->ongkir_rmb }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Info (non-editable) --}}
                            <td class="px-2 py-3">
                                <span class="text-[11px] text-ink/40 select-none">{{ $store->products_count }} produk &middot; {{ $store->games_count }} game</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-2 py-3 text-center">
                                <span class="text-[10px] text-ink/45 store-status" data-id="{{ $store->id }}"></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

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

    {{-- ═══ INFO ═══ --}}
    <div class="mt-5 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
        <p class="flex items-start gap-2 text-xs text-ink/65">
            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
            Store digunakan sebagai <strong class="mx-0.5">tier ongkir</strong> di produk &amp; game.
            Store yang masih punya produk/game tidak bisa dihapus.
        </p>
    </div>

    {{-- ═══ MODAL ═══ --}}
    <div id="store-modal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:360px;box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="store-modal-icon" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0;"></div>
                <div>
                    <p id="store-modal-title" style="font-size:14px;font-weight:600;color:#222;margin:0;">Berhasil</p>
                    <p id="store-modal-text" style="font-size:12px;color:#888;margin:4px 0 0 0;"></p>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
                <button id="store-modal-cancel" style="display:none;padding:8px 16px;border-radius:8px;border:1px solid #e0e0e0;background:#fff;font-size:12px;font-weight:600;color:#666;cursor:pointer;">Batal</button>
                <button id="store-modal-ok" style="padding:8px 20px;border-radius:8px;border:none;background:#111;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Mengerti</button>
            </div>
        </div>
    </div>

    {{-- CONFIG --}}
    @php
        $storeCfg = ['csrf' => csrf_token(), 'baseUrl' => route('office.stores.index')];
    @endphp
    <script type="application/json" id="store-page-config">{!! json_encode($storeCfg) !!}</script>

    <script>
    (function () {
        var config = JSON.parse(document.getElementById('store-page-config').textContent);

        // ═══ MODAL HELPER ═══
        var modalEl = document.getElementById('store-modal');
        var modalIcon = document.getElementById('store-modal-icon');
        var modalTitle = document.getElementById('store-modal-title');
        var modalText = document.getElementById('store-modal-text');
        var modalOk = document.getElementById('store-modal-ok');
        var modalCancel = document.getElementById('store-modal-cancel');

        function showStoreModal(type, title, text, onOk, showCancel) {
            if (type === 'success') {
                modalIcon.style.background = '#f0fdf4';
                modalIcon.innerHTML = '<i class="bi bi-check-lg" style="font-size:1.3rem;color:#43A047;"></i>';
            } else if (type === 'error') {
                modalIcon.style.background = '#fef2f2';
                modalIcon.innerHTML = '<i class="bi bi-x-lg" style="font-size:1.15rem;color:#ef4444;"></i>';
            } else {
                modalIcon.style.background = '#fffbeb';
                modalIcon.innerHTML = '<i class="bi bi-info-lg" style="font-size:1.15rem;color:#f59e0b;"></i>';
            }

            modalTitle.textContent = title;
            modalText.textContent = text;

            if (showCancel) {
                modalCancel.style.display = 'inline-block';
                modalOk.textContent = 'Ya, Hapus';
                modalOk.style.background = '#dc2626';
            } else {
                modalCancel.style.display = 'none';
                modalOk.textContent = 'Mengerti';
                modalOk.style.background = '#111';
            }

            modalOk.onclick = function () {
                closeStoreModal();
                if (onOk) onOk();
            };
            modalCancel.onclick = function () {
                closeStoreModal();
            };

            modalEl.style.display = 'flex';
        }

        window.closeStoreModal = function () {
            modalEl.style.display = 'none';
        };

        // ── Clear all selections ──
        var selectAll = document.getElementById('store-select-all');

        function clearSelections() {
            document.querySelectorAll('.store-checkbox').forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            var selInfo = document.getElementById('store-selected-info');
            if (selInfo) selInfo.textContent = '0 terpilih';
        }

        // ── Counter ──
        var info = document.getElementById('store-selected-info');

        function updateCounter() {
            if (!info) return;
            info.textContent = document.querySelectorAll('.store-checkbox:checked').length + ' terpilih';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.store-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
                updateCounter();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('store-checkbox')) updateCounter();
        });

        // ── Bulk save ──
        var saveBtn = document.getElementById('bulk-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = document.querySelectorAll('[data-store-row]');
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

                if (!pending.length) {
                    showStoreModal('info', 'Tidak ada perubahan', 'Semua data masih sama. Tidak ada yang perlu disimpan.');
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1">&#10227;</span> Menyimpan...';
                var done = 0, success = 0, fail = 0;

                pending.forEach(function (item) {
                    var form = new FormData();
                    form.append('_token', config.csrf);
                    form.append('_method', 'PUT');
                    form.append('name', item.fields.name);
                    form.append('min_belanja_rmb', item.fields.min_belanja_rmb);
                    form.append('ongkir_rmb', item.fields.ongkir_rmb);

                    fetch(config.baseUrl + '/' + item.id, {
                        method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                    })
                    .then(function (r) {
                        if (r.ok) {
                            success++;
                            item.row.querySelectorAll('[data-field]').forEach(function (el) {
                                el.setAttribute('data-orig', el.value);
                            });
                        } else {
                            fail++;
                        }
                    })
                    .catch(function () { fail++; })
                    .finally(function () {
                        done++;
                        if (done === pending.length) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan';
                            clearSelections();

                            if (fail === 0) {
                                showStoreModal('success', 'Berhasil disimpan', success + ' store berhasil diperbarui.');
                            } else if (success === 0) {
                                showStoreModal('error', 'Gagal menyimpan', 'Semua ' + fail + ' store gagal disimpan. Coba lagi.');
                            } else {
                                showStoreModal('info', 'Sebagian berhasil', success + ' berhasil, ' + fail + ' gagal disimpan.');
                            }
                        }
                    });
                });
            });
        }

        // ── Bulk delete ──
        var delBtn = document.getElementById('bulk-delete');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                var checked = document.querySelectorAll('.store-checkbox:checked');
                if (!checked.length) {
                    showStoreModal('info', 'Belum ada yang dipilih', 'Centang store yang mau dihapus terlebih dahulu.');
                    return;
                }

                showStoreModal('info', 'Konfirmasi hapus', 'Hapus ' + checked.length + ' store terpilih? Store yang masih punya produk/game akan ditolak.', function () {
                    delBtn.disabled = true;
                    var done = 0, deleted = 0, rejected = 0;

                    checked.forEach(function (cb) {
                        var form = new FormData();
                        form.append('_token', config.csrf);
                        form.append('_method', 'DELETE');

                        fetch(config.baseUrl + '/' + cb.getAttribute('data-id'), {
                            method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                        })
                        .then(function (r) {
                            if (r.ok) {
                                deleted++;
                                var tr = cb.closest('[data-store-row]');
                                if (tr) tr.remove();
                            } else {
                                rejected++;
                            }
                        })
                        .catch(function () { rejected++; })
                        .finally(function () {
                            done++;
                            if (done === checked.length) {
                                delBtn.disabled = false;
                                clearSelections();

                                if (rejected === 0) {
                                    showStoreModal('success', 'Berhasil dihapus', deleted + ' store berhasil dihapus.');
                                } else if (deleted === 0) {
                                    showStoreModal('error', 'Gagal dihapus', 'Store yang dipilih tidak bisa dihapus (masih punya produk/game).');
                                } else {
                                    showStoreModal('info', 'Sebagian terhapus', deleted + ' terhapus, ' + rejected + ' ditolak.');
                                }
                            }
                        });
                    });
                }, true);
            });
        }
    })();
    </script>
@endsection