@extends('admin.layouts.admin')

@section('title', 'Developer')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Developer</h1>

    <form method="POST" action="{{ route('office.developers.store') }}" enctype="multipart/form-data" class="mt-4 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div class="flex flex-wrap items-end gap-2">
            <input type="text" name="name" placeholder="Nama developer" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <input type="text" name="slug" placeholder="slug (opsional)" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
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
            <input type="checkbox" id="dev-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="dev-bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <button type="button" id="dev-bulk-delete" class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">Hapus Terpilih</button>
        <span class="text-xs text-ink/45">Centang baris untuk bulk edit / hapus.</span>
    </div>

    <div class="mt-3 space-y-3" id="dev-list">
        @foreach ($developers as $developer)
            <div class="rounded-xl border border-rule bg-white p-4" data-dev-row>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" class="dev-checkbox rounded border-rule" data-id="{{ $developer->id }}">
                    @if ($developer->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($developer->logo_path) }}" alt="{{ $developer->name }}"
                             class="h-10 w-10 rounded border border-rule object-cover">
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded border border-rule bg-paper text-[10px] text-ink/30">N/A</div>
                    @endif
                    <input type="text" value="{{ $developer->name }}" data-field="name" data-orig="{{ $developer->name }}"
                           class="w-48 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <input type="text" value="{{ $developer->slug }}" data-field="slug" data-orig="{{ $developer->slug }}" placeholder="slug"
                           class="w-36 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <div>
                        <label class="block text-[9px] text-ink/40">Logo baru</label>
                        <input type="file" data-field="logo" accept=".jpg,.jpeg,.png,.webp" class="block w-32 rounded border border-rule px-1 py-0.5 text-[10px] text-ink">
                    </div>
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                        <input type="checkbox" data-field="is_active" data-orig="{{ $developer->is_active ? '1' : '0' }}" @checked($developer->is_active) class="rounded border-rule"> Aktif
                    </label>
                    <span class="ml-auto text-xs text-ink/45 dev-status" data-id="{{ $developer->id }}"></span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            var list = document.getElementById('dev-list');
            if (!list) return;
            var selectAll = document.getElementById('dev-select-all');
            selectAll.addEventListener('change', function () {
                list.querySelectorAll('.dev-checkbox').forEach(function (cb) { cb.checked = this.checked; }.bind(this));
            });

            document.getElementById('dev-bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-dev-row]');
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
                    if (changed) pending.push({ row: row, id: row.querySelector('.dev-checkbox').getAttribute('data-id'), fields: fields });
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
                    form.append('is_active', item.fields.is_active);
                    var logoInput = item.row.querySelector('input[data-field="logo"]');
                    if (logoInput && logoInput.files && logoInput.files.length) {
                        form.append('logo', logoInput.files[0]);
                    }
                    fetch('{{ route("office.developers.index") }}/' + item.id, { method: 'POST', body: form })
                        .then(function () {
                            item.row.querySelectorAll('[data-field]').forEach(function (el) {
                                if (el.type === 'file') { el.value = ''; return; }
                                el.setAttribute('data-orig', el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);
                            });
                            item.row.querySelector('.dev-status').textContent = '\u2713';
                            item.row.querySelector('.dev-status').className = 'ml-auto text-xs text-[#43A047] dev-status';
                        })
                        .catch(function () {
                            item.row.querySelector('.dev-status').textContent = '\u2717';
                            item.row.querySelector('.dev-status').className = 'ml-auto text-xs text-danger dev-status';
                        })
                        .finally(function () { done++; if (done === pending.length) { btn.disabled = false; btn.textContent = 'Simpan Semua'; } });
                });
            });

            document.getElementById('dev-bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.dev-checkbox:checked');
                if (!checked.length) { alert('Pilih developer yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' developer terpilih?')) return;
                var done = 0;
                checked.forEach(function (cb) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'DELETE');
                    fetch('{{ route("office.developers.index") }}/' + cb.getAttribute('data-id'), { method: 'POST', body: form })
                        .then(function () { cb.closest('[data-dev-row]').remove(); })
                        .finally(function () { done++; if (done === checked.length) alert('Selesai.'); });
                });
            });
        })();
    </script>
@endsection
