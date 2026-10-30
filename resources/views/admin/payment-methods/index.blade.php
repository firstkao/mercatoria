@extends('admin.layouts.admin')

@section('title', 'Metode Bayar')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Metode Bayar</h1>

    {{-- Tambah --}}
    <form method="POST" action="{{ route('office.payment-methods.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="text" name="code" placeholder="Code (bca)" required class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="name" placeholder="Nama" required class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <select name="type" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <option value="bank_transfer">Bank Transfer</option>
            <option value="qris">QR Code</option>
        </select>
        <input type="text" name="fee" placeholder="Fee (Rp)" inputmode="numeric" class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="instructions" placeholder="Instruksi" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    {{-- Bulk Actions --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="button" id="pm-bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <button type="button" id="pm-bulk-delete" class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">Hapus Terpilih</button>
        <span class="text-xs text-ink/45">Centang untuk hapus</span>
    </div>

    {{-- Daftar --}}
    <div class="mt-3 space-y-3" id="pm-list">
        @forelse ($methods as $method)
            <div class="rounded-xl border border-rule bg-white p-4" data-pm-row>
                <div class="flex flex-wrap items-end gap-2">
                    <label class="flex items-center pb-2">
                        <input type="checkbox" class="pm-checkbox rounded border-rule" data-id="{{ $method->id }}">
                    </label>
                    <input type="text" name="code" value="{{ $method->code }}" required class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="code">
                    <input type="text" name="name" value="{{ $method->name }}" required class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="name">
                    <select name="type" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="type">
                        <option value="bank_transfer" @selected($method->type === 'bank_transfer')>Bank Transfer</option>
                        <option value="qris" @selected($method->type === 'qris')>QR Code</option>
                    </select>
                    <input type="text" name="fee" value="{{ $method->fee }}" inputmode="numeric" class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="fee">
                    <input type="text" name="instructions" value="{{ $method->instructions }}" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="instructions">
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                        <input type="checkbox" class="rounded border-rule" data-field="is_active" @checked($method->is_active)> Aktif
                    </label>
                    <span class="pb-2 text-xs text-ink/45 pm-status" data-id="{{ $method->id }}"></span>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">Belum ada metode bayar.</div>
        @endforelse
    </div>

    <script>
        (function () {
            var list = document.getElementById('pm-list');
            if (!list) return;

            document.getElementById('pm-bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-pm-row]');
                var btn = this;
                btn.disabled = true; btn.textContent = 'Menyimpan...';
                var done = 0;
                rows.forEach(function (row) {
                    var id = row.querySelector('[data-id]').getAttribute('data-id');
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'PUT');
                    form.append('code', row.querySelector('[data-field="code"]').value);
                    form.append('name', row.querySelector('[data-field="name"]').value);
                    form.append('type', row.querySelector('[data-field="type"]').value);
                    form.append('fee', row.querySelector('[data-field="fee"]').value);
                    form.append('instructions', row.querySelector('[data-field="instructions"]').value);
                    form.append('is_active', row.querySelector('[data-field="is_active"]').checked ? '1' : '0');
                    fetch('{{ route("office.payment-methods.index") }}/' + id, { method: 'POST', body: form })
                        .then(function () {
                            row.querySelector('.pm-status').textContent = '✓';
                            row.querySelector('.pm-status').className = 'pb-2 text-xs text-[#43A047] pm-status';
                        })
                        .catch(function () {
                            row.querySelector('.pm-status').textContent = '✗';
                            row.querySelector('.pm-status').className = 'pb-2 text-xs text-danger pm-status';
                        })
                        .finally(function () {
                            done++;
                            if (done === rows.length) { btn.disabled = false; btn.textContent = 'Simpan Semua'; }
                        });
                });
            });

            document.getElementById('pm-bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.pm-checkbox:checked');
                if (!checked.length) { alert('Pilih metode yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' metode terpilih?')) return;
                var done = 0;
                checked.forEach(function (cb) {
                    var id = cb.getAttribute('data-id');
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'DELETE');
                    fetch('{{ route("office.payment-methods.index") }}/' + id, { method: 'POST', body: form })
                        .then(function () { cb.closest('[data-pm-row]').remove(); })
                        .finally(function () {
                            done++;
                            if (done === checked.length) alert('Selesai.');
                        });
                });
            });
        })();
    </script>
@endsection
