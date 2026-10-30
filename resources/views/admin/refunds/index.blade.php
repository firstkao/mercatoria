@extends('admin.layouts.admin')

@section('title', 'Refund')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Transaksi</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Refund</h1>

    <form method="GET" class="mt-4 flex items-end gap-2">
        <div>
            <label for="filter-status" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Status</label>
            <select name="status" id="filter-status" onchange="this.form.submit()"
                    class="mt-1 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
                <option value="">Semua</option>
                <option value="requested" @selected($status === 'requested')>Requested</option>
                <option value="completed" @selected($status === 'completed')>Completed</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
            </select>
        </div>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-rule text-left">
                <tr>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Order</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Amount</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Reason</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($refunds as $refund)
                    <tr class="border-b border-rule/50">
                        <td class="px-3 py-2 font-medium text-ink">{{ $refund->order->no_order ?? '—' }}</td>
                        <td class="px-3 py-2 text-ink/80">{{ format_rp($refund->amount) }}</td>
                        <td class="px-3 py-2">
                            <span class="rounded px-2 py-0.5 text-[10px] font-semibold {{ $refund->status === 'completed' ? 'bg-[#43A047] text-white' : ($refund->status === 'rejected' ? 'bg-[#E53935] text-white' : 'bg-amber-100 text-amber-800') }}">
                                {{ strtoupper($refund->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-ink/60">{{ \Illuminate\Support\Str::limit($refund->reason ?? '—', 50) }}</td>
                        <td class="px-3 py-2 text-right">
                            @if ($refund->status === 'requested')
                                <form method="POST" action="{{ route('office.refunds.complete', $refund) }}" class="inline">
                                    @csrf
                                    <button class="text-[#43A047] hover:underline">Proses</button>
                                </form>
                                <form method="POST" action="{{ route('office.refunds.reject', $refund) }}" class="ml-2 inline">
                                    @csrf
                                    <button class="text-danger hover:underline">Tolak</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-sm text-ink/45">Belum ada refund.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $refunds->links() }}</div>
@endsection
