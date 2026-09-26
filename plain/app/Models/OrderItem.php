<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'product_name_snapshot', 'variant_name_snapshot',
        'price_yuan_snapshot', 'weight_grams_snapshot', 'cn_shipping_yuan_snapshot',
        'unit_price_idr', 'quantity', 'line_total_idr'
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
