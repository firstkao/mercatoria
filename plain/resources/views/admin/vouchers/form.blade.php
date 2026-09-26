@php($isNew = ! $voucher->exists)
@extends('admin.layouts.app', ['title' => $isNew ? 'Tambah Voucher' : 'Edit Voucher', 'back' => route('admin.vouchers.index')])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.vouchers.store') : route('admin.vouchers.update', $voucher) }}" class="panel stack">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <div class="field-row">
        <label class="field">
            <span>Kode Voucher (Unik)</span>
            <input type="text" name="code" value="{{ old('code', $voucher->code) }}" required style="text-transform: uppercase;">
            @include('admin.partials.error', ['name' => 'code'])
        </label>
        <label class="field">
            <span>Nama Promosi</span>
            <input type="text" name="name" value="{{ old('name', $voucher->name) }}" required>
            @include('admin.partials.error', ['name' => 'name'])
        </label>
    </div>

    <div class="field-row">
        <label class="field">
            <span>Tipe Diskon</span>
            <select name="discount_type">
                <option value="nominal" @selected(old('discount_type', $voucher->discount_type) === 'nominal')>Potongan Nominal (Rp)</option>
                <option value="percent" @selected(old('discount_type', $voucher->discount_type) === 'percent')>Potongan Persen (%)</option>
            </select>
        </label>
        <label class="field">
            <span>Besaran Diskon (Rp / %)</span>
            <input type="number" name="value" value="{{ old('value', $voucher->value) }}" min="1" required>
            @include('admin.partials.error', ['name' => 'value'])
        </label>
    </div>

    <div class="field-row">
        <label class="field">
            <span>Minimal Belanja (Rp)</span>
            <input type="number" name="min_purchase_idr" value="{{ old('min_purchase_idr', $voucher->min_purchase_idr ?? 0) }}" min="0" required>
            @include('admin.partials.error', ['name' => 'min_purchase_idr'])
        </label>
        <label class="field">
            <span>Maksimal Diskon (Opsional untuk Persen)</span>
            <input type="number" name="max_discount_idr" value="{{ old('max_discount_idr', $voucher->max_discount_idr) }}">
        </label>
    </div>

    <div class="field-row">
        <label class="field">
            <span>Mulai Berlaku (Opsional)</span>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $voucher->starts_at?->format('Y-m-d\TH:i')) }}">
        </label>
        <label class="field">
            <span>Batas Kedaluwarsa (Opsional)</span>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $voucher->ends_at?->format('Y-m-d\TH:i')) }}">
        </label>
    </div>

    <div class="field-row">
        <label class="field">
            <span>Kuota Pemakaian Global (Opsional)</span>
            <input type="number" name="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}">
        </label>
        <label class="switch" style="margin-top: 25px;">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher->is_active ?? true))>
            <span>Voucher Aktif</span>
        </label>
    </div>

    <button type="submit" class="btn btn--primary" style="margin-top: 20px;">Simpan Voucher</button>
</form>
@endsection
