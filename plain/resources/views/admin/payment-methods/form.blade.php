@php($isNew = ! $method->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Metode Pembayaran' : 'Edit: ' . $method->label,
    'back' => route('admin.payment-methods.index'),
])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.payment-methods.store') : route('admin.payment-methods.update', $method) }}" class="panel stack">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <label class="field">
        <span>Label</span>
        <input type="text" name="label" value="{{ old('label', $method->label) }}" maxlength="100" required placeholder="Contoh: BCA / QRIS / Mandiri">
        @include('admin.partials.error', ['name' => 'label'])
    </label>

    <div class="field-row">
        <label class="field">
            <span>Nomor Rekening (opsional)</span>
            <input type="text" name="account_number" value="{{ old('account_number', $method->account_number) }}" maxlength="100" placeholder="1234567890">
            @include('admin.partials.error', ['name' => 'account_number'])
        </label>
        <label class="field">
            <span>Atas Nama (opsional)</span>
            <input type="text" name="account_name" value="{{ old('account_name', $method->account_name) }}" maxlength="100">
            @include('admin.partials.error', ['name' => 'account_name'])
        </label>
    </div>

    <label class="field">
        <span>Instruksi (opsional)</span>
        <textarea name="instructions" rows="4" maxlength="2000" placeholder="Cara transfer, dsb.">{{ old('instructions', $method->instructions) }}</textarea>
        @include('admin.partials.error', ['name' => 'instructions'])
    </label>

    <div class="field-row">
        <label class="field field--short">
            <span>Urutan tampil</span>
            <input type="text"
                   name="sort_order"
                   value="{{ old('sort_order', $method->sort_order) }}"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   autocomplete="off"
                   placeholder="0">
        </label>
        <label class="switch" style="margin-top:25px;">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active))>
            <span>Aktif</span>
        </label>
    </div>

    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.payment-methods.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>

@unless($isNew)
    <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="danger-zone"
          onsubmit="return confirm('Hapus metode {{ $method->label }}?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus metode</button>
    </form>
@endunless
@endsection