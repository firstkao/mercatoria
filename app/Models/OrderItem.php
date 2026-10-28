<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'qty',
        'bundle_group_id',
        'split_box_slot_id',
        'split_box_batch_slot_id',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function splitBoxSlot(): BelongsTo
    {
        return $this->belongsTo(SplitBoxSlot::class);
    }

    public function splitBoxBatchSlot(): BelongsTo
    {
        return $this->belongsTo(SplitBoxBatchSlot::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(OrderItemSnapshot::class);
    }
}
