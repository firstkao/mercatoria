@php($isNew = ! $method->exists)

@extends('admin.layouts.app', [
    'title' => $isNew ? 'Tambah Metode Pembayaran' : 'Edit: ' . $method->label,
    'back'  => route('admin.payment-methods.index'),
])

@push('head')
<style>
    /* ============================================================
       TOGGLE AKTIF — override .field input biar jadi switch pill
       ============================================================ */
    .pm-active-toggle {
        display: flex;
        align-items: flex-end;
        padding-bottom: 8px;
    }
    .pm-active-toggle .switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        font-size: 14px;
        color: var(--text, #1e293b);
        cursor: pointer;
        user-select: none;
    }
    .pm-active-toggle .switch input[type="checkbox"] {
        /* Reset total — biar gak kena rule `.field input` */
        appearance: none !important;
        -webkit-appearance: none !important;
        width: 44px !important;
        height: 24px !important;
        min-height: 24px !important;
        max-height: 24px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 999px !important;
        background: #cbd5e1 !important;
        box-shadow: none !important;
        position: relative;
        transition: background .2s;
        flex-shrink: 0;
        cursor: pointer;
    }
    .pm-active-toggle .switch input[type="checkbox"]::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        transition: left .2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
    }
    .pm-active-toggle .switch input[type="checkbox"]:checked {
        background: var(--primary, #0ea5e9) !important;
    }
    .pm-active-toggle .switch input[type="checkbox"]:checked::after {
        left: 22px;
    }
    .pm-active-toggle .switch input[type="checkbox"]:focus-visible {
        outline: 2px solid var(--primary, #0ea5e9);
        outline-offset: 2px;
    }

    /* ============================================================
       MODAL KONFIRMASI HAPUS
       ============================================================ */
    .confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .confirm-modal[hidden] { display: none !important; }
    .confirm-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .6);
        border: 0;
        padding: 0;
        cursor: pointer;
    }
    .confirm-modal__panel {
        position: relative;
        width: 100%;
        max-width: 480px;
        max-height: 90dvh;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }
    .confirm-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
    }
    .confirm-modal__head h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .confirm-modal__close {
        background: none;
        border: 0;
        padding: 4px;
        color: #64748b;
        cursor: pointer;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
    }
    .confirm-modal__close:hover { background: #f1f5f9; color: #0f172a; }

    .confirm-modal__body {
        padding: 22px;
        font-size: 14px;
        color: #1e293b;
        line-height: 1.6;
    }
    .confirm-modal__hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        text-align: center;
    }
    .confirm-modal__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        flex-shrink: 0;
        background: #fef2f2;
        color: #dc2626;
    }
    .confirm-modal__title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -.01em;
        line-height: 1.3;
    }
    .confirm-modal__lead {
        margin: 0 0 16px;
        color: #475569;
        line-height: 1.65;
    }
    .confirm-modal__lead strong { color: #0f172a; font-weight: 600; }

    .confirm-modal__list {
        list-style: none;
        margin: 0;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .confirm-modal__list li {
        position: relative;
        padding-left: 20px;
        font-size: 13.5px;
        color: #334155;
        line-height: 1.55;
    }
    .confirm-modal__list li::before {
        content: "";
        position: absolute;
        left: 2px;
        top: 8px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #94a3b8;
    }
    .confirm-modal__list li strong { color: #0f172a; font-weight: 600; }

    .confirm-modal__foot {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 22px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }
    .confirm-modal__foot .btn { min-width: 100px; }

    @media (max-width: 640px) {
        .confirm-modal__foot { flex-direction: column-reverse; }
        .confirm-modal__foot .btn { width: 100%; }
        .confirm-modal__icon { width: 48px; height: 48px; font-size: 22px; }
        .confirm-modal__title { font-size: 16px; }
    }
</style>
@endpush

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
             SECTION 3 — Foto QR/Barcode
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
             SECTION 5 — Urutan + Aktif (2 kolom)
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

            <div class="pm-active-toggle">
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

    {{-- Action bar --}}
    <div class="form-actions form-actions--between mt-6">
        <a href="{{ route('admin.payment-methods.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">
            {{ $isNew ? '💾 Simpan Metode' : '💾 Simpan Perubahan' }}
        </button>
    </div>
</form>

{{-- ============================================================
     DANGER ZONE — Hapus (khusus mode edit)
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

        <button type="button"
                class="btn btn--danger-outline"
                id="pm-delete-trigger">
            🗑️ Hapus Metode "{{ $method->label }}"
        </button>
    </div>

    {{-- Form delete tersembunyi (biar tombol di atas cuma trigger modal) --}}
    <form method="POST"
          action="{{ route('admin.payment-methods.destroy', $method) }}"
          id="pm-delete-form"
          hidden>
        @csrf
        @method('DELETE')
    </form>
@endunless

{{-- ============================================================
     MODAL KONFIRMASI HAPUS
     ============================================================ --}}
@unless($isNew)
    <div class="confirm-modal" id="pm-delete-modal" hidden>
        <button type="button" class="confirm-modal__backdrop" data-modal-close aria-label="Tutup"></button>

        <div class="confirm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="pm-delete-title">
            <div class="confirm-modal__head">
                <h2 id="pm-delete-title">Konfirmasi Hapus</h2>
                <button type="button" class="confirm-modal__close" data-modal-close aria-label="Tutup">✕</button>
            </div>

            <div class="confirm-modal__body">
                <div class="confirm-modal__hero">
                    <div class="confirm-modal__icon">⚠</div>
                    <h3 class="confirm-modal__title">Hapus Metode Pembayaran?</h3>
                </div>

                <p class="confirm-modal__lead">
                    Yakin hapus metode <strong>"{{ $method->label }}"</strong>? Tindakan ini <strong>tidak bisa dibatalkan</strong>.
                </p>

                <ul class="confirm-modal__list">
                    <li>Bukti pembayaran lama <strong>tetap ada</strong> dengan label "(metode dihapus)"</li>
                    <li>Pembeli tidak akan bisa pilih metode ini lagi saat checkout</li>
                </ul>
            </div>

            <div class="confirm-modal__foot">
                <button type="button" class="btn" data-modal-close>Batal</button>
                <button type="button" class="btn btn--danger" id="pm-delete-confirm">
                    🗑️ Ya, Hapus
                </button>
            </div>
        </div>
    </div>
@endunless

<script>
/* ============================================================
   Show / hide field sesuai jenis pembayaran
   - bank         → rekening + atas nama
   - qris/barcode → foto QR/barcode + opsi auto-verify
   ============================================================ */
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

        document.querySelectorAll('.pm-qris-only').forEach(function (el) {
            el.style.display = (sel.value === 'qris') ? '' : 'none';
        });
    }

    sel.addEventListener('change', sync);
    sync();
})();

/* ============================================================
   Modal konfirmasi hapus
   ============================================================ */
(function () {
    var trigger  = document.getElementById('pm-delete-trigger');
    var modal    = document.getElementById('pm-delete-modal');
    var form     = document.getElementById('pm-delete-form');
    var confirmBtn = document.getElementById('pm-delete-confirm');

    if (! trigger || ! modal || ! form) return;

    function openModal() {
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { confirmBtn.focus(); }, 50);
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    trigger.addEventListener('click', openModal);

    modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    confirmBtn.addEventListener('click', function () {
        closeModal();
        form.submit();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && ! modal.hasAttribute('hidden')) {
            closeModal();
        }
    });
})();
</script>
@endsection
