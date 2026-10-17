<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemSnapshot extends Model
{
    protected $fillable = [
        'order_item_id',
        'product_id_audit', 'variant_id_audit',
        'product_name', 'product_slug', 'product_sku',
        'game_name', 'developer_name', 'store_name', 'tag',
        'variant_name', 'variant_sku', 'variant_attributes',
        'base_price', 'discount_amount', 'final_price', 'qty', 'subtotal',
        'weight_gram',
        'ongkir_lokal_cn_idr', 'ongkir_cn_btm_idr', 'ongkir_btm_jkt_idr', 'ongkir_total_idr',
        'product_image_path', 'variant_image_path',
        'snapshot_at',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'weight_gram' => 'integer',
        'ongkir_lokal_cn_idr' => 'decimal:2',
        'ongkir_cn_btm_idr' => 'decimal:2',
        'ongkir_btm_jkt_idr' => 'decimal:2',
        'ongkir_total_idr' => 'decimal:2',
        'snapshot_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    { return $this->belongsTo(OrderItem::class); }

    public function productStillExists(): bool
    {
        return Product::where('id', $this->product_id_audit)->exists();
    }
}
