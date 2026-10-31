@extends('admin.layouts.admin')

@section('title', 'Semua Produk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Produk</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Semua Produk</h1>
        </div>
        <a href="{{ route('office.products.create') }}"
           class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
            + Tambah Produk
        </a>
    </div>

    {{-- Filter + Search --}}
    <form method="GET" class="mt-4 flex flex-wrap items-end gap-2">
        <div>
            <label for="filter-game" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Game</label>
            <select name="game_id" id="filter-game" onchange="this.form.submit()"
                    class="mt-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="">Semua Game</option>
                @foreach (($games ?? collect()) as $g)
                    <option value="{{ $g->id }}" @selected(request('game_id') == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- [NEW] Filter Developer --}}
        <div>
            <label for="filter-dev" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Developer</label>
            <select name="developer_id" id="filter-dev" onchange="this.form.submit()"
                    class="mt-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="">Semua Developer</option>
                @foreach (($developers ?? collect()) as $dev)
                    <option value="{{ $dev->id }}" @selected(request('developer_id') == $dev->id)>{{ $dev->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-q" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Cari</label>
            <input type="text" name="q" id="filter-q" value="{{ request('q') }}" placeholder="Nama / SKU..."
                   class="mt-1 w-56 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
        </div>

        <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">
            Terapkan
        </button>

        @if (request()->hasAny(['game_id', 'developer_id', 'q']))
            <a href="{{ route('office.products.index') }}" class="px-2 py-2 text-sm text-link underline decoration-link/35 underline-offset-4">Reset</a>
        @endif
    </form>

    <form method="POST" action="{{ route('office.products.bulk') }}" class="mt-4">
        @csrf
        <div class="overflow-x-auto rounded-xl border border-rule bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-rule text-left">
                    <tr>
                        <th class="w-10 px-3 py-2"></th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nama</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">SKU</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Game</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Developer</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Harga RMB</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Berat</th>
                        <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b border-rule/50 transition-colors hover:bg-paper/20">
                            <td class="px-3 py-2">
                                <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="rounded border-rule">
                            </td>
                            <td class="px-3 py-2 font-medium text-ink">{{ $product->name }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-ink/60">{{ $product->sku }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ $product->game->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ $product->developer->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ $product->price_rmb !== null ? '¥'.number_format((float) $product->price_rmb, 2) : '—' }}</td>
                            <td class="px-3 py-2 text-ink/80">{{ $product->weight_gram !== null ? number_format($product->weight_gram).' g' : '—' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold {{ $product->is_active ? 'bg-[#43A047] text-white' : 'bg-[#9E9E9E] text-white' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('office.products.edit', $product) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-8 text-center text-sm text-ink/45">Tidak ada produk ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <label class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Aksi massal:</label>
            <select name="action" class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="aktifkan">Aktifkan</option>
                <option value="nonaktifkan">Nonaktifkan</option>
                <option value="hapus">Hapus Permanen</option>
            </select>
            <button type="submit" class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">
                Terapkan
            </button>
        </div>
    </form>

    <div class="mt-4">{{ $products->links() }}</div>
@endsection