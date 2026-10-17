<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\UserState;
use App\Enums\TierLevel;

class Customer extends Model
{
    protected $fillable = [
        'user_id', 'current_state', 'state_updated_at',
        'tier', 'total_belanja', 'total_orders', 'last_order_at', 'unblocked_until',
    ];

    protected $casts = [
        'state_updated_at' => 'datetime',
        'last_order_at' => 'datetime',
        'unblocked_until' => 'datetime',
        'total_belanja' => 'decimal:2',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function getStateEnumAttribute(): UserState
    { return UserState::from($this->current_state); }

    public function getTierEnumAttribute(): TierLevel
    { return TierLevel::from($this->tier); }

    public function isBlocked(): bool
    { return $this->current_state === 'blocked'; }

    public function isInGracePeriod(): bool
    {
        if (! $this->unblocked_until) return false;
        return $this->unblocked_until->isFuture();
    }
}
