@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
    <style>
        .user-row { border-bottom: 1.5px solid #f5f5f5 !important; }
        .user-thead { border-top: 1.5px solid #f5f5f5 !important; border-bottom: 1px solid #f5f5f5 !important; }
    </style>

    @php
        $stats = [
            'total' => $users->total(),
            'admin' => $users->whereIn('role', ['admin', 'super_admin'])->count(),
            'customer' => $users->where('role', 'customer')->count(),
        ];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengguna</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Users</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#43A047] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['admin'] }} Admin</span>
            <span class="rounded-full bg-[#9E9E9E] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['customer'] }} Customer</span>
            <a href="{{ route('office.users.create') }}" class="ml-2 rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah User</a>
        </div>
    </div>

    {{-- ═══ SEARCH ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="border-b border-rule px-5 py-2">
            <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Cari User</h2>
        </div>
        <form method="GET" class="px-5 py-4">
            <div class="flex items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Kata Kunci</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / HP"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <button class="h-11 shrink-0 rounded-lg bg-black px-5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    <i class="bi bi-search me-1" style="font-size:0.85rem;"></i> Cari
                </button>
                @if($q)
                    <a href="{{ route('office.users.index') }}" class="flex h-11 shrink-0 items-center rounded-lg border border-rule px-4 text-sm text-ink/60 transition-colors hover:border-ink hover:text-ink">
                        <i class="bi bi-x-circle me-1" style="font-size:0.85rem;"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ═══ BULK ACTIONS + TABLE ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-2.5">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" id="user-select-all" class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                    Pilih Semua
                </label>
                <span class="text-xs text-ink/45" id="user-selected-info">0 terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="user-bulk-save"
                        class="rounded-lg bg-black px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink disabled:opacity-40">
                    <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan
                </button>
            </div>
        </div>

        @if ($users->isNotEmpty())
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-9">
                    <col>
                    <col class="w-[200px]">
                    <col class="w-[130px]">
                    <col class="w-[110px]">
                    <col class="w-[220px]">
                </colgroup>
                <thead>
                    <tr class="user-thead bg-[#F8F9FA]">
                        <th class="px-2 py-2"></th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Nama
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Email
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">No HP</th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Role
                        </th>
                        <th class="px-2 py-2 text-right font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    @foreach ($users as $user)
                        <tr class="user-row transition-colors hover:bg-paper/15" data-user-row>
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" class="user-checkbox h-4 w-4 rounded border-rule" data-id="{{ $user->id }}" style="accent-color:#43A047">
                            </td>

                            {{-- Nama --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $user->name }}" data-field="name" data-orig="{{ $user->name }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- Email --}}
                            <td class="px-2 py-3">
                                <input type="text" value="{{ $user->email }}" data-field="email" data-orig="{{ $user->email }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink/75"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            {{-- No HP (non-editable) --}}
                            <td class="px-2 py-3">
                                <span class="text-[11px] text-ink/40 select-none">{{ $user->no_hp ?? '—' }}</span>
                            </td>

                            {{-- Role --}}
                            <td class="px-2 py-3">
@if (auth()->user()->can('changeRole', $user))
                                <select data-field="role" data-orig="{{ $user->role }}"
                                        class="w-full px-2 py-1.5 text-sm text-ink"
                                        style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                        onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                        onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                                    <option value="crush" @selected($user->role === 'crush')>crush</option>
                                    <option value="customer" @selected($user->role === 'customer')>customer</option>
                                    <option value="admin" @selected($user->role === 'admin')>admin</option>
                                    <option value="super_admin" @selected($user->role === 'super_admin')>super_admin</option>
                                </select>
                                @else
                                    <span class="inline-block rounded-full bg-[#9E9E9E] px-2.5 py-1 text-[10px] font-bold text-white">{{ $user->role }}</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-2 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('office.users.edit', $user) }}"
                                       class="inline-flex items-center gap-1 rounded-md border border-rule px-2 py-1 text-[10px] text-ink/70 transition-colors hover:border-ink hover:text-ink">
                                        <i class="bi bi-pencil" style="font-size:0.65rem;"></i> Edit
                                    </a>
                                    @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
                                        <button type="button"
                                                class="inline-flex items-center gap-1 rounded-md border border-rule px-2 py-1 text-[10px] text-ink/70 transition-colors hover:border-ink hover:text-ink"
                                                onclick="document.getElementById('notify-{{ $user->id }}').showModal()">
                                            <i class="bi bi-bell" style="font-size:0.65rem;"></i> Notif
                                        </button>
                                    @endif
                                    @if (\App\Models\Spammer::blocksIdentity($user->email, $user->no_hp))
                                        <button type="button"
                                                class="inline-flex items-center gap-1 rounded-md border border-danger/30 px-2 py-1 text-[10px] text-danger transition-colors hover:border-danger hover:bg-red-50"
                                                onclick="document.getElementById('del-{{ $user->id }}').showModal()">
                                            <i class="bi bi-trash" style="font-size:0.65rem;"></i>
                                        </button>
                                        <button type="button"
                                                class="inline-flex items-center gap-1 rounded-md border border-danger/30 px-2 py-1 text-[10px] text-danger transition-colors hover:border-danger hover:bg-red-50"
                                                onclick="document.getElementById('fdel-{{ $user->id }}').showModal()">
                                            <i class="bi bi-x-octagon" style="font-size:0.65rem;"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-xs text-ink/50"><strong class="text-ink/70">{{ $users->count() }}</strong> user di halaman ini</p>
                <p class="text-[10px] text-ink/35">Edit langsung di baris &rarr; Simpan Perubahan</p>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-14">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                    <i class="bi bi-people text-ink/25" style="font-size:2rem;"></i>
                </div>
                <p class="text-sm font-medium text-ink/60">Tidak ada user ditemukan</p>
                @if($q)
                    <p class="text-xs text-ink/40">Coba kata kunci lain atau reset pencarian.</p>
                @else
                    <p class="text-xs text-ink/40">Belum ada user terdaftar.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif

    {{-- ═══ INFO ═══ --}}
    <div class="mt-5 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
        <p class="flex items-start gap-2 text-xs text-ink/65">
            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
            Edit nama, email, dan role langsung di tabel lalu klik Simpan Perubahan.
            Hard Delete / Forever Delete hanya muncul untuk user yang diblokir sistem.
        </p>
    </div>

    {{-- ═══ DIALOGS (Hard Delete / Forever Delete / Notify) ═══ --}}
    @foreach ($users as $user)
        @if (\App\Models\Spammer::blocksIdentity($user->email, $user->no_hp))
            <dialog id="del-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
                <form method="POST" action="{{ route('office.users.destroy', $user) }}" class="p-5">
                    @csrf @method('DELETE')
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50">
                            <i class="bi bi-trash text-danger" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Hard Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                            <p class="mt-0.5 text-xs text-ink/55">Ketik email persis untuk konfirmasi.</p>
                        </div>
                    </div>
                    <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                           oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                           class="mt-3 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-paper" onclick="document.getElementById('del-{{ $user->id }}').close()">Batal</button>
                        <button type="submit" disabled class="rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white transition-opacity disabled:opacity-40">Hapus</button>
                    </div>
                </form>
            </dialog>

            <dialog id="fdel-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
                <form method="POST" action="{{ route('office.users.forever-delete', $user) }}" class="p-5">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50">
                            <i class="bi bi-x-octagon text-danger" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Forever Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                            <p class="mt-0.5 text-xs text-danger">Tidak dapat dibatalkan. Ketik email persis untuk konfirmasi.</p>
                        </div>
                    </div>
                    <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                           oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                           class="mt-3 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-paper" onclick="document.getElementById('fdel-{{ $user->id }}').close()">Batal</button>
                        <button type="submit" disabled class="rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white transition-opacity disabled:opacity-40">Hapus Permanen</button>
                    </div>
                </form>
            </dialog>
        @endif

        @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
            <dialog id="notify-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
                <form method="POST" action="{{ route('office.users.notify', $user) }}" class="p-5">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50">
                            <i class="bi bi-bell text-blue-600" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Kirim Notifikasi</p>
                            <p class="mt-0.5 text-xs text-ink/55">ke {{ $user->name }}</p>
                        </div>
                    </div>
                    <input type="text" name="title" placeholder="Judul notifikasi" required maxlength="255"
                           class="mt-3 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <textarea name="message" rows="3" required maxlength="1000" placeholder="Pesan..."
                              class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                    <input type="text" name="link" placeholder="Link (opsional)" maxlength="255"
                           class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-paper" onclick="document.getElementById('notify-{{ $user->id }}').close()">Batal</button>
                        <button type="submit" class="rounded-lg bg-black px-3 py-1.5 text-xs font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Kirim</button>
                    </div>
                </form>
            </dialog>
        @endif
    @endforeach

    {{-- ═══ MODAL ═══ --}}
    <div id="user-modal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:360px;box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="user-modal-icon" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0;"></div>
                <div>
                    <p id="user-modal-title" style="font-size:14px;font-weight:600;color:#222;margin:0;">Berhasil</p>
                    <p id="user-modal-text" style="font-size:12px;color:#888;margin:4px 0 0 0;"></p>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
                <button id="user-modal-ok" style="padding:8px 20px;border-radius:8px;border:none;background:#111;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Mengerti</button>
            </div>
        </div>
    </div>

    {{-- CONFIG --}}
    @php
        $userCfg = ['csrf' => csrf_token(), 'baseUrl' => route('office.users.index')];
    @endphp
    <script type="application/json" id="user-page-config">{!! json_encode($userCfg) !!}</script>

    <script>
    (function () {
        var config = JSON.parse(document.getElementById('user-page-config').textContent);
        var list = document.getElementById('user-list');

        // ═══ MODAL HELPER ═══
        var modalEl = document.getElementById('user-modal');
        var modalIcon = document.getElementById('user-modal-icon');
        var modalTitle = document.getElementById('user-modal-title');
        var modalText = document.getElementById('user-modal-text');
        var modalOk = document.getElementById('user-modal-ok');

        function showUserModal(type, title, text) {
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
            modalOk.onclick = function () { modalEl.style.display = 'none'; };
            modalEl.style.display = 'flex';
        }

        // ── Clear all selections ──
        var selectAll = document.getElementById('user-select-all');

        function clearSelections() {
            document.querySelectorAll('.user-checkbox').forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            var selInfo = document.getElementById('user-selected-info');
            if (selInfo) selInfo.textContent = '0 terpilih';
        }

        // ── Counter ──
        var info = document.getElementById('user-selected-info');

        function updateCounter() {
            if (!info) return;
            info.textContent = document.querySelectorAll('.user-checkbox:checked').length + ' terpilih';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.user-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
                updateCounter();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('user-checkbox')) updateCounter();
        });

        // ── Bulk save ──
        var saveBtn = document.getElementById('user-bulk-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-user-row]');
                var pending = [];

                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        var val = el.value;
                        if (val !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = val;
                    });
                    if (changed) {
                        pending.push({ row: row, id: row.querySelector('.user-checkbox').getAttribute('data-id'), fields: fields });
                    }
                });

                if (!pending.length) {
                    showUserModal('info', 'Tidak ada perubahan', 'Semua data masih sama. Tidak ada yang perlu disimpan.');
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
                    form.append('email', item.fields.email);
                    if (item.fields.role !== undefined) { form.append('role', item.fields.role); }

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
                                showUserModal('success', 'Berhasil disimpan', success + ' user berhasil diperbarui.');
                            } else if (success === 0) {
                                showUserModal('error', 'Gagal menyimpan', 'Semua ' + fail + ' user gagal disimpan. Coba lagi.');
                            } else {
                                showUserModal('info', 'Sebagian berhasil', success + ' berhasil, ' + fail + ' gagal disimpan.');
                            }
                        }
                    });
                });
            });
        }
    })();
    </script>
@endsection