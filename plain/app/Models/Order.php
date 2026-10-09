<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
    // ===== Order & Pembayaran =====
    'user_id',
    'order_number',
    'status',
    'payment_scheme',
    'subtotal_idr',
    'discount_type',
    'discount_idr',
    'total_idr',
    'pay_now_idr',
    'remaining_idr',
    'marketplace_id',
    'marketplace_fee_idr',
    'qris_fee_idr',
    'coin_estimate',
    'pricing_snapshot',

    // ===== Timestamp Status =====
    'payment_deadline_at',
    'paid_at',
    'completed_at',
    'cancelled_at',

    // ===== Catatan =====
    'refund_note',
    'customer_note',

    // ===== (2) PENERIMA & PENGIRIMAN =====
    'recipient_name',
    'recipient_phone',
    'recipient_email',
    'shipping_address',
    'shipping_city',
    'shipping_province',
    'shipping_postal_code',
    'shipping_note',

    // ===== (4) TRACKING KUNJUNGAN =====
    'source',
    'device_type',
    'landing_page',
    'referrer',
    'session_page_views',
    'user_agent',
    'ip_address',
];

    protected function casts(): array
    {
        return [
            'pricing_snapshot' => 'array',
            'payment_deadline_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal_idr' => 'integer',
            'discount_idr' => 'integer',
            'total_idr' => 'integer',
            'pay_now_idr' => 'integer',
            'remaining_idr' => 'integer',
            'marketplace_fee_idr' => 'integer',
            'qris_fee_idr' => 'integer',   // ← tambah baris ini
            'coin_estimate' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    /**
     * ✅ PERAPIAN: bukti pembayaran terbaru berdasarkan waktu unggah.
     * paymentProofs() tidak punya orderBy, sedangkan beberapa view memakai
     * ->last() untuk mengambil "bukti terakhir". Tanpa ordering eksplisit,
     * hasil depends pada urutan DB (normalnya id ASC, jadi kebetulan benar),
     * tapi bisa salah begitu relasi di-eager-load dengan join/order lain.
     */
    public function latestPaymentProof(): ?PaymentProof
    {
        return $this->paymentProofs()->latest('uploaded_at')->latest('id')->first();
    }

    /**
     * Human-readable label untuk status saat ini.
     */
    public function statusLabel(): string
    {
        return OrderStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    /**
     * CSS badge class untuk status saat ini.
     */
    public function statusBadgeClass(): string
    {
        return OrderStatus::tryFrom($this->status)?->badgeClass() ?? '';
    }
}
