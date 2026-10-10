@extends('admin.layouts.app', ['title' => 'Bukti Pembayaran ' . $proof->order_number, 'back' => route('admin.payments.index')])

@section('content')
@php
    $statusLabel = match ($proof->status ?? '') {
        'pending'  => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        default    => ucfirst((string) ($proof->status ?? '—')),
    };

    $statusBadge = match ($proof->status ?? '') {
        'approved' => 'badge--on',
        'rejected' => 'badge--danger',
        'pending'  => 'badge--warn',
        default    => '',
    };

    $isPending  = ($proof->status ?? '') === 'pending';
    $isSpammer  = ($proof->user_role ?? '') === 'spammer';
@endphp

<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- ============================================================
             INFO BUKTI
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h2 class="m-0">#{{ $proof->order_number }}</h2>
                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
            </div>

            <dl class="deflist">
                <div>
                    <dt>Pembeli</dt>
                    <dd>
                        {{ $proof->full_name ?? '—' }}
                        @if ($proof->email)
                            <span class="muted small">({{ $proof->email }})</span>
                        @endif
                        @if ($isSpammer)
                            <span class="badge badge--danger ms-2">Spammer</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Metode Pembayaran</dt>
                    <dd>{{ $proof->payment_method_label ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Jumlah</dt>
                    <dd class="text-price">
                        Rp {{ number_format($proof->amount_idr, 0, ',', '.') }}
                    </dd>
                </div>
                <div>
                    <dt>Status Order</dt>
                    <dd>
                        @php
                            $orderStatusLabel = \App\Enums\OrderStatus::tryFrom($proof->order_status)?->label() ?? $proof->order_status;
                        @endphp
                        <span class="badge {{ \App\Enums\OrderStatus::tryFrom($proof->order_status)?->badgeClass() ?? '' }}">
                            {{ $orderStatusLabel }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Diunggah</dt>
                    <dd>
                        {{ $proof->uploaded_at ? $proof->uploaded_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') : '—' }} WIB
                    </dd>
                </div>
                @if ($proof->reviewed_at)
                    <div>
                        <dt>Direview</dt>
                        <dd>{{ $proof->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd>
                    </div>
                @endif
                @if ($proof->reject_reason)
                    <div>
                        <dt>Alasan Tolak</dt>
                        <dd class="text-danger">{{ $proof->reject_reason }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        {{-- ============================================================
             GAMBAR BUKTI
             ============================================================ --}}
        <x-admin.card>
            <div class="panel__head">
                <h3 class="m-0">Bukti Pembayaran</h3>
                <a href="{{ $imageUrl }}" target="_blank" rel="noopener" class="btn btn--small">
                    🔍 Buka Ukuran Penuh
                </a>
            </div>

            <a href="{{ $imageUrl }}"
               target="_blank" rel="noopener"
               class="payment-proof-image-wrap"
               title="Klik untuk buka di tab baru">
                <img src="{{ $imageUrl }}"
                     alt="Bukti Transfer"
                     class="payment-proof-image">
            </a>
        </x-admin.card>

    </div>

    <aside class="form-grid__side stack">

        {{-- ============================================================
             VERIFIKASI (cuma muncul kalau status = pending)
             ============================================================ --}}
        @if ($isPending)
            <x-admin.card>
                <div class="panel__head">
                    <h3 class="m-0">Verifikasi</h3>
                    <span class="badge badge--warn">Perlu Aksi</span>
                </div>

                <p class="hint mb-4">
                    Periksa gambar bukti di samping. Kalau nominal & tujuan transfer sudah sesuai, klik <strong>Setujui</strong>.
                </p>

                {{-- Form Setujui --}}
                <form method="POST"
                      action="{{ route('admin.payments.approve', $proof->id) }}"
                      class="js-confirm-form"
                      data-modal-type="approve"
                      data-modal-order="{{ $proof->order_number }}"
                      data-modal-amount="Rp {{ number_format($proof->amount_idr, 0, ',', '.') }}"
                      data-modal-buyer="{{ $proof->full_name }}"
                      data-modal-spammer="{{ $isSpammer ? '1' : '0' }}">
                    @csrf
                    <button type="submit" class="btn btn--primary btn--block">
                        ✓ Setujui Pembayaran
                    </button>
                </form>

                {{-- Divider --}}
                <div class="divider-top"></div>

                {{-- Form Tolak --}}
                <form method="POST"
                      action="{{ route('admin.payments.reject', $proof->id) }}"
                      class="stack stack--tight js-confirm-form"
                      data-modal-type="reject"
                      data-modal-order="{{ $proof->order_number }}">
                    @csrf

                    <label class="field">
                        <span>Alasan Tolak <em class="req">*</em></span>
                        <textarea name="reject_reason"
                                  rows="3"
                                  required
                                  maxlength="1000"
                                  class="textarea"
                                  placeholder="Contoh: Nominal transfer tidak sesuai / bukti tidak jelas / mutasi belum masuk.">{{ old('reject_reason') }}</textarea>
                    </label>

                    @error('reject_reason')
                        <p class="error">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn btn--danger-outline btn--block">
                        ✗ Tolak Bukti
                    </button>
                </form>
            </x-admin.card>
        @else
            {{-- ============================================================
                 SUDAH DIREVIEW
                 ============================================================ --}}
            <x-admin.card>
                <h3>Hasil Verifikasi</h3>
                <dl class="deflist">
                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                        </dd>
                    </div>
                    @if ($proof->reviewed_at)
                        <div>
                            <dt>Direview</dt>
                            <dd>{{ $proof->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('admin.payments.index') }}"
                   class="btn btn--outline btn--block mt-4">
                    ← Kembali ke Daftar
                </a>
            </x-admin.card>
        @endif

    </aside>
</div>

{{-- ============================================================
     MODAL KONFIRMASI
     ============================================================ --}}
@if ($isPending)
    <div class="modal" id="confirm-modal" hidden>
        <button type="button" class="modal__backdrop" data-modal-close aria-label="Tutup"></button>

        <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
            <div class="modal__head">
                <h2 id="confirm-modal-title" class="m-0">Konfirmasi</h2>
                <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">✕</button>
            </div>

            <div class="modal__body">
                {{-- Ikon + heading --}}
                <div class="confirm-modal__hero">
                    <div class="confirm-modal__icon" id="confirm-modal-icon"></div>
                    <h3 class="confirm-modal__title" id="confirm-modal-heading"></h3>
                </div>

                {{-- Isi pesan --}}
                <div class="confirm-modal__content" id="confirm-modal-content"></div>
            </div>

            <div class="modal__foot">
                <button type="button" class="btn" data-modal-close>Batal</button>
                <button type="button" class="btn btn--primary" id="confirm-modal-action">
                    Lanjut
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const modal        = document.getElementById('confirm-modal');
            const modalHeading = document.getElementById('confirm-modal-heading');
            const modalContent = document.getElementById('confirm-modal-content');
            const modalIcon    = document.getElementById('confirm-modal-icon');
            const modalAction  = document.getElementById('confirm-modal-action');

            if (!modal) return;

            let pendingForm = null;

            function openModal(form) {
                pendingForm = form;

                const type    = form.dataset.modalType;
                const order   = form.dataset.modalOrder || '';
                const amount  = form.dataset.modalAmount || '';
                const buyer   = form.dataset.modalBuyer || '';
                const spammer = form.dataset.modalSpammer === '1';

                // Reset
                modalAction.classList.remove('btn--primary', 'btn--danger');

                if (type === 'approve') {
                    modalIcon.textContent = '✓';
                    modalIcon.className = 'confirm-modal__icon confirm-modal__icon--success';
                    modalHeading.textContent = 'Setujui Pembayaran?';

                    // Bullet list — conditional spammer
                    let bullets = `
                        <li>Nominal: <strong>${amount}</strong></li>
                        <li>Order masuk antrian <strong>Sedang Diproses</strong></li>
                    `;

                    // Cuma tampil kalau role user = spammer
                    if (spammer) {
                        bullets = `
                            <li>Nominal: <strong>${amount}</strong></li>
                            <li>Akun pembeli otomatis jadi <strong>Customer</strong></li>
                            <li>Order masuk antrian <strong>Sedang Diproses</strong></li>
                        `;
                    }

                    modalContent.innerHTML = `
                        <p class="confirm-modal__lead">Order <strong>#${order}</strong> dari <strong>${buyer}</strong> akan ditandai <strong>Pembayaran Diterima</strong>.</p>
                        <ul class="confirm-modal__list">${bullets}</ul>
                    `;

                    modalAction.textContent = 'Ya, Setujui';
                    modalAction.classList.add('btn--primary');
                    modalAction.dataset.action = 'approve';

                } else if (type === 'reject') {
                    modalIcon.textContent = '✗';
                    modalIcon.className = 'confirm-modal__icon confirm-modal__icon--danger';
                    modalHeading.textContent = 'Tolak Bukti Pembayaran?';

                    modalContent.innerHTML = `
                        <p class="confirm-modal__lead">Order <strong>#${order}</strong> akan ditandai <strong>Ditolak</strong>.</p>
                        <ul class="confirm-modal__list">
                            <li>Pembeli dapat <strong>waktu 24 jam</strong> untuk upload ulang</li>
                            <li>Notifikasi + email otomatis dikirim ke pembeli</li>
                        </ul>
                    `;

                    modalAction.textContent = 'Ya, Tolak';
                    modalAction.classList.remove('btn--primary');
                    modalAction.classList.add('btn--danger');
                    modalAction.dataset.action = 'reject';
                }

                modal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';

                setTimeout(() => modalAction.focus(), 50);
            }

            function closeModal() {
                modal.setAttribute('hidden', '');
                document.body.style.overflow = '';
                pendingForm = null;
            }

            document.querySelectorAll('.js-confirm-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    openModal(form);
                });
            });

            modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
                el.addEventListener('click', closeModal);
            });

            modalAction.addEventListener('click', function () {
                if (pendingForm) {
                    const form = pendingForm;
                    closeModal();
                    form.submit();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
    @endpush
@endif

{{-- ============================================================
     CSS MODAL KONFIRMASI — DIPERBAIKI
     ============================================================ --}}
@push('styles')
<style>
    /* Hero: ikon + judul, center */
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
    }
    .confirm-modal__icon--success {
        background: #ecfdf5;
        color: #059669;
    }
    .confirm-modal__icon--danger {
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

    /* Konten */
    .confirm-modal__content {
        font-size: 14px;
        color: #1e293b;
        line-height: 1.6;
    }

    .confirm-modal__lead {
        margin: 0 0 16px;
        color: #475569;
        line-height: 1.65;
    }
    .confirm-modal__lead strong {
        color: #0f172a;
        font-weight: 600;
    }

    /* List bullet rapi */
    .confirm-modal__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
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
    .confirm-modal__list li strong {
        color: #0f172a;
        font-weight: 600;
    }

    /* Tombol footer */
    .modal__foot .btn {
        min-width: 96px;
    }

    /* Mobile */
    @media (max-width: 640px) {
        .modal__foot {
            flex-direction: column-reverse;
        }
        .modal__foot .btn {
            width: 100%;
        }
        .confirm-modal__icon {
            width: 48px;
            height: 48px;
            font-size: 22px;
        }
        .confirm-modal__title {
            font-size: 16px;
        }
    }
</style>
@endpush
@endsection
