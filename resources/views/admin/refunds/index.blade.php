@extends('layouts.admin')

@section('title', 'Refund')

@section('content')
<h1 class="text-2xl font-bold text-navy-500">Permintaan Refund</h1>
<div class="mt-5 space-y-4">
    @forelse ($refunds as $refund)
        <article class="rounded-xl border border-border bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ route('office.orders.show', $refund->order) }}" class="font-semibold text-navy-500 hover:underline">{{ $refund->order->order_no }}</a>
                    <p class="mt-1 text-sm">{{ $refund->order->customer_name }} · {{ ucfirst($refund->type) }} · {{ format_rupiah($refund->amount) }}</p>
                    <p class="mt-2 text-sm text-text-muted">{{ $refund->reason }}</p>
                    @if ($refund->admin_note)<p class="mt-2 text-sm">Catatan admin: {{ $refund->admin_note }}</p>@endif
                </div>
                <span class="rounded-full bg-surface-2 px-3 py-1 text-xs">{{ $refund->status_enum->label() }}</span>
            </div>
            @if (in_array($refund->status, ['requested', 'processing'], true))
                <form method="POST" action="{{ route('office.refunds.process', $refund) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="refund-note-{{ $refund->id }}">Catatan admin</label>
                    <input id="refund-note-{{ $refund->id }}" name="admin_note" maxlength="1000" placeholder="Catatan / alasan penolakan" class="rounded-lg border-border text-sm">
                    <button name="decision" value="approve" class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Tandai selesai</button>
                    <button name="decision" value="reject" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Tolak</button>
                </form>
            @endif
        </article>
    @empty
        <div class="rounded-xl border border-dashed border-border bg-white p-8 text-center text-text-muted">Belum ada permintaan refund.</div>
    @endforelse
</div>
<div class="mt-5">{{ $refunds->links() }}</div>
@endsection
