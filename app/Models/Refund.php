<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\RefundStatus;

class Refund extends Model
{
    protected $fillable = [
        'order_id', 'type', 'amount', 'reason', 'status',
        'admin_note', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    { return $this->belongsTo(Order::class); }

    public function processedBy(): BelongsTo
    { return $this->belongsTo(User::class, 'processed_by'); }

    public function getStatusEnumAttribute(): RefundStatus
    { return RefundStatus::from($this->status); }
}
