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

    $isPending = ($proof->status ?? '') === 'pending';
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
                      class="stack stack--tight">
                    @csrf
                    <button type="submit"
                            class="btn btn--primary btn--block"
                            onclick="return confirm('Setujui pembayaran ini?\n\nOrder akan ditandai sebagai Pembayaran Diterima, dan akun pembeli jadi Customer.');">
                        ✓ Setujui Pembayaran
                    </button>
                </form>

                {{-- Divider --}}
                <div class="divider-top"></div>

                {{-- Form Tolak --}}
                <form method="POST"
                      action="{{ route('admin.payments.reject', $proof->id) }}"
                      class="stack stack--tight">
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

                    <button type="submit"
                            class="btn btn--danger-outline btn--block"
                            onclick="return confirm('Tolak bukti pembayaran ini?\n\nPembeli akan diberi waktu 24 jam untuk upload ulang.');">
                        ✗ Tolak Bukti
                    </button>
                </form>
            </x-admin.card>
        @else
            {{-- ============================================================
                 SUDAH DIREVIEW — tampilkan hasil
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
@endsection
