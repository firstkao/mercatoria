@extends('admin.layouts.admin')

@section('title', 'Tag')

@section('content')
    <style>
        .tag-row { border-bottom: 1.5px solid #f5f5f5 !important; }
        .tag-thead { border-top: 1.5px solid #f5f5f5 !important; border-bottom: 1px solid #f5f5f5 !important; }
    </style>

    @php
        $stats = [
            'total' => $tags->count(),
        ];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Tag</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#43A047] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['total'] }} Tag</span>
        </div>
    </div>

    {{-- ═══ FORM TAMBAH ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="border-b border-rule px-5 py-2">
            <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tambah Tag</h2>
        </div>

        <form method="POST" action="{{ route('office.tags.store') }}">
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
                        <input type="text" name="name" id="tag-name-input" required value="{{ old('name') }}" placeholder="cth: PRESALE"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="w-[150px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Slug <span class="text-ink/35">auto</span></label>
                        <input type="text" name="slug" id="tag-slug-input" value="{{ old('slug') }}" placeholder="presale"
                               class="h-11 w-full rounded-lg border border-rule bg-paper/30 px-3 font-mono text-xs text-ink">
                    </div>

                    <button class="h-11 shrink-0 rounded-lg bg-black px-5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                        + Tambah
                    </button>
                </div>

                <p class="mt-2 flex items-center gap-1 text-[10px] text-ink/40">
                    <i class="bi bi-info-circle" style="font-size:0.7rem;"></i>
                    Tag otomatis tampil di filter produk &amp; katalog
                </p>
            </div>
        </form>
    </div>

    {{-- ═══ BULK ACTIONS + TABLE ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-2.5">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" id="tag-select-all" class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                    Pilih Semua
                </label>
                <span class="text-xs text-ink/45" id="tag-selected-info">0 terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="tag-bulk-save"
                        class="rounded-lg bg-black px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink disabled:opacity-40">
                    <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan
                </button>
                <button type="button" id="tag-bulk-delete"
                        class="rounded-lg border border-danger/30 px-3.5 py-2 text-xs font-semibold text-danger transition-colors hover:border-danger hover:bg-red-50 disabled:opacity-40">
                    <i class="bi bi-trash me-1" style="font-size:0.85rem;"></i> Hapus Terpilih
                </button>
            </div>
        </div>

        @if ($tags->isNotEmpty())
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-9">
                    <col>
                    <col class="w-[180px]">
                </colgroup>
                <thead>
                    <tr class="tag-thead bg-[#F8F9FA]">
                        <th class="px-2 py-2"></th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Nama
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Slug
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tags as $tag)
                        <tr class="tag-row transition-colors hover:bg-paper/15" data-tag-row>
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" class="tag-checkbox h-4 w-4 rounded border-rule" data-id="{{ $tag->id }}" style="accent-color:#43A047">
                            </td>

                            <td class="px-2 py-3">
                                <input type="text" value="{{ $tag->name }}" data-field="name" data-orig="{{ $tag->name }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            <td class="px-2 py-3">
                                <input type="text" value="{{ $tag->slug }}" data-field="slug" data-orig="{{ $tag->slug }}" placeholder="slug"
                                       class="w-full px-2.5 py-1.5 font-mono text-xs text-ink/75"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-xs text-ink/50"><strong class="text-ink/70">{{ $tags->count() }}</strong> tag</p>
                <p class="text-[10px] text-ink/35">Edit langsung di baris &rarr; Simpan Perubahan</p>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-14">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                    <i class="bi bi-tag text-ink/25" style="font-size:2rem;"></i>
                </div>
                <p class="text-sm font-medium text-ink/60">Belum ada tag</p>
                <p class="text-xs text-ink/40">Tambahkan tag pertama di form atas.</p>
            </div>
        @endif
    </div>

    {{-- ═══ INFO ═══ --}}
    <div class="mt-5 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
        <p class="flex items-start gap-2 text-xs text-ink/65">
            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
            Tag terhubung ke <strong class="mx-0.5">product_tag</strong> pivot.
            Tag yang masih terpakai tidak bisa dihapus.
        </p>
    </div>

    {{-- ═══ MODAL ═══ --}}
    <div id="tag-modal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:360px;box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="tag-modal-icon" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0;"></div>
                <div>
                    <p id="tag-modal-title" style="font-size:14px;font-weight:600;color:#222;margin:0;">Berhasil</p>
                    <p id="tag-modal-text" style="font-size:12px;color:#888;margin:4px 0 0 0;"></p>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
                <button id="tag-modal-cancel" style="display:none;padding:8px 16px;border-radius:8px;border:1px solid #e0e0e0;background:#fff;font-size:12px;font-weight:600;color:#666;cursor:pointer;">Batal</button>
                <button id="tag-modal-ok" style="padding:8px 20px;border-radius:8px;border:none;background:#111;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Mengerti</button>
            </div>
        </div>
    </div>

    {{-- CONFIG --}}
    @php
        $tagConfig = ['csrf' => csrf_token(), 'baseUrl' => route('office.tags.index')];
    @endphp
    <script type="application/json" id="tag-page-config">{!! json_encode($tagConfig) !!}</script>

    <script>
    (function () {
        var config = JSON.parse(document.getElementById('tag-page-config').textContent);

        // ── Auto-slug form tambah ──
        var nameInput = document.getElementById('tag-name-input');
        var slugInput = document.getElementById('tag-slug-input');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
            });
        }

        // ── Auto-slug per row ──
        document.querySelectorAll('[data-tag-row]').forEach(function (row) {
            var nameEl = row.querySelector('[data-field="name"]');
            var slugEl = row.querySelector('[data-field="slug"]');
            if (nameEl && slugEl) {
                nameEl.addEventListener('input', function () {
                    slugEl.value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                });
            }
        });

        // ═══ MODAL HELPER ═══
        var modalEl = document.getElementById('tag-modal');
        var modalIcon = document.getElementById('tag-modal-icon');
        var modalTitle = document.getElementById('tag-modal-title');
        var modalText = document.getElementById('tag-modal-text');
        var modalOk = document.getElementById('tag-modal-ok');
        var modalCancel = document.getElementById('tag-modal-cancel');

        function showTagModal(type, title, text, onOk, showCancel) {
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
                closeTagModal();
                if (onOk) onOk();
            };
            modalCancel.onclick = function () {
                closeTagModal();
            };

            modalEl.style.display = 'flex';
        }

        window.closeTagModal = function () {
            modalEl.style.display = 'none';
        };

        // ── Clear all selections ──
        var selectAll = document.getElementById('tag-select-all');

        function clearSelections() {
            document.querySelectorAll('.tag-checkbox').forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            var selInfo = document.getElementById('tag-selected-info');
            if (selInfo) selInfo.textContent = '0 terpilih';
        }

        // ── Counter ──
        var info = document.getElementById('tag-selected-info');

        function updateCounter() {
            if (!info) return;
            info.textContent = document.querySelectorAll('.tag-checkbox:checked').length + ' terpilih';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.tag-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
                updateCounter();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('tag-checkbox')) updateCounter();
        });

        // ── Bulk save ──
        var saveBtn = document.getElementById('tag-bulk-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = document.querySelectorAll('[data-tag-row]');
                var pending = [];

                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        var val = el.value;
                        if (val !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = val;
                    });
                    if (changed) {
                        pending.push({ row: row, id: row.querySelector('.tag-checkbox').getAttribute('data-id'), fields: fields });
                    }
                });

                if (!pending.length) {
                    showTagModal('info', 'Tidak ada perubahan', 'Semua data masih sama. Tidak ada yang perlu disimpan.');
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
                    form.append('slug', item.fields.slug || '');

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
                                showTagModal('success', 'Berhasil disimpan', success + ' tag berhasil diperbarui.');
                            } else if (success === 0) {
                                showTagModal('error', 'Gagal menyimpan', 'Semua ' + fail + ' tag gagal disimpan. Coba lagi.');
                            } else {
                                showTagModal('info', 'Sebagian berhasil', success + ' berhasil, ' + fail + ' gagal disimpan.');
                            }
                        }
                    });
                });
            });
        }

        // ── Bulk delete ──
        var delBtn = document.getElementById('tag-bulk-delete');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                var checked = document.querySelectorAll('.tag-checkbox:checked');
                if (!checked.length) {
                    showTagModal('info', 'Belum ada yang dipilih', 'Centang tag yang mau dihapus terlebih dahulu.');
                    return;
                }

                showTagModal('info', 'Konfirmasi hapus', 'Hapus ' + checked.length + ' tag terpilih? Tag yang masih terpakai akan ditolak.', function () {
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
                                var tr = cb.closest('[data-tag-row]');
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
                                    showTagModal('success', 'Berhasil dihapus', deleted + ' tag berhasil dihapus.');
                                } else if (deleted === 0) {
                                    showTagModal('error', 'Gagal dihapus', 'Tag yang dipilih tidak bisa dihapus (masih terpakai).');
                                } else {
                                    showTagModal('info', 'Sebagian terhapus', deleted + ' terhapus, ' + rejected + ' ditolak.');
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