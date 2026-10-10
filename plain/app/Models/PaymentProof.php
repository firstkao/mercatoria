<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'payment_method_id',
    'payment_stage',
    'amount_idr',
    'proof_path',
    'status',
    'reject_reason',
    'reviewed_at',
    'resubmit_deadline_at',
    'uploaded_at',
])]
class PaymentProof extends Model
{
    /** Status bukti pembayaran. */
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_idr'            => 'integer',
            'reviewed_at'           => 'datetime',
            'resubmit_deadline_at'  => 'datetime',
            'uploaded_at'           => 'datetime',
        ];
    }

    /* ============================================================
     * RELASI
     * ============================================================ */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /* ============================================================
     * URL FILE BUKTI
     * ============================================================ */

    /**
     * URL internal ke file bukti (route cek auth: owner atau admin).
     * Dipakai di Blade: {{ $proof->url() }}
     */
    public function url(): string
    {
        return route('payment-proof.show', $this);
    }

    /* ============================================================
     * STATUS HELPERS
     * ============================================================ */

    /**
     * Label status dalam bahasa Indonesia.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default               => ucfirst((string) $this->status),
        };
    }

    /**
     * Class CSS badge sesuai status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'badge--on',
            self::STATUS_REJECTED => 'badge--off',
            default               => '',
        };
    }

    /**
     * Class CSS badge versi kecil (untuk mobile / cards).
     */
    public function statusBadgeSmallClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'badge-small--on',
            self::STATUS_REJECTED => 'badge-small--off',
            default               => '',
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
