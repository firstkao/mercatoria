<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_item_id',
        'product_id_audit',
        'variant_id_audit',
        'item_type',
        'bundle_group_id',
        'split_batch_no',
        'product_name',
        'product_sku',
        'variant_name',
        'variant_sku',
        'base_price',
        'discount_amount',
        'final_price',
        'qty',
        'subtotal',
        'product_image_path',
        'variant_image_path',
        'weight_gram',
        'store_name',
        'ongkir_lokal_cn_idr',
        'ongkir_cn_btm_idr',
        'ongkir_btm_jkt_idr',
        'ongkir_total_idr',
        'snapshot_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'qty' => 'integer',
            'subtotal' => 'decimal:2',
            'weight_gram' => 'integer',
            'ongkir_lokal_cn_idr' => 'decimal:2',
            'ongkir_cn_btm_idr' => 'decimal:2',
            'ongkir_btm_jkt_idr' => 'decimal:2',
            'ongkir_total_idr' => 'decimal:2',
            'snapshot_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
