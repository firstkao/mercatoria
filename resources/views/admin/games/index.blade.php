@extends('admin.layouts.admin')

@section('title', 'Game')

@section('content')
    <style>
        .game-row { border-bottom: 1.5px solid #f5f5f5 !important; }
        .game-thead { border-top: 1.5px solid #f5f5f5 !important; border-bottom: 1px solid #f5f5f5 !important; }
    </style>

    @php
        $stats = [
            'total' => $games->count(),
            'active' => $games->where('is_active', true)->count(),
            'inactive' => $games->where('is_active', false)->count(),
        ];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Game</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#43A047] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['active'] }} Aktif</span>
            @if ($stats['inactive'] > 0)
                <span class="rounded-full bg-[#9E9E9E] px-2.5 py-1 text-[10px] font-bold text-white">{{ $stats['inactive'] }} Nonaktif</span>
            @endif
        </div>
    </div>

    {{-- ═══ FORM TAMBAH ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="border-b border-rule px-5 py-2">
            <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tambah Game</h2>
        </div>

        <form method="POST" action="{{ route('office.games.store') }}" enctype="multipart/form-data">
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
                    <div class="flex items-center gap-2">
                        <div id="game-add-preview" class="h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-rule bg-paper flex items-center justify-center">
                            <i class="bi bi-controller text-ink/25" style="font-size:1rem;"></i>
                        </div>
                        <label class="flex h-11 w-[110px] shrink-0 cursor-pointer items-center gap-1.5 rounded-lg border border-rule px-3 text-xs text-ink/60 transition-colors hover:border-ink hover:text-ink">
                            <i class="bi bi-cloud-arrow-up" style="font-size:0.85rem;"></i>
                            <span class="game-add-file-label truncate">Logo</span>
                            <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" class="hidden" onchange="previewGameAdd(this)">
                        </label>
                    </div>

                    <div class="w-[220px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="game-name-input" required value="{{ old('name') }}" placeholder="cth: Genshin Impact"
                               class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                    </div>

                    <div class="w-[150px] shrink-0">
                        <label class="block text-xs font-medium text-ink/70 mb-1">Slug <span class="text-ink/35">auto</span></label>
                        <input type="text" name="slug" id="game-slug-input" value="{{ old('slug') }}" placeholder="genshin-impact"
                               class="h-11 w-full rounded-lg border border-rule bg-paper/30 px-3 font-mono text-xs text-ink">
                    </div>

                    <div class="flex h-11 shrink-0 items-center">
                        <label class="flex items-center gap-1.5 text-sm text-ink/70 whitespace-nowrap">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                                   class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                            Aktif
                        </label>
                    </div>

                    <button class="h-11 shrink-0 rounded-lg bg-black px-5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                        + Tambah
                    </button>
                </div>

                <div class="mt-3 flex flex-wrap items-end gap-4">
                    <div class="w-[180px]">
                        <label class="block text-xs font-medium text-ink/70">Default Store</label>
                        <select name="default_store_id" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                            <option value="">&mdash; ikut game &mdash;</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('default_store_id') == $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 pb-2">
                        <span class="text-xs font-medium text-ink/70">Developer:</span>
                        @foreach ($developers as $dev)
                            <label class="flex items-center gap-1.5 text-sm text-ink/75">
                                <input type="checkbox" name="developers[]" value="{{ $dev->id }}"
                                       @checked(in_array($dev->id, old('developers', [])))
                                       class="h-3.5 w-3.5 rounded border-rule" style="accent-color:#43A047">
                                {{ $dev->name }}
                            </label>
                        @endforeach
                        @if ($developers->isEmpty())
                            <span class="text-xs text-ink/40">Belum ada developer.</span>
                        @endif
                    </div>
                </div>

                <p class="mt-2 flex items-center gap-1 text-[10px] text-ink/40">
                    <i class="bi bi-info-circle" style="font-size:0.7rem;"></i>
                    JPG, PNG, WebP &mdash; Maks 5 MB &middot; Game otomatis tampil di navigasi &amp; katalog
                </p>
            </div>
        </form>
    </div>

    {{-- ═══ BULK ACTIONS + TABLE ═══ --}}
    <div class="mt-4 rounded-xl border border-rule bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-2.5">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" id="game-select-all" class="h-4 w-4 rounded border-rule" style="accent-color:#43A047">
                    Pilih Semua
                </label>
                <span class="text-xs text-ink/45" id="game-selected-info">0 terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="game-bulk-save"
                        class="rounded-lg bg-black px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-ink disabled:opacity-40">
                    <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i> Simpan Perubahan
                </button>
                <button type="button" id="game-bulk-delete"
                        class="rounded-lg border border-danger/30 px-3.5 py-2 text-xs font-semibold text-danger transition-colors hover:border-danger hover:bg-red-50 disabled:opacity-40">
                    <i class="bi bi-trash me-1" style="font-size:0.85rem;"></i> Hapus Terpilih
                </button>
            </div>
        </div>

        @if ($games->isNotEmpty())
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-9">
                    <col class="w-11">
                    <col class="w-[200px]">
                    <col class="w-[140px]">
                    <col class="w-[120px]">
                    <col class="w-[180px]">
                    <col class="w-[110px]">
                    <col class="w-[50px]">
                </colgroup>
                <thead>
                    <tr class="game-thead bg-[#F8F9FA]">
                        <th class="px-2 py-2"></th>
                        <th class="px-1 py-2"></th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Nama
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Slug
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Store
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Developer
                        </th>
                        <th class="px-2 py-2 text-left font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Logo
                        </th>
                        <th class="px-2 py-2 text-center font-mono text-[9px] uppercase tracking-[0.12em] text-ink/50">
                            <i class="bi bi-pencil-square me-1 text-ink/25" style="font-size:0.65rem;"></i>Aktif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($games as $game)
                        <tr class="game-row transition-colors hover:bg-paper/15" data-game-row>
                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" class="game-checkbox h-4 w-4 rounded border-rule" data-id="{{ $game->id }}" style="accent-color:#43A047">
                            </td>

                            <td class="px-1 py-3">
                                <div class="h-9 w-9 overflow-hidden rounded" style="border:2px solid #f5f5f5;">
                                    @if ($game->logo_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-paper">
                                            <i class="bi bi-controller text-ink/25" style="font-size:0.8rem;"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-2 py-3">
                                <input type="text" value="{{ $game->name }}" data-field="name" data-orig="{{ $game->name }}"
                                       class="w-full px-2.5 py-1.5 text-sm text-ink"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            <td class="px-2 py-3">
                                <input type="text" value="{{ $game->slug }}" data-field="slug" data-orig="{{ $game->slug }}" placeholder="slug"
                                       class="w-full px-2.5 py-1.5 font-mono text-xs text-ink/75"
                                       style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s,box-shadow .15s;"
                                       onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                       onfocus="this.style.borderColor='#aaa';this.style.boxShadow='0 0 0 2px rgba(0,0,0,0.05)'" onblur="this.style.borderColor='#f5f5f5';this.style.boxShadow='none'">
                            </td>

                            <td class="px-2 py-3">
                                <select data-field="default_store_id" data-orig="{{ $game->default_store_id }}"
                                        class="w-full cursor-pointer px-2 py-1.5 text-xs text-ink/75"
                                        style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;outline:none;transition:border-color .15s;"
                                        onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'"
                                        onfocus="this.style.borderColor='#aaa'" onblur="this.style.borderColor='#f5f5f5'">
                                    <option value="">&mdash;</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}" @selected($game->default_store_id == $store->id)>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Developer dropdown --}}
                            <td class="relative px-2 py-3">
                                <details class="dev-dropdown" data-orig-devs="{{ $game->developers->pluck('id')->implode(',') }}">
                                    <summary class="flex w-full cursor-pointer list-none items-center justify-between gap-1 px-2.5 py-1.5"
                                             style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;transition:border-color .15s,box-shadow .15s;"
                                             onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'">
                                        <span class="dev-badges flex min-w-0 flex-1 items-center gap-1 overflow-hidden">
                                            @forelse ($game->developers as $dev)
                                                <span class="shrink-0 whitespace-nowrap rounded-full bg-paper px-2 py-0.5 text-[9px] font-semibold text-ink/60">{{ $dev->name }}</span>
                                            @empty
                                                <span class="text-[10px] text-ink/30">Pilih&hellip;</span>
                                            @endforelse
                                        </span>
                                        <i class="bi bi-chevron-down shrink-0 text-ink/40" style="font-size:0.6rem;"></i>
                                    </summary>
                                    <div class="absolute left-0 top-full z-50 mt-1 w-[200px] rounded-lg bg-white py-1 shadow-xl" style="border:1px solid #f0f0f0;">
                                        <p class="px-3 pb-1.5 pt-1 font-mono text-[8px] uppercase tracking-[0.14em] text-ink/40" style="border-bottom:1px solid #f5f5f5;">Pilih Developer</p>
                                        @foreach ($developers as $dev)
                                            <label class="flex cursor-pointer items-center gap-2 px-3 py-2 text-xs text-ink/75 transition-colors hover:bg-paper/50">
                                                <input type="checkbox" class="dev-check h-3.5 w-3.5 rounded border-rule" style="accent-color:#43A047"
                                                       value="{{ $dev->id }}" data-dev-name="{{ $dev->name }}"
                                                       @checked($game->developers->pluck('id')->contains($dev->id))>
                                                {{ $dev->name }}
                                            </label>
                                        @endforeach
                                        @if ($developers->isEmpty())
                                            <p class="px-3 py-2 text-[10px] text-ink/40">Belum ada developer.</p>
                                        @endif
                                    </div>
                                </details>
                            </td>

                            <td class="px-2 py-3">
                                <div class="flex items-center gap-1.5">
                                    <div class="game-row-preview flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded bg-paper" style="border:1px solid #eaeaea;">
                                        <i class="bi bi-image text-ink/20" style="font-size:0.65rem;"></i>
                                    </div>
                                    <label class="inline-flex shrink-0 cursor-pointer items-center gap-1 px-2 py-1.5 text-[9px] text-ink/55"
                                           style="border:2px solid #f5f5f5;background:#fff;border-radius:6px;transition:border-color .15s;"
                                           onmouseover="this.style.borderColor='#e0e0e0'" onmouseout="this.style.borderColor='#f5f5f5'">
                                        <i class="bi bi-cloud-arrow-up" style="font-size:0.7rem;"></i>
                                        <span class="game-row-file-label">Ganti</span>
                                        <input type="file" data-field="logo" accept=".jpg,.jpeg,.png,.webp" class="hidden game-row-file-input">
                                    </label>
                                </div>
                            </td>

                            <td class="px-2 py-3 text-center">
                                <input type="checkbox" data-field="is_active" data-orig="{{ $game->is_active ? '1' : '0' }}"
                                       @checked($game->is_active)
                                       class="h-4 w-4 cursor-pointer rounded border-rule" style="accent-color:#43A047">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-xs text-ink/50"><strong class="text-ink/70">{{ $games->count() }}</strong> game</p>
                <p class="text-[10px] text-ink/35">Edit langsung di baris &rarr; Simpan Perubahan</p>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-14">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-paper">
                    <i class="bi bi-controller text-ink/25" style="font-size:2rem;"></i>
                </div>
                <p class="text-sm font-medium text-ink/60">Belum ada game</p>
                <p class="text-xs text-ink/40">Tambahkan game pertama di form atas.</p>
            </div>
        @endif
    </div>

    {{-- ═══ INFO ═══ --}}
    <div class="mt-5 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
        <p class="flex items-start gap-2 text-xs text-ink/65">
            <i class="bi bi-info-circle mt-0.5 text-blue-500" style="font-size:0.8rem;"></i>
            Game terhubung ke <strong class="mx-0.5">products.game_id</strong> dan <strong class="mx-0.5">developer_game</strong> pivot.
            Game yang masih punya produk tidak bisa dihapus.
        </p>
    </div>

    {{-- ═══ MODAL ═══ --}}
    <div id="game-modal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:360px;box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="game-modal-icon" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0;"></div>
                <div>
                    <p id="game-modal-title" style="font-size:14px;font-weight:600;color:#222;margin:0;">Berhasil</p>
                    <p id="game-modal-text" style="font-size:12px;color:#888;margin:4px 0 0 0;"></p>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
                <button id="game-modal-cancel" style="display:none;padding:8px 16px;border-radius:8px;border:1px solid #e0e0e0;background:#fff;font-size:12px;font-weight:600;color:#666;cursor:pointer;">Batal</button>
                <button id="game-modal-ok" style="padding:8px 20px;border-radius:8px;border:none;background:#111;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Mengerti</button>
            </div>
        </div>
    </div>

    {{-- CONFIG --}}
    @php
        $devConfig = ['csrf' => csrf_token(), 'baseUrl' => route('office.games.index')];
    @endphp
    <script type="application/json" id="game-page-config">{!! json_encode($devConfig) !!}</script>

    <script>
    (function () {
        var config = JSON.parse(document.getElementById('game-page-config').textContent);

        // ── Auto-slug ──
        var nameInput = document.getElementById('game-name-input');
        var slugInput = document.getElementById('game-slug-input');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
            });
        }

        // ── Preview logo form tambah ──
        window.previewGameAdd = function (input) {
            var preview = document.getElementById('game-add-preview');
            var label = input.closest('label').querySelector('.game-add-file-label');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) { preview.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover" alt="">'; };
                reader.readAsDataURL(input.files[0]);
                if (label) label.textContent = input.files[0].name.substring(0, 10) + '..';
            } else {
                preview.innerHTML = '<i class="bi bi-controller text-ink/25" style="font-size:1rem;"></i>';
                if (label) label.textContent = 'Logo';
            }
        };

        // ── Auto-slug per row ──
        document.querySelectorAll('[data-game-row]').forEach(function (row) {
            var nameEl = row.querySelector('[data-field="name"]');
            var slugEl = row.querySelector('[data-field="slug"]');
            if (nameEl && slugEl) {
                nameEl.addEventListener('input', function () {
                    slugEl.value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                });
            }
        });

        // ── Dev IDs helper ──
        function getRowDevIds(row) {
            var ids = [];
            row.querySelectorAll('.dev-check:checked').forEach(function (cb) { ids.push(cb.value); });
            return ids;
        }

        function devsChanged(row) {
            var dd = row.querySelector('.dev-dropdown');
            if (!dd) return false;
            var orig = (dd.getAttribute('data-orig-devs') || '').split(',').filter(Boolean).sort().join(',');
            var now = getRowDevIds(row).slice().sort().join(',');
            return orig !== now;
        }

        function updateDevBadges(dd) {
            var badges = dd.querySelector('.dev-badges');
            if (!badges) return;
            var checked = dd.querySelectorAll('.dev-check:checked');
            var html = '';
            checked.forEach(function (cb, i) {
                if (i < 2) {
                    html += '<span class="shrink-0 whitespace-nowrap rounded-full bg-paper px-2 py-0.5 text-[9px] font-semibold text-ink/60">' + cb.getAttribute('data-dev-name') + '</span>';
                }
            });
            if (checked.length > 2) {
                html += '<span class="shrink-0 rounded-full bg-paper px-2 py-0.5 text-[9px] text-ink/40">+' + (checked.length - 2) + '</span>';
            }
            if (checked.length === 0) {
                html = '<span class="text-[10px] text-ink/30">Pilih&hellip;</span>';
            }
            badges.innerHTML = html;
        }

        // ── Dropdown z-index fix ──
        document.querySelectorAll('.dev-dropdown').forEach(function (dd) {
            dd.addEventListener('toggle', function () {
                var tr = dd.closest('[data-game-row]');
                if (!tr) return;
                if (dd.open) {
                    tr.style.position = 'relative';
                    tr.style.zIndex = '50';
                } else {
                    tr.style.zIndex = '';
                    tr.style.position = '';
                }
            });
            dd.addEventListener('change', function (e) {
                if (e.target.classList.contains('dev-check')) updateDevBadges(dd);
            });
        });

        // ── Close dropdown saat klik di luar ──
        document.addEventListener('click', function (e) {
            document.querySelectorAll('details.dev-dropdown[open]').forEach(function (dd) {
                if (!dd.contains(e.target)) dd.removeAttribute('open');
            });
        });

        // ── Instant upload ──
        document.querySelectorAll('.game-row-file-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var row = this.closest('[data-game-row]');
                if (!row) return;
                var preview = row.querySelector('.game-row-preview');
                var label = row.querySelector('.game-row-file-label');
                var checkbox = row.querySelector('.game-checkbox');
                var id = checkbox ? checkbox.dataset.id : null;
                var file = this.files[0];
                if (!file || !id) return;

                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover" alt="">';
                    preview.style.borderColor = '#43A047';
                    preview.style.boxShadow = '0 0 0 1px #43A047';
                };
                reader.readAsDataURL(file);
                if (label) label.textContent = file.name.substring(0, 8) + '..';

                var fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('_token', config.csrf);
                fd.append('name', row.querySelector('[data-field="name"]').value || 'temp');
                fd.append('default_store_id', row.querySelector('[data-field="default_store_id"]').value || '');
                fd.append('is_active', row.querySelector('[data-field="is_active"]').checked ? '1' : '0');
                fd.append('logo', file);
                getRowDevIds(row).forEach(function (did) { fd.append('developers[]', did); });

                fetch(config.baseUrl + '/' + id, {
                    method: 'POST', body: fd,
                    headers: { 'X-CSRF-TOKEN': config.csrf, 'Accept': 'application/json' }
                })
                .then(function (r) {
                    if (r.ok && label) label.textContent = '\u2713 Logo';
                    else if (label) label.textContent = '\u2717 Gagal';
                })
                .catch(function () {
                    if (label) label.textContent = '\u2717 Gagal';
                });
            });
        });

        // ═══ MODAL HELPER ═══
        var modalEl = document.getElementById('game-modal');
        var modalIcon = document.getElementById('game-modal-icon');
        var modalTitle = document.getElementById('game-modal-title');
        var modalText = document.getElementById('game-modal-text');
        var modalOk = document.getElementById('game-modal-ok');
        var modalCancel = document.getElementById('game-modal-cancel');

        function showGameModal(type, title, text, onOk, showCancel) {
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
                closeGameModal();
                if (onOk) onOk();
            };
            modalCancel.onclick = function () {
                closeGameModal();
            };

            modalEl.style.display = 'flex';
        }

        window.closeGameModal = function () {
            modalEl.style.display = 'none';
        };

        // ── Clear all selections ──
        var selectAll = document.getElementById('game-select-all');

        function clearSelections() {
            document.querySelectorAll('.game-checkbox').forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            var info = document.getElementById('game-selected-info');
            if (info) info.textContent = '0 terpilih';
        }

        // ── Counter ──
        var info = document.getElementById('game-selected-info');

        function updateCounter() {
            if (!info) return;
            info.textContent = document.querySelectorAll('.game-checkbox:checked').length + ' terpilih';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.game-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
                updateCounter();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('game-checkbox')) updateCounter();
        });

        // ── Bulk save ──
        var saveBtn = document.getElementById('game-bulk-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = document.querySelectorAll('[data-game-row]');
                var pending = [];

                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.type === 'file') { if (el.files && el.files.length) changed = true; return; }
                        var val = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                        if (val !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = val;
                    });
                    if (devsChanged(row)) changed = true;
                    if (changed) {
                        pending.push({ row: row, id: row.querySelector('.game-checkbox').getAttribute('data-id'), fields: fields });
                    }
                });

                if (!pending.length) {
                    showGameModal('info', 'Tidak ada perubahan', 'Semua data masih sama. Tidak ada yang perlu disimpan.');
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
                    form.append('default_store_id', item.fields.default_store_id);
                    form.append('is_active', item.fields.is_active);

                    var logoInput = item.row.querySelector('input[data-field="logo"]');
                    if (logoInput && logoInput.files && logoInput.files.length) form.append('logo', logoInput.files[0]);
                    getRowDevIds(item.row).forEach(function (did) { form.append('developers[]', did); });

                    fetch(config.baseUrl + '/' + item.id, {
                        method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': config.csrf }
                    })
                    .then(function (r) {
                        if (r.ok) {
                            success++;
                            item.row.querySelectorAll('[data-field]').forEach(function (el) {
                                if (el.type === 'file') { el.value = ''; return; }
                                el.setAttribute('data-orig', el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);
                            });
                            var dd = item.row.querySelector('.dev-dropdown');
                            if (dd) dd.setAttribute('data-orig-devs', getRowDevIds(item.row).join(','));
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
                                showGameModal('success', 'Berhasil disimpan', success + ' game berhasil diperbarui.');
                            } else if (success === 0) {
                                showGameModal('error', 'Gagal menyimpan', 'Semua ' + fail + ' game gagal disimpan. Coba lagi.');
                            } else {
                                showGameModal('info', 'Sebagian berhasil', success + ' berhasil, ' + fail + ' gagal disimpan.');
                            }
                        }
                    });
                });
            });
        }

        // ── Bulk delete ──
        var delBtn = document.getElementById('game-bulk-delete');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                var checked = document.querySelectorAll('.game-checkbox:checked');
                if (!checked.length) {
                    showGameModal('info', 'Belum ada yang dipilih', 'Centang game yang mau dihapus terlebih dahulu.');
                    return;
                }

                showGameModal('info', 'Konfirmasi hapus', 'Hapus ' + checked.length + ' game terpilih? Game yang masih punya produk akan ditolak.', function () {
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
                                var tr = cb.closest('[data-game-row]');
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
                                    showGameModal('success', 'Berhasil dihapus', deleted + ' game berhasil dihapus.');
                                } else if (deleted === 0) {
                                    showGameModal('error', 'Gagal dihapus', 'Game yang dipilih tidak bisa dihapus (masih punya produk).');
                                } else {
                                    showGameModal('info', 'Sebagian terhapus', deleted + ' terhapus, ' + rejected + ' ditolak.');
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