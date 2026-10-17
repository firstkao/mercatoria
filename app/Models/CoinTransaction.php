<?php

namespace App\Models;

use App\Enums\CoinSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'amount', 'source', 'description',
        'balance_after', 'expires_at', 'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getSourceEnumAttribute(): CoinSource
    {
        return CoinSource::from($this->source);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
