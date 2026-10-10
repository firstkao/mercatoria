<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'product_name_snapshot', 'variant_name_snapshot',
        'price_yuan_snapshot', 'weight_grams_snapshot', 'cn_shipping_yuan_snapshot',
        'unit_price_idr', 'quantity', 'line_total_idr',
        'item_status', 'item_status_updated_at', 'split_box_slot_id',
    ];

    protected function casts(): array
    {
        return [
            'price_yuan_snapshot' => 'decimal:2',
            'cn_shipping_yuan_snapshot' => 'decimal:2',
            'weight_grams_snapshot' => 'integer',
            'unit_price_idr' => 'integer',
            'quantity' => 'integer',
            'line_total_idr' => 'integer',
            'item_status_updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }

    /**
     * Status efektif: item override kalau di-set, kalau tidak ikut order.
     */
    public function effectiveStatus(): string
    {
        return $this->item_status ?? $this->order->status;
    }

    public function effectiveStatusLabel(): string
    {
        $code = $this->effectiveStatus();
        return OrderStatus::tryFrom($code)?->label() ?? $code;
    }

    public function effectiveStatusBadgeClass(): string
    {
        $code = $this->effectiveStatus();
        return OrderStatus::tryFrom($code)?->badgeClass() ?? '';
    }

    /**
     * True kalau item ini punya status sendiri yang beda dari order.
     */
    public function hasStatusOverride(): bool
    {
        return $this->item_status !== null && $this->item_status !== $this->order->status;
    }

    public function splitBoxSlot(): BelongsTo
    {
        return $this->belongsTo(SplitBoxSlot::class, 'split_box_slot_id');
    }
}
