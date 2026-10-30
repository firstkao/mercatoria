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
        <input type="text" name="ongkir_rmb" placeholder="Ongkir (RMB)" required inputmode="numeric" pattern="[0-9]*" class="w-36 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    {{-- Bulk Actions --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
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
                    <input type="text" name="name" value="{{ $store->name }}" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="name">
                    <input type="text" name="min_belanja_rmb" value="{{ $store->min_belanja_rmb }}" inputmode="numeric" pattern="[0-9]*" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="min_belanja_rmb">
                    <input type="text" name="ongkir_rmb" value="{{ $store->ongkir_rmb }}" inputmode="numeric" pattern="[0-9]*" class="w-36 rounded-lg border border-rule px-3 py-2 text-sm text-ink" data-field="ongkir_rmb">
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

            document.getElementById('bulk-save').addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-store-row]');
                var saveBtn = this;
                saveBtn.disabled = true;
                saveBtn.textContent = 'Menyimpan...';
                var done = 0;
                var total = rows.length;

                rows.forEach(function (row) {
                    var id = row.querySelector('[data-id]').getAttribute('data-id');
                    var name = row.querySelector('[data-field="name"]').value;
                    var min = row.querySelector('[data-field="min_belanja_rmb"]').value;
                    var ongkir = row.querySelector('[data-field="ongkir_rmb"]').value;
                    var status = row.querySelector('.store-status');

                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'PUT');
                    form.append('name', name);
                    form.append('min_belanja_rmb', min);
                    form.append('ongkir_rmb', ongkir);

                    fetch('{{ route("office.stores.index") }}/' + id, { method: 'POST', body: form })
                        .then(function () {
                            status.textContent = '✓';
                            status.className = 'pb-2 text-xs text-[#43A047] store-status';
                        })
                        .catch(function () {
                            status.textContent = '✗';
                            status.className = 'pb-2 text-xs text-danger store-status';
                        })
                        .finally(function () {
                            done++;
                            if (done === total) {
                                saveBtn.disabled = false;
                                saveBtn.textContent = 'Simpan Semua';
                            }
                        });
                });
            });

            document.getElementById('bulk-delete').addEventListener('click', function () {
                var checked = list.querySelectorAll('.store-checkbox:checked');
                if (!checked.length) { alert('Pilih store yang mau dihapus.'); return; }
                if (!confirm('Hapus ' + checked.length + ' store terpilih? Referensi produk/game jadi NULL.')) return;

                var done = 0;
                var total = checked.length;
                checked.forEach(function (cb) {
                    var id = cb.getAttribute('data-id');
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'DELETE');

                    fetch('{{ route("office.stores.index") }}/' + id, { method: 'POST', body: form })
                        .then(function () {
                            cb.closest('[data-store-row]').remove();
                        })
                        .finally(function () {
                            done++;
                            if (done === total) {
                                alert('Selesai menghapus ' + total + ' store.');
                            }
                        });
                });
            });
        })();
    </script>
@endsection
