@extends('admin.layouts.admin')

@section('title', 'Kategori Merch')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Produk</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Kategori Merch (Auto-Group)</h1>

    <form method="POST" action="{{ route('office.merch-categories.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="text" name="name" placeholder="Nama kategori" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="number" name="priority" placeholder="Prioritas" required class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="keywords" placeholder="keyword1, keyword2" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <label class="flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" id="mc-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="mc-bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <button type="button" id="mc-bulk-delete" class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">Hapus Terpilih</button>
        <span class="text-xs text-ink/45">Centang baris untuk bulk edit / hapus.</span>
    </div>

    <div class="mt-3 space-y-3" id="mc-list">
        @foreach ($categories as $category)
            <div class="rounded-xl border border-rule bg-white p-4" data-mc-row>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" class="mc-checkbox rounded border-rule" data-id="{{ $category->id }}">
                    <input type="text" value="{{ $category->name }}" data-field="name" data-orig="{{ $category->name }}"
                           class="w-48 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <input type="number" value="{{ $category->priority }}" data-field="priority" data-orig="{{ $category->priority }}"
                           class="w-24 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <input type="text" value="{{ implode(', ', $category->keywords ?? []) }}" data-field="keywords" data-orig="{{ implode(', ', $category->keywords ?? []) }}"
                           class="flex-1 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                    <span class="text-xs text-ink/45 mc-status" data-id="{{ $category->id }}"></span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            var list = document.getElementById('mc-list');
            if (!list) return;
            var selectAll = document.getElementById('mc-select-all');
            selectAll.addEventListener('change', function () {
                list.querySelectorAll('.mc-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
            });

            document.getElementById('mc-bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-mc-row]');
                var pending = [];
                rows.forEach(function (row) {
                    var fields = {}; var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        if (el.value !== el.getAttribute('data-orig')) changed = true;
                        fields[el.getAttribute('data-field')] = el.value;
                    });
                    if (changed) pending.push({ row: row, id: row.querySelector('.mc-checkbox').getAttribute('data-id'), fields: fields });
                });
                if (!pending.length) { alert('Tidak ada perubahan.'); return; }
                var btn = this; btn.disabled = true; btn.textContent = 'Menyimpan...';
                var done = 0;
                pending.forEach(function (item) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'PUT');
                    form.append('name', item.fields.name);
                    form.append('priority', item.fields.priority);
                    form.append('keywords', item.fields.keywords);
                    fetch('{{ route("office.merch-categories.index") }}/' + item.id, { method: 'POST', body: form })
                        .then(function () {
                            item.row.querySelectorAll('[data-field]').forEach(function (el) { el.setAttribute('data-orig', el.value); });
                            item.row.querySelector('.mc-status').textContent = '\u2713';
                            item.row.querySelector('.mc-status').className = 'text-xs text-[#43A047] mc-status';
                        })
                        .catch(function () {
                            item.row.querySelector('.mc-status').textContent = '\u2717';
                            item.row.querySelector('.mc-status').className = 'text-xs text-danger mc-status';
                        })
                        .finally(function () { done++; if (done === pending.length) { btn.disabled = false; btn.textContent = 'Simpan Semua'; } });
                });
            });

            document.getElementById('mc-bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.mc-checkbox:checked');
                if (!checked.length) { alert('Pilih kategori yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' kategori terpilih?')) return;
                var done = 0;
                checked.forEach(function (cb) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'DELETE');
                    fetch('{{ route("office.merch-categories.index") }}/' + cb.getAttribute('data-id'), { method: 'POST', body: form })
                        .then(function () { cb.closest('[data-mc-row]').remove(); })
                        .finally(function () { done++; if (done === checked.length) alert('Selesai.'); });
                });
            });
        })();
    </script>
@endsection
