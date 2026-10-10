@extends('admin.layouts.app', ['title' => 'Metode Pembayaran'])

@push('head')
<style>
    .pm-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .pm-table thead th {
        padding: 14px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
    }
    .pm-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #1e293b;
        vertical-align: middle;
    }
    .pm-table tbody tr {
        transition: background .12s;
    }
    .pm-table tbody tr:hover {
        background: #fafbfc;
    }
    .pm-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* Kolom label */
    .pm-table .pm-label {
        font-weight: 600;
        color: #0f172a;
    }

    /* Kolom jenis — pill abu */
    .pm-table .pm-type {
        display: inline-flex;
        padding: 3px 10px;
        background: #f1f5f9;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    /* Kolom qr — link kecil */
    .pm-table .pm-qr-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #0284c7;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
    }
    .pm-table .pm-qr-link:hover { text-decoration: underline; }

    /* Kolom status — badge */
    .pm-status {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .pm-status--active {
        background: #d1fae5;
        color: #065f46;
    }
    .pm-status--inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Kolom aksi — Edit | Hapus */
    .pm-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .pm-actions a,
    .pm-actions button {
        background: none;
        border: 0;
        padding: 0;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: color .12s;
    }
    .pm-actions a       { color: #0284c7; }
    .pm-actions a:hover { color: #0369a1; text-decoration: underline; }
    .pm-actions button  { color: #dc2626; }
    .pm-actions button:hover { color: #b91c1c; text-decoration: underline; }
    .pm-actions .pm-actions__sep {
        color: #cbd5e1;
        user-select: none;
    }

    /* Mobile — table wrap */
    @media (max-width: 900px) {
        .pm-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .pm-table {
            min-width: 900px;
        }
    }
</style>
@endpush

@section('content')
<x-admin.card>
    <div class="panel__head">
        <h2 class="m-0">Metode Pembayaran</h2>
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn--primary btn--small">
            + Tambah Metode
        </a>
    </div>

    @if ($methods->isEmpty())
        <div class="empty">
            <p>Belum ada metode pembayaran.</p>
            <a href="{{ route('admin.payment-methods.create') }}" class="btn btn--primary btn--small mt-3">
                + Tambah Metode Pertama
            </a>
        </div>
    @else
        <div class="pm-table-wrap">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Jenis</th>
                        <th>Nomor Rekening</th>
                        <th>Atas Nama</th>
                        <th>QR/Barcode</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-center">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($methods as $method)
                        <tr>
                            <td class="pm-label">{{ $method->label }}</td>

                            <td>
                                <span class="pm-type">
                                    {{ $types[$method->type] ?? ucfirst((string) $method->type) }}
                                </span>
                            </td>

                            <td>
                                @if ($method->account_number)
                                    <span class="mono">{{ $method->account_number }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>

                            <td>
                                {{ $method->account_name ?: '—' }}
                            </td>

                            <td>
                                @if ($method->qr_image)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($method->qr_image) }}"
                                       target="_blank" rel="noopener"
                                       class="pm-qr-link">
                                        🔍 Lihat
                                    </a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">{{ $method->sort_order ?? 0 }}</td>

                            <td class="text-center">
                                @if ($method->is_active)
                                    <span class="pm-status pm-status--active">Aktif</span>
                                @else
                                    <span class="pm-status pm-status--inactive">Nonaktif</span>
                                @endif
                            </td>

                            <td>
                                <div class="pm-actions">
                                    <a href="{{ route('admin.payment-methods.edit', $method) }}">Edit</a>
                                    <span class="pm-actions__sep">|</span>
                                    <button type="button"
                                            class="js-delete-trigger"
                                            data-delete-id="{{ $method->id }}"
                                            data-delete-label="{{ $method->label }}">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (method_exists($methods, 'links') && $methods->hasPages())
            <div class="p-5">
                {{ $methods->links('admin.partials.pagination') }}
            </div>
        @endif
    @endif
</x-admin.card>

{{-- ============================================================
     FORM DELETE — 1 form tersembunyi, action di-set via JS
     ============================================================ --}}
<form method="POST" id="pm-delete-form" hidden>
    @csrf
    @method('DELETE')
</form>

{{-- ============================================================
     MODAL KONFIRMASI HAPUS
     ============================================================ --}}
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
                Yakin hapus metode <strong id="pm-delete-modal-label">—</strong>? Tindakan ini <strong>tidak bisa dibatalkan</strong>.
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
@endsection

@push('head')
<style>
    /* ===== MODAL KONFIRMASI (kalau belum ada di layout) ===== */
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

@push('scripts')
<script>
(function () {
    var modal      = document.getElementById('pm-delete-modal');
    var modalLabel = document.getElementById('pm-delete-modal-label');
    var form       = document.getElementById('pm-delete-form');
    var confirmBtn = document.getElementById('pm-delete-confirm');

    if (! modal || ! form) return;

    var pendingId = null;

    function openModal(id, label) {
        pendingId = id;
        modalLabel.textContent = '"' + label + '"';
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { confirmBtn.focus(); }, 50);
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
        pendingId = null;
    }

    // Tombol hapus di setiap baris
    document.querySelectorAll('.js-delete-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal(btn.dataset.deleteId, btn.dataset.deleteLabel);
        });
    });

    // Close handlers
    modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    // Konfirmasi hapus — set action form lalu submit
    confirmBtn.addEventListener('click', function () {
        if (! pendingId) return;

        form.action = '{{ url('office/payment-methods') }}/' + pendingId;
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
@endpush
