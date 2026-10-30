@extends('admin.layouts.admin')

@section('title', 'Input Order Lama')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Input Order Lama (Inject)</h1>
    <p class="mt-1 text-sm text-ink/55">PRD 22.7 — harga snapshot bisa diedit manual, status bebas, tanpa bukti bayar.</p>

    <form method="POST" action="{{ route('office.orders.inject.store') }}" class="mt-4 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-ink" for="user-search">User <span class="text-danger">*</span></label>
                <div class="relative mt-1" id="user-combo">
                    <input type="text" id="user-search" placeholder="Ketik nama / email / HP untuk cari..." autocomplete="off"
                           class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <input type="hidden" name="user_id" id="user-id" required>
                    <div id="user-results" class="absolute left-0 top-full z-20 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-lg border border-rule bg-white shadow-sm"></div>
                    <p id="user-selected" class="mt-1 text-xs text-ink/45">Belum ada user terpilih.</p>
                </div>
                <a href="{{ route('office.users.create') }}" class="mt-1 inline-block text-xs text-link underline decoration-link/35 underline-offset-4">+ User Baru</a>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink">Status Tujuan <span class="text-danger">*</span></label>
                <select name="status" required class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink">Catatan Pembelian</label>
                <input type="text" name="customer_note" maxlength="500" class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            </div>

            <div>
                <label class="block text-sm font-medium text-ink">Tanggal Order</label>
                <input type="date" name="tanggal_order" max="{{ now()->format('Y-m-d') }}"
                       class="mt-1 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                <p class="mt-1 text-xs text-ink/40">Opsional. Masa lalu. Kosong = pakai waktu sekarang.</p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Item Pesanan</h2>
            <a href="{{ route('office.products.create') }}" class="text-xs text-link underline decoration-link/35 underline-offset-4">+ Produk Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                    <tr>
                        <th class="px-2 py-2" style="width:38%">Produk</th>
                        <th class="px-2 py-2" style="width:26%">Varian</th>
                        <th class="px-2 py-2" style="width:12%">Qty</th>
                        <th class="px-2 py-2" style="width:24%">Harga Snapshot (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < 3; $i++)
                        <tr class="border-b border-rule/50">
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-2">
                                    <img src="" alt="" data-thumb="product-{{ $i }}" class="h-10 w-10 shrink-0 rounded border border-rule object-cover" style="display:none">
                                    <select name="items[{{ $i }}][product_id]" data-select="product-{{ $i }}" class="w-full rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                                        <option value="">—</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-image="{{ $product->image_path ? \Illuminate\Support\Facades\Storage::url($product->image_path) : '' }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-2">
                                    <img src="" alt="" data-thumb="variant-{{ $i }}" class="h-10 w-10 shrink-0 rounded border border-rule object-cover" style="display:none">
                                    <select name="items[{{ $i }}][variant_id]" data-select="variant-{{ $i }}" class="w-full rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                                        <option value="">— tanpa varian —</option>
                                        @foreach ($products as $product)
                                            @if ($product->variants->isNotEmpty())
                                                <optgroup label="{{ $product->name }}">
                                                    @foreach ($product->variants as $variant)
                                                        <option value="{{ $variant->id }}" data-image="{{ $variant->image_path ? \Illuminate\Support\Facades\Storage::url($variant->image_path) : ($product->image_path ? \Illuminate\Support\Facades\Storage::url($product->image_path) : '') }}">{{ $variant->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[{{ $i }}][qty]" min="1" value="1"
                                       class="w-full rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" step="0.01" name="items[{{ $i }}][price]" min="0" placeholder="manual"
                                       class="w-full rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <p class="text-xs text-ink/40">Baris tanpa produk diabaikan. Harga yang diketik menjadi harga snapshot final (bisa diedit).</p>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
            SIMPAN ORDER LAMA
        </button>
    </form>

    @php
        $userList = [];
        foreach ($users as $u) {
            $userList[] = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'no_hp' => $u->no_hp];
        }
    @endphp
    <script>
        (function () {
            // Searchable user dropdown
            var users = {!! json_encode($userList) !!};
            var search = document.getElementById('user-search');
            var results = document.getElementById('user-results');
            var hidden = document.getElementById('user-id');
            var picked = document.getElementById('user-selected');

            search.addEventListener('input', function () {
                var q = this.value.trim().toLowerCase();
                if (q.length < 1) { results.classList.add('hidden'); return; }
                var matches = users.filter(function (u) {
                    return (u.name && u.name.toLowerCase().indexOf(q) !== -1)
                        || (u.email && u.email.toLowerCase().indexOf(q) !== -1)
                        || (u.no_hp && u.no_hp.indexOf(q) !== -1);
                }).slice(0, 20);
                var html = matches.map(function (u) {
                    var label = u.name + ' · ' + u.email + (u.no_hp ? ' · ' + u.no_hp : '');
                    return '<button type="button" class="block w-full px-3 py-2 text-left text-sm text-ink hover:bg-paper" data-id="' + u.id + '" data-label="' + label.replace(/"/g, '&quot;') + '">' + label + '</button>';
                }).join('');
                results.innerHTML = html || '<p class="px-3 py-2 text-sm text-ink/45">Tidak ditemukan.</p>';
                results.classList.remove('hidden');
            });

            results.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-id]');
                if (!btn) return;
                hidden.value = btn.getAttribute('data-id');
                search.value = btn.getAttribute('data-label');
                picked.textContent = 'Terpilih: ' + btn.getAttribute('data-label');
                picked.className = 'mt-1 text-xs text-[#43A047]';
                results.classList.add('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#user-combo')) results.classList.add('hidden');
            });

            // Thumbnail preview for product/variant selects
            document.querySelectorAll('select[data-select]').forEach(function (sel) {
                var key = sel.getAttribute('data-select');
                var img = document.querySelector('img[data-thumb="' + key + '"]');
                if (!img) return;
                function sync() {
                    var opt = sel.options[sel.selectedIndex];
                    var src = opt ? (opt.getAttribute('data-image') || '') : '';
                    if (src) { img.src = src; img.style.display = ''; }
                    else { img.removeAttribute('src'); img.style.display = 'none'; }
                }
                sel.addEventListener('change', sync);
                sync();
            });
        })();
    </script>
@endsection
