@extends('admin.layouts.admin')

@section('title', 'Rank Pelanggan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-lg font-semibold text-ink">Rank Pelanggan</h1>
        <a href="{{ route('office.customers.rank.export', request()->query()) }}"
           class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink hover:border-ink">Export CSV</a>
    </div>

    @if ($errors->any())
        <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="mt-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/email" class="rounded-lg border border-rule px-3 py-2 text-sm">
        <select name="state" class="rounded-lg border border-rule px-3 py-2 text-sm">
            <option value="">Semua state</option>
            @foreach (['MVP', 'Ex', 'ONS', 'Blocked', 'Grace'] as $state)
                <option value="{{ $state }}" @selected(request('state') === $state)>{{ $state }}</option>
            @endforeach
        </select>
        <select name="tier" class="rounded-lg border border-rule px-3 py-2 text-sm">
            <option value="">Semua tier</option>
            @foreach (['silver', 'gold', 'platinum'] as $tier)
                <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ ucfirst($tier) }}</option>
            @endforeach
        </select>
        <select name="sort" class="rounded-lg border border-rule px-3 py-2 text-sm">
            @foreach (['total_belanja' => 'Total Belanja', 'total_orders' => 'Total Order', 'aov' => 'AOV', 'name' => 'Nama'] as $key => $label)
                <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="dir" class="rounded-lg border border-rule px-3 py-2 text-sm">
            <option value="desc" @selected(request('dir') !== 'asc')>Turun</option>
            <option value="asc" @selected(request('dir') === 'asc')>Naik</option>
        </select>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#F8F9FA] font-mono text-[10px] uppercase tracking-[0.14em] text-ink/60">
                <tr>
                    <th class="px-3 py-2">#</th><th class="px-3 py-2">Pelanggan</th><th class="px-3 py-2">Badge</th>
                    <th class="px-3 py-2 text-right">Total Order</th><th class="px-3 py-2 text-right">Total Belanja</th>
                    <th class="px-3 py-2 text-right">AOV</th><th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-t border-rule">
                        <td class="px-3 py-2">{{ $rows->firstItem() + $loop->index }}</td>
                        <td class="px-3 py-2">{{ $row->name }}<br><span class="text-xs text-ink/50">{{ $row->email }}</span></td>
                        <td class="px-3 py-2 text-xs">{{ $row->current_state }} · {{ ucfirst($row->tier) }}</td>
                        <td class="px-3 py-2 text-right">{{ $row->total_orders }}</td>
                        <td class="px-3 py-2 text-right">{{ format_rp($row->total_belanja) }}</td>
                        <td class="px-3 py-2 text-right">{{ format_rp((float) $row->aov) }}</td>
                        <td class="px-3 py-2">@include('admin.customers._gift-modal', ['user' => $row, 'vouchers' => $vouchers])</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-ink/50">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $rows->links() }}</div>
@endsection
