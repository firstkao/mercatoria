<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CoinSource;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'source', 'description',
        'balance_after', 'expires_at', 'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function getSourceEnumAttribute(): CoinSource
    { return CoinSource::from($this->source); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
