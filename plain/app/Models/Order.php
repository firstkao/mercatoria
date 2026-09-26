<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'marketplace_id', 'status', 'payment_scheme',
        'subtotal_idr', 'discount_type', 'discount_idr', 'total_idr', 'pay_now_idr',
        'remaining_idr', 'marketplace_fee_idr', 'coin_estimate', 'pricing_snapshot',
        'payment_deadline_at', 'paid_at', 'completed_at', 'cancelled_at', 'refund_note'
    ];

    protected function casts(): array
    {
        return [
            'pricing_snapshot' => 'array',
            'payment_deadline_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function marketplace(): BelongsTo { return $this->belongsTo(Marketplace::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
}
