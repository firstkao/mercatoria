@extends('admin.layouts.admin')

@section('title', 'Stores')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Stores (Tier Ongkir)</h1>

    {{-- Tambah Store --}}
    <form method="POST" action="{{ route('office.stores.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="text" name="name" placeholder="Nama store" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="min_belanja_rmb" placeholder="Min belanja (RMB)" required inputmode="numeric" pattern="[0-9]*" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        {{-- [FIX #1] Rename field — sesuai migration & controller --}}
        <input type="text" name="ongkir_lokal_cn_percent" placeholder="Ongkir lokal (%)" required inputmode="numeric" pattern="[0-9]*" class="w-36 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    {{-- Bulk Actions --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <label class="flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" id="store-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <button type="button" id="bulk-delete" class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">Hapus Terpilih</button>
        <span class="text-xs text-ink/45">Centang store untuk hapus</span>
    </div>

    {{-- Daftar Store --}}
    <div class="mt-3 space-y-3" id="stores-list">
        @foreach ($stores as $store)
            <div class="rounded-xl border border-rule bg-white p-4" data-store-row>
                <div class="flex flex-wrap items-end gap-2">
                    <label class="flex items-center pb-2">
                        <input type="checkbox" class="store-checkbox rounded border-rule" data-id="{{ $store->id }}">
                    </label>
                    <input type="text" value="{{ $store->name }}" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="name">
                    <input type="text" value="{{ $store->min_belanja_rmb }}" inputmode="numeric" pattern="[0-9]*" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="min_belanja_rmb">
                    {{-- [FIX #1] Rename field --}}
                    <input type="text" value="{{ $store->ongkir_lokal_cn_percent }}" inputmode="numeric" pattern="[0-9]*" class="w-36 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="ongkir_lokal_cn_percent">
                    <span class="pb-2 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">{{ $store->products_count }} produk · {{ $store->games_count }} game</span>
                    <span class="pb-2 text-xs text-ink/45 store-status" data-id="{{ $store->id }}"></span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            var list = document.getElementById('stores-list');
            if (!list) return;

            // ── Select All ──
            document.getElementById('store-select-all').addEventListener('change', function () {
                list.querySelectorAll('.store-checkbox').forEach(function (cb) { cb.checked = this.checked; }.bind(this));
            });

            // ── [FIX #2] Bulk Save — pakai endpoint bulk() dari controller ──
            document.getElementById('bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-store-row]');
                var saveBtn = this;
                saveBtn.disabled = true;
                saveBtn.textContent = 'Menyimpan...';

                var stores = [];
                rows.forEach(function (row) {
                    var id = row.querySelector('[data-id]').getAttribute('data-id');
                    stores.push({
                        id: id,
                        name: row.querySelector('[data-field="name"]').value,
                        min_belanja_rmb: row.querySelector('[data-field="min_belanja_rmb"]').value,
                        ongkir_lokal_cn_percent: row.querySelector('[data-field="ongkir_lokal_cn_percent"]').value,
                    });
                });

                var form = new FormData();
                form.append('_token', '{{ csrf_token() }}');
                stores.forEach(function (s, i) {
                    form.append('stores[' + i + '][id]', s.id);
                    form.append('stores[' + i + '][name]', s.name);
                    form.append('stores[' + i + '][min_belanja_rmb]', s.min_belanja_rmb);
                    form.append('stores[' + i + '][ongkir_lokal_cn_percent]', s.ongkir_lokal_cn_percent);
                });

                fetch('{{ route("office.stores.bulk") }}', {
                    method: 'POST',
                    body: form,
                    headers: { 'Accept': 'application/json' },
                })
                .then(function (r) {
                    if (r.ok) {
                        list.querySelectorAll('.store-status').forEach(function (s) {
                            s.textContent = '✓';
                            s.className = 'pb-2 text-xs text-[#43A047] store-status';
                        });
                    } else {
                        return r.json().then(function (d) {
                            var msg = d.message || 'Validasi gagal.';
                            if (d.errors) {
                                msg = Object.values(d.errors).flat().join(' ');
                            }
                            alert('Gagal: ' + msg);
                        });
                    }
                })
                .catch(function () {
                    alert('Terjadi kesalahan jaringan.');
                })
                .finally(function () {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Simpan Semua';
                });
            });

            // ── [FIX #2] Bulk Delete — pakai bulkDelete() yang punya safety check ──
            document.getElementById('bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.store-checkbox:checked');
                if (!checked.length) { alert('Pilih store yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' store terpilih? Store yang masih punya produk/game akan ditolak.')) return;

                var form = new FormData();
                form.append('_token', '{{ csrf_token() }}');
                checked.forEach(function (cb, i) {
                    form.append('ids[]', cb.getAttribute('data-id'));
                });

                fetch('{{ route("office.stores.bulk-delete") }}', {
                    method: 'POST',
                    body: form,
                    headers: { 'Accept': 'application/json' },
                })
                .then(function (r) {
                    return r.json().then(function (d) { return { ok: r.ok, data: d }; });
                })
                .then(function (res) {
                    if (res.ok) {
                        checked.forEach(function (cb) {
                            var row = cb.closest('[data-store-row]');
                            if (row) row.remove();
                        });
                        alert(res.data.message || checked.length + ' store dihapus.');
                    } else {
                        // Safety check menolak — store masih punya produk/game
                        var msg = res.data.message || 'Gagal menghapus.';
                        if (res.data.errors) {
                            msg = Object.values(res.data.errors).flat().join(' ');
                        }
                        alert('Ditolak: ' + msg);
                    }
                })
                .catch(function () {
                    alert('Terjadi kesalahan jaringan.');
                });
            });

        })();
    </script>
@endsection