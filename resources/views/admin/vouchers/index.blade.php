@extends('layouts.admin')

@section('title', 'Voucher')

@section('content')
<div class="mb-5 flex items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">Voucher</h1>
        <p class="text-sm text-text-muted">Kelola voucher diskon untuk campaign dan hadiah customer.</p>
    </div>
    <a href="{{ route('office.vouchers.create') }}" class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Buat Voucher</a>
</div>
@if ($errors->has('voucher'))
    <x-alert type="warning" class="mb-4">{{ $errors->first('voucher') }}</x-alert>
@endif
<div class="overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted">
            <tr><th class="px-4 py-3">Kode / Nama</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Nilai</th><th class="px-4 py-3">Berlaku</th><th class="px-4 py-3">Dibagikan</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($vouchers as $voucher)
                <tr>
                    <td class="px-4 py-3"><strong>{{ $voucher->code }}</strong><br>{{ $voucher->name }}</td>
                    <td class="px-4 py-3">{{ $voucher->type === 'percent' ? 'Persentase' : 'Nominal' }}</td>
                    <td class="px-4 py-3">{{ $voucher->type === 'percent' ? $voucher->value.'%' : 'Rp '.number_format((float) $voucher->value, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ $voucher->valid_from?->format('d-m-Y') ?: '—' }} – {{ $voucher->valid_until?->format('d-m-Y') ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $voucher->user_vouchers_count }}</td>
                    <td class="px-4 py-3">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="{{ route('office.vouchers.edit', $voucher) }}" class="text-navy-500 hover:underline">Edit</a>
                        @if ($voucher->user_vouchers_count === 0)
                            <form method="POST" action="{{ route('office.vouchers.destroy', $voucher) }}" class="inline" onsubmit="return confirm('Hapus voucher ini?')">
                                @csrf @method('DELETE')
                                <button class="ml-3 text-danger-500 hover:underline">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-text-muted">Belum ada voucher.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $vouchers->links() }}</div>
@endsection
