@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-navy-500">Verifikasi Pembayaran</h1>
    <div class="flex gap-2">
        @foreach (['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $status => $label)
            <a href="{{ route('office.payments.index', ['status' => $status]) }}"
               @class(['rounded-lg px-3 py-2 text-sm', 'bg-primary-300 text-white' => $filters['status'] === $status, 'border border-border bg-white' => $filters['status'] !== $status])>{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="mt-4 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted">
            <tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Metode</th><th class="px-4 py-3">Nominal</th><th class="px-4 py-3">Diunggah</th><th class="px-4 py-3 text-right">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($payments as $payment)
                <tr class="hover:bg-[#F5F7FA]">
                    <td class="px-4 py-3 font-semibold">{{ $payment->order?->order_no ?? '—' }}</td>
                    <td class="px-4 py-3 uppercase">{{ $payment->type }}</td>
                    <td class="px-4 py-3">{{ $payment->method }}</td>
                    <td class="px-4 py-3">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $payment->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($payment->order)
                            <a href="{{ route('office.orders.show', $payment->order) }}" class="font-semibold text-navy-500 hover:underline">{{ $payment->status === 'pending' ? 'Verifikasi' : 'Lihat order' }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Tidak ada pembayaran pada status ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
