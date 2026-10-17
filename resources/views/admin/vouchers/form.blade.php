@extends('layouts.admin')

@section('title', $voucher->exists ? 'Edit Voucher' : 'Buat Voucher')

@section('content')
<h1 class="mb-5 text-2xl font-bold">{{ $voucher->exists ? 'Edit Voucher' : 'Buat Voucher' }}</h1>
<form method="POST" action="{{ $voucher->exists ? route('office.vouchers.update', $voucher) : route('office.vouchers.store') }}" class="max-w-3xl space-y-4 rounded-xl border border-border bg-white p-6">
    @csrf
    @if ($voucher->exists) @method('PUT') @endif
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm">Kode
            <input name="code" value="{{ old('code', $voucher->code) }}" required maxlength="32" class="mt-1 w-full rounded-lg border border-border px-3 py-2 uppercase">
            @error('code')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Nama
            <input name="name" value="{{ old('name', $voucher->name) }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('name')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Jenis
            <select name="type" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                <option value="nominal" @selected(old('type', $voucher->type ?: 'nominal') === 'nominal')>Nominal rupiah</option>
                <option value="percent" @selected(old('type', $voucher->type) === 'percent')>Persentase</option>
            </select>
            @error('type')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Nilai
            <input name="value" type="number" step="0.01" min="0.01" value="{{ old('value', $voucher->value) }}" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('value')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Minimum belanja (Rp)
            <input name="min_purchase" type="number" step="0.01" min="0" value="{{ old('min_purchase', $voucher->min_purchase ?? 0) }}" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('min_purchase')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Maksimum diskon (opsional)
            <input name="max_discount" type="number" step="0.01" min="0.01" value="{{ old('max_discount', $voucher->max_discount) }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('max_discount')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Mulai berlaku
            <input name="valid_from" type="datetime-local" value="{{ old('valid_from', $voucher->valid_from?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('valid_from')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Akhir berlaku
            <input name="valid_until" type="datetime-local" value="{{ old('valid_until', $voucher->valid_until?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('valid_until')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="text-sm">Batas per pengguna
            <input name="per_user_limit" type="number" min="1" max="1000" value="{{ old('per_user_limit', $voucher->per_user_limit ?? 1) }}" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('per_user_limit')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
    </div>
    @if ($voucher->scope)
        <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Voucher ini memakai scope khusus yang belum didukung checkout. Scope tidak dapat diubah dari formulir ini.</p>
    @endif
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $voucher->is_active ?? true))> Aktif</label>
    <div class="flex gap-3">
        <button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Simpan</button>
        <a href="{{ route('office.vouchers.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm">Batal</a>
    </div>
</form>
@endsection
