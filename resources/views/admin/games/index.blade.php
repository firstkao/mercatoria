@extends('admin.layouts.admin')

@section('title', 'Game')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Game</h1>

    <form method="POST" action="{{ route('office.games.store') }}" enctype="multipart/form-data" class="mt-4 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div class="flex flex-wrap items-end gap-2">
            <input type="text" name="name" placeholder="Nama game" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <input type="text" name="slug" placeholder="slug (opsional)" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <select name="default_store_id" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                <option value="">Default store</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </select>
            <div>
                <label class="block text-xs text-ink/55">Logo</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" class="block rounded-lg border border-rule px-2 py-1 text-xs text-ink">
            </div>
            <label class="flex items-center gap-1 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
            <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
        </div>
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <label class="flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" id="game-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="game-bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <button type="button" id="game-bulk-delete" class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">Hapus Terpilih</button>
        <span class="text-xs text-ink/45">Centang baris untuk bulk edit / hapus.</span>
    </div>

    <div class="mt-3 space-y-3" id="game-list">
        @foreach ($games as $game)
            <div class="rounded-xl border border-rule bg-white p-4" data-game-row>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" class="game-checkbox rounded border-rule" data-id="{{ $game->id }}">
                    @if ($game->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}"
                             class="h-10 w-10 rounded border border-rule object-cover">
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded border border-rule bg-paper text-[10px] text-ink/30">N/A</div>
                    @endif
                    <input type="text" value="{{ $game->name }}" data-field="name" data-orig="{{ $game->name }}"
                           class="w-40 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <input type="text" value="{{ $game->slug }}" data-field="slug" data-orig="{{ $game->slug }}" placeholder="slug"
                           class="w-36 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <select data-field="default_store_id" data-orig="{{ $game->default_store_id }}" class="rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                        <option value="">Default store</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" @selected($game->default_store_id == $store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <div>
                        <label class="block text-[9px] text-ink/40">Logo baru</label>
                        <input type="file" data-field="logo" accept=".jpg,.jpeg,.png,.webp" class="block w-32 rounded border border-rule px-1 py-0.5 text-[10px] text-ink">
                    </div>
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                        <input type="checkbox" data-field="is_active" data-orig="{{ $game->is_active ? '1' : '0' }}" @checked($game->is_active) class="rounded border-rule"> Aktif
                    </label>
                    <span class="ml-auto text-xs text-ink/45 game-status" data-id="{{ $game->id }}"></span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            var list = document.getElementById('game-list');
            if (!list) return;
            var selectAll = document.getElementById('game-select-all');
            selectAll.addEventListener('change', function () {
                list.querySelectorAll('.game-checkbox').forEach(function (cb) { cb.checked = this.checked; }.bind(this));
            });

            document.getElementById('game-bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-game-row]');
                var pending = [];
                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.type === 'file') {
                            if (el.files && el.files.length) changed = true;
                            return;
                        }
                        var val = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
                        if (val !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = val;
                    });
                    if (changed) pending.push({ row: row, id: row.querySelector('.game-checkbox').getAttribute('data-id'), fields: fields });
                });
                if (!pending.length) { alert('Tidak ada perubahan.'); return; }
                var btn = this; btn.disabled = true; btn.textContent = 'Menyimpan...';
                var done = 0;
                pending.forEach(function (item) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'PUT');
                    form.append('name', item.fields.name);
                    form.append('slug', item.fields.slug || '');
                    form.append('default_store_id', item.fields.default_store_id);
                    form.append('is_active', item.fields.is_active);
                    var logoInput = item.row.querySelector('input[data-field="logo"]');
                    if (logoInput && logoInput.files && logoInput.files.length) {
                        form.append('logo', logoInput.files[0]);
                    }
                    fetch('{{ route("office.games.index") }}/' + item.id, { method: 'POST', body: form })
                        .then(function () {
                            item.row.querySelectorAll('[data-field]').forEach(function (el) {
                                if (el.type === 'file') { el.value = ''; return; }
                                el.setAttribute('data-orig', el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);
                            });
                            item.row.querySelector('.game-status').textContent = '\u2713';
                            item.row.querySelector('.game-status').className = 'ml-auto text-xs text-[#43A047] game-status';
                        })
                        .catch(function () {
                            item.row.querySelector('.game-status').textContent = '\u2717';
                            item.row.querySelector('.game-status').className = 'ml-auto text-xs text-danger game-status';
                        })
                        .finally(function () { done++; if (done === pending.length) { btn.disabled = false; btn.textContent = 'Simpan Semua'; } });
                });
            });

            document.getElementById('game-bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.game-checkbox:checked');
                if (!checked.length) { alert('Pilih game yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' game terpilih beserta katalognya?')) return;
                var done = 0;
                checked.forEach(function (cb) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'DELETE');
                    fetch('{{ route("office.games.index") }}/' + cb.getAttribute('data-id'), { method: 'POST', body: form })
                        .then(function () { cb.closest('[data-game-row]').remove(); })
                        .finally(function () { done++; if (done === checked.length) alert('Selesai.'); });
                });
            });
        })();
    </script>
@endsection
