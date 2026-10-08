@php($isNew = ! $method->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Metode Pembayaran' : 'Edit: ' . $method->label,
    'back' => route('admin.payment-methods.index'),
])

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.payment-methods.store') : route('admin.payment-methods.update', $method) }}" class="panel stack" enctype="multipart/form-data">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <label class="field">
        <span>Jenis Pembayaran</span>
        <select name="type" id="pm-type" required>
            @foreach($types as $value => $labelOption)
                <option value="{{ $value }}" @selected(old('type', $method->type ?? 'bank') === $value)>{{ $labelOption }}</option>
            @endforeach
        </select>
        @include('admin.partials.error', ['name' => 'type'])
    </label>

    <label class="field">
        <span>Label</span>
        <input type="text" name="label" value="{{ old('label', $method->label) }}" maxlength="100" required placeholder="Contoh: BCA / QRIS / Shopee Pay">
        @include('admin.partials.error', ['name' => 'label'])
    </label>

    <!-- Bank: nomor rekening + atas nama (wajib untuk jenis Transfer Bank) -->
    <div class="field-row pm-bank-only">
        <label class="field">
            <span>Nomor Rekening <em class="req">*</em></span>
            <input type="text" name="account_number" value="{{ old('account_number', $method->account_number) }}" maxlength="100" placeholder="1234567890">
            @include('admin.partials.error', ['name' => 'account_number'])
        </label>
        <label class="field">
            <span>Atas Nama <em class="req">*</em></span>
            <input type="text" name="account_name" value="{{ old('account_name', $method->account_name) }}" maxlength="100" placeholder="PT Contoh Abadi">
            @include('admin.partials.error', ['name' => 'account_name'])
        </label>
    </div>

    <!-- QR/Barcode: upload gambar (wajib untuk jenis scan) -->
    <label class="field pm-scan-only">
        <span>Foto QR Code / Barcode <em class="req pm-required-star">*</em></span>
        <input type="file" name="qr_image" accept="image/png, image/jpeg, image/jpg, image/webp">
        @include('admin.partials.error', ['name' => 'qr_image'])

        @if(! $isNew && $method->qr_image)
            <div class="thumb-box">
                <p class="hint mb-2">Gambar saat ini:</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($method->qr_image) }}" alt="QR/Barcode">
            </div>
        @endif
    </label>

    <label class="field">
        <span>Instruksi (opsional)</span>
        <textarea name="instructions" rows="4" maxlength="2000" placeholder="Cara transfer, batas waktu pembayaran, dsb.">{{ old('instructions', $method->instructions) }}</textarea>
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
        <label class="switch mt-6">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active))>
            <span>Aktif</span>
        </label>
    </div>

    {{-- FASE 2: verifikasi otomatis khusus QRIS. --}}
    <label @class(['field', 'pm-qris-only', 'is-hidden' => old('type', $method->type ?? 'bank') !== 'qris']) id="pm-autoverify-field">
        <span class="switch m-0">
            <input type="checkbox" name="auto_verify" value="1" @checked(old('auto_verify', $method->auto_verify ?? false))>
            <span>Verifikasi Otomatis (khusus QRIS)</span>
        </span>
        <small class="muted">Isi <strong>ID Merchant QRIS / NMI</strong> pada kolom Nomor Rekening di atas, lalu aktifkan ini. Bukti bayar QRIS yang payload-nya cocok dengan ID merchant akan lolos verifikasi tanpa review manual.</small>
    </label>

    <div class="actions mt-5">
        <a href="{{ route('admin.payment-methods.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>

<script>
// Tampilkan hanya isian yang relevan per jenis pembayaran:
// - bank    -> rekening + atas nama
// - qris/barcode -> cukup foto QR/barcode
(function () {
    var sel = document.getElementById('pm-type');
    if (! sel) return;
    function sync() {
        var isBank = sel.value === 'bank';
        document.querySelectorAll('.pm-bank-only').forEach(function (el) { el.style.display = isBank ? '' : 'none'; });
        document.querySelectorAll('.pm-scan-only').forEach(function (el) { el.style.display = isBank ? 'none' : ''; });
        // FASE 2: opsi verifikasi otomatis hanya untuk QRIS.
        document.querySelectorAll('.pm-qris-only').forEach(function (el) { el.style.display = (sel.value === 'qris') ? '' : 'none'; });
        var star = document.querySelector('.pm-required-star');
        if (star) star.style.visibility = isBank ? 'hidden' : 'visible';
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>

@unless($isNew)
    <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="danger-zone"
          onsubmit="return confirm('Hapus metode {{ $method->label }}?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus metode</button>
    </form>
@endunless
@endsection
