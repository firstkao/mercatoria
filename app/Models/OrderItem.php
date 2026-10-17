<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'variant_id', 'qty'];

    public function order(): BelongsTo
    { return $this->belongsTo(Order::class); }

    public function product(): BelongsTo
    { return $this->belongsTo(Product::class); }

    public function variant(): BelongsTo
    { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function snapshot(): HasOne
    { return $this->hasOne(OrderItemSnapshot::class); }

    public function productStillExists(): bool
    {
        return $this->product_id !== null
            && Product::where('id', $this->product_id)->exists();
    }

    public function getDisplayNameAttribute(): string
    { return $this->snapshot?->product_name ?? '(Produk dihapus)'; }

    public function getDisplayPriceAttribute(): float
    { return (float) ($this->snapshot?->final_price ?? 0); }
}
