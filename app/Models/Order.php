<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;

class Order extends Model
{
    protected $fillable = [
        'order_no', 'user_id', 'status',
        'payment_scheme', 'platform', 'payment_method',
        'customer_name', 'customer_email', 'customer_phone', 'customer_address',
        'referral_source', 'landing_page', 'customer_note',
        'note_to_customer', 'admin_note', 'note_to_customer_updated_at',
        'subtotal', 'discount_amount', 'fee_marketplace', 'fee_qris',
        'shipping_cost', 'total',
        'frozen_rate_rmb_idr', 'frozen_profit_percent',
        'pickup_deadline_at', 'hangus_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fee_marketplace' => 'decimal:2',
        'fee_qris' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'pickup_deadline_at' => 'datetime',
        'hangus_at' => 'datetime',
        'note_to_customer_updated_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────
    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function items(): HasMany
    { return $this->hasMany(OrderItem::class); }

    public function payments(): HasMany
    { return $this->hasMany(Payment::class); }

    public function refunds(): HasMany
    { return $this->hasMany(Refund::class); }

    public function statusLogs(): HasMany
    { return $this->hasMany(OrderStatusLog::class)->orderBy('created_at'); }

    // ─── Helpers ───────────────────────────────
    public function getStatusEnumAttribute(): OrderStatus
    { return OrderStatus::from($this->status); }

    public function isFp(): bool { return $this->payment_scheme === 'fp'; }
    public function isDp(): bool { return $this->payment_scheme === 'dp'; }
    public function isHangus(): bool { return $this->status === 'hangus'; }

    public function isPickupActive(): bool
    {
        return $this->status === OrderStatus::MENUNGGU_PELUNASAN->value
            && $this->pickup_deadline_at
            && $this->pickup_deadline_at->isFuture();
    }

    public function getIsPickupUrgentAttribute(): bool
    {
        if (! $this->isPickupActive()) return false;
        return $this->pickup_deadline_at->diffInHours(now()) < 24;
    }

    public function getPickupRemainingSecondsAttribute(): int
    {
        if (! $this->isPickupActive()) return 0;
        return max(0, now()->diffInSeconds($this->pickup_deadline_at, false));
    }

    public function dpAmount(): float
    { return (float) $this->total * 0.5; }

    public function remainingAmount(): float
    {
        if ($this->isFp()) return 0;
        $paid = $this->payments()->where('status', 'verified')->sum('amount');
        return max(0, (float) $this->total - (float) $paid);
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        $current = $this->status_enum;
        if ($current->isEnded()) return false;

        $allowed = [
            OrderStatus::MENUNGGU_PEMBAYARAN->value => [
                OrderStatus::DITAHAN->value, OrderStatus::PEMBAYARAN_GAGAL->value,
            ],
            OrderStatus::DITAHAN->value => [
                OrderStatus::PEMBAYARAN_DITERIMA->value,
                OrderStatus::PEMBAYARAN_GAGAL->value,
                OrderStatus::MENUNGGU_PEMBAYARAN->value,
            ],
            OrderStatus::PEMBAYARAN_DITERIMA->value => [
                OrderStatus::SEDANG_DIPROSES->value, OrderStatus::DANA_DIKEMBALIKAN->value,
            ],
            OrderStatus::SEDANG_DIPROSES->value => [OrderStatus::SAMPAI_WH_CHINA->value],
            OrderStatus::SAMPAI_WH_CHINA->value => [OrderStatus::DIKIRIM_KE_INDONESIA->value],
            OrderStatus::DIKIRIM_KE_INDONESIA->value => [OrderStatus::BEA_CUKAI->value],
            OrderStatus::BEA_CUKAI->value => [OrderStatus::SAMPAI_WH_INDONESIA->value],
            OrderStatus::SAMPAI_WH_INDONESIA->value => [OrderStatus::MENUNGGU_PELUNASAN->value],
            OrderStatus::MENUNGGU_PELUNASAN->value => [
                OrderStatus::SEDANG_DIKEMAS->value, OrderStatus::HANGUS->value,
            ],
            OrderStatus::SEDANG_DIKEMAS->value => [OrderStatus::SELESAI->value],
        ];

        return in_array($newStatus->value, $allowed[$current->value] ?? []);
    }
}
