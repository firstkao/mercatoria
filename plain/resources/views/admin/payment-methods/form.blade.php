@php($isNew = ! $method->exists)

@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Metode Pembayaran' : 'Edit: ' . $method->label,
    'back'  => route('admin.payment-methods.index'),
])

@section('content')
<form method="POST"
      action="{{ $isNew ? route('admin.payment-methods.store') : route('admin.payment-methods.update', $method) }}"
      class="panel"
      enctype="multipart/form-data">
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <div class="stack">

        {{-- ============================================================
             SECTION 1 — Info Utama
             ============================================================ --}}
        <div class="field-row">
            <label class="field">
                <span>Jenis Pembayaran <em class="req">*</em></span>
                <select name="type" id="pm-type" required>
                    @foreach($types as $value => $labelOption)
                        <option value="{{ $value }}" @selected(old('type', $method->type ?? 'bank') === $value)>
                            {{ $labelOption }}
                        </option>
                    @endforeach
                </select>
                @include('admin.partials.error', ['name' => 'type'])
            </label>

            <label class="field">
                <span>Label <em class="req">*</em></span>
                <input type="text"
                       name="label"
                       value="{{ old('label', $method->label) }}"
                       maxlength="100"
                       required
                       placeholder="Contoh: BCA / QRIS / Shopee Pay">
                @include('admin.partials.error', ['name' => 'label'])
            </label>
        </div>

        {{-- ============================================================
             SECTION 2 — Data Bank (khusus Transfer Bank)
             ============================================================ --}}
        <div class="field-row pm-bank-only">
            <label class="field">
                <span>Nomor Rekening <em class="req">*</em></span>
                <input type="text"
                       name="account_number"
                       value="{{ old('account_number', $method->account_number) }}"
                       maxlength="100"
                       placeholder="1234567890"
                       inputmode="numeric">
                @include('admin.partials.error', ['name' => 'account_number'])
            </label>

            <label class="field">
                <span>Atas Nama <em class="req">*</em></span>
                <input type="text"
                       name="account_name"
                       value="{{ old('account_name', $method->account_name) }}"
                       maxlength="100"
                       placeholder="PT Contoh Abadi">
                @include('admin.partials.error', ['name' => 'account_name'])
            </label>
        </div>

        {{-- ============================================================
             SECTION 3 — Foto QR/Barcode (khusus jenis scan)
             ============================================================ --}}
        <div class="pm-scan-only">
            <label class="field">
                <span>Foto QR Code / Barcode <em class="req">*</em></span>
                <input type="file"
                       name="qr_image"
                       accept="image/png, image/jpeg, image/jpg, image/webp">
                @include('admin.partials.error', ['name' => 'qr_image'])
            </label>

            @if(! $isNew && $method->qr_image)
                <div class="thumb-box mt-3">
                    <p class="hint mb-2">Gambar saat ini:</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($method->qr_image) }}"
                         alt="QR/Barcode">
                </div>
            @endif
        </div>

        {{-- ============================================================
             SECTION 4 — Instruksi
             ============================================================ --}}
        <label class="field">
            <span>Instruksi <span class="muted small">(opsional)</span></span>
            <textarea name="instructions"
                      rows="4"
                      maxlength="2000"
                      placeholder="Cara transfer, batas waktu pembayaran, dsb.">{{ old('instructions', $method->instructions) }}</textarea>
            @include('admin.partials.error', ['name' => 'instructions'])
        </label>

        {{-- ============================================================
             SECTION 5 — Urutan + Aktif
             ============================================================ --}}
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

            <div class="field" style="justify-content: flex-end;">
                <span>&nbsp;</span>
                <label class="switch">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $method->is_active))>
                    <span>Aktifkan metode ini</span>
                </label>
            </div>
        </div>

        {{-- ============================================================
             SECTION 6 — Auto Verify (khusus QRIS)
             ============================================================ --}}
        <label @class(['field', 'pm-qris-only', 'is-hidden' => old('type', $method->type ?? 'bank') !== 'qris']) id="pm-autoverify-field">
            <span class="switch m-0">
                <input type="checkbox"
                       name="auto_verify"
                       value="1"
                       @checked(old('auto_verify', $method->auto_verify ?? false))>
                <span>Verifikasi Otomatis (khusus QRIS)</span>
            </span>
            <small class="hint">
                Isi <strong>ID Merchant QRIS / NMI</strong> pada kolom Nomor Rekening di atas,
                lalu aktifkan ini. Bukti bayar QRIS yang payload-nya cocok dengan ID merchant
                akan lolos verifikasi tanpa review manual.
            </small>
        </label>

    </div>

    {{-- ============================================================
         ACTION BAR — Batal di kiri, Simpan di kanan
         ============================================================ --}}
    <div class="form-actions form-actions--between mt-6">
        <a href="{{ route('admin.payment-methods.index') }}" class="btn">
            Batal
        </a>
        <button type="submit" class="btn btn--primary">
            {{ $isNew ? '💾 Simpan Metode' : '💾 Simpan Perubahan' }}
        </button>
    </div>
</form>

{{-- ============================================================
     DANGER ZONE — terpisah dari form update
     ============================================================ --}}
@unless($isNew)
    <div class="panel panel--danger mt-6">
        <div class="panel__head">
            <h3 class="m-0 text-danger">⚠️ Zona Berbahaya</h3>
        </div>

        <p class="hint mb-4">
            Hapus metode pembayaran <strong>"{{ $method->label }}"</strong> secara permanen.
            Bukti pembayaran lama yang menggunakan metode ini <strong>tidak ikut terhapus</strong> —
            mereka akan tetap ada dengan label <em>"(metode dihapus)"</em>.
        </p>

        <form method="POST"
              action="{{ route('admin.payment-methods.destroy', $method) }}"
              onsubmit="return confirm('Hapus metode \'{{ $method->label }}\'?\n\nTindakan ini tidak bisa dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger-outline">
                🗑️ Hapus Metode "{{ $method->label }}"
            </button>
        </form>
    </div>
@endunless

<script>
// Tampilkan hanya isian yang relevan per jenis pembayaran:
// - bank         → rekening + atas nama
// - qris/barcode → cukup foto QR/barcode
(function () {
    var sel = document.getElementById('pm-type');
    if (! sel) return;

    function sync() {
        var isBank = sel.value === 'bank';

        document.querySelectorAll('.pm-bank-only').forEach(function (el) {
            el.style.display = isBank ? '' : 'none';
        });

        document.querySelectorAll('.pm-scan-only').forEach(function (el) {
            el.style.display = isBank ? 'none' : '';
        });

        // FASE 2: opsi verifikasi otomatis hanya untuk QRIS.
        document.querySelectorAll('.pm-qris-only').forEach(function (el) {
            el.style.display = (sel.value === 'qris') ? '' : 'none';
        });
    }

    sel.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
