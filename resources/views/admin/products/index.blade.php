@extends('admin.layouts.admin')

@section('title', 'Semua Produk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Produk</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Semua Produk</h1>
        </div>
        <a href="{{ route('office.products.create') }}"
           class="rounded-lg bg-black px-5 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-all hover:bg-ink hover:shadow-md">
            + Tambah Produk
        </a>
    </div>

    {{-- Filter + Search --}}
    <form method="GET" class="mt-5 flex flex-wrap items-end gap-3 rounded-xl border border-rule bg-white p-4 shadow-sm">
        <div>
            <label for="filter-game" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/50">Game</label>
            <select name="game_id" id="filter-game" onchange="this.form.submit()"
                    class="mt-1.5 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                <option value="">Semua Game</option>
                @foreach (($games ?? collect()) as $g)
                    <option value="{{ $g->id }}" @selected(request('game_id') == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-dev" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/50">Developer</label>
            <select name="developer_id" id="filter-dev" onchange="this.form.submit()"
                    class="mt-1.5 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                <option value="">Semua Developer</option>
                @foreach (($developers ?? collect()) as $dev)
                    <option value="{{ $dev->id }}" @selected(request('developer_id') == $dev->id)>{{ $dev->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-q" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/50">Cari</label>
            <input type="text" name="q" id="filter-q" value="{{ request('q') }}" placeholder="Nama / SKU..."
                   class="mt-1.5 w-56 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
        </div>

        <button type="submit"
                class="rounded-lg border border-rule px-5 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink hover:bg-paper">
            Terapkan
        </button>

        @if (request()->hasAny(['game_id', 'developer_id', 'q']))
            <a href="{{ route('office.products.index') }}"
               class="px-2 py-2 text-sm text-link underline decoration-link/35 underline-offset-4 hover:decoration-link">
                Reset
            </a>
        @endif
    </form>

    {{-- Result count --}}
    <p class="mt-3 text-xs text-ink/50">
        Menampilkan <strong class="text-ink">{{ $products->count() }}</strong> dari
        <strong class="text-ink">{{ $products->total() }}</strong> produk
    </p>

    {{-- Bulk form --}}
    <form method="POST" action="{{ route('office.products.bulk') }}" class="mt-3"
          onsubmit="return confirmBulkAction(this)">
        @csrf

        <div class="overflow-hidden rounded-xl border border-rule bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-rule bg-[#F8F9FA]">
                    <tr>
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" id="check-all-products" class="h-4 w-4 rounded border-rule">
                        </th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Nama</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">SKU</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Game</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Developer</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Harga RMB</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Berat</th>
                        <th class="px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b border-rule/40 transition-colors last:border-b-0 hover:bg-paper/20">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="h-4 w-4 rounded border-rule">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt=""
                                             class="h-9 w-9 rounded-lg border border-rule object-cover">
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-rule bg-paper">
                                            <i class="bi bi-image text-ink/20" style="font-size:0.9rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-ink">{{ $product->name }}</p>
                                        @if ($product->is_split_box)
                                            <span class="mt-0.5 inline-flex items-center gap-1 rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-semibold text-blue-600">
                                                <i class="bi bi-grid-3x3-gap" style="font-size:0.6rem;"></i> Split Box
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-ink/50">{{ $product->sku }}</td>
                            <td class="px-4 py-3 text-ink/70">{{ $product->game->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink/70">{{ $product->developer->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-ink/80">
                                {{ $product->price_rmb !== null ? '¥'.number_format((float) $product->price_rmb, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-ink/80">
                                {{ $product->weight_gram !== null ? number_format($product->weight_gram).' g' : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('office.products.edit', $product) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-3 py-1.5 text-xs font-medium text-ink/70 transition-colors hover:border-ink hover:bg-paper hover:text-ink">
                                    <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-paper">
                                        <i class="bi bi-box-seam text-ink/20" style="font-size:1.8rem;"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-ink/50">Tidak ada produk ditemukan</p>
                                        <p class="mt-1 text-xs text-ink/35">Coba ubah filter atau kata kunci pencarian.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk action bar --}}
        <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-rule bg-white p-4 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="bi bi-lightning text-ink/35" style="font-size:0.85rem;"></i>
                <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/50">Aksi Massal</span>
            </div>
            <div class="h-5 w-px bg-rule"></div>
            <select name="action"
                    class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink transition-colors focus:border-ink focus:ring-1 focus:ring-ink/10">
                <option value="aktifkan">Aktifkan</option>
                <option value="nonaktifkan">Nonaktifkan</option>
                <option value="hapus">Hapus Permanen</option>
            </select>
            <button type="submit"
                    class="rounded-lg border border-rule px-5 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink hover:bg-paper">
                Terapkan
            </button>
            <p class="ml-auto text-xs text-ink/40">
                <i class="bi bi-info-circle me-1" style="font-size:0.75rem;"></i>
                Centang produk di tabel untuk aksi massal
            </p>
        </div>
    </form>

    <div class="mt-5">{{ $products->withQueryString()->links() }}</div>

    <script>
        document.getElementById('check-all-products')?.addEventListener('change', function () {
            document.querySelectorAll('input[name="ids[]"]').forEach(function (cb) {
                cb.checked = this.checked;
            }.bind(this));
        });

        function confirmBulkAction(form) {
            var action = form.querySelector('select[name="action"]').value;
            var count = form.querySelectorAll('input[name="ids[]"]:checked').length;

            if (count === 0) {
                alert('Pilih minimal 1 produk.');
                return false;
            }

            if (action === 'hapus') {
                return confirm('Hapus PERMANEN ' + count + ' produk? Tidak bisa dibatalkan!');
            }

            return confirm('Terapkan "' + action + '" ke ' + count + ' produk?');
        }
    </script>
@endsection
