<?php

namespace App\Models;

use App\Enums\SpammerReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spammer extends Model
{
    protected $fillable = [
        'email', 'no_hp', 'reason', 'status', 'unblock_count', 'unblocked_until', 'admin_note',
    ];

    protected $casts = [
        'unblocked_until' => 'datetime',
    ];

    public function ips(): HasMany
    {
        return $this->hasMany(SpammerIp::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SpammerLog::class);
    }

    public function getReasonEnumAttribute(): SpammerReason
    {
        return SpammerReason::from($this->reason);
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isWatchlist(): bool
    {
        return $this->status === 'watchlist';
    }

    public function isInGracePeriod(): bool
    {
        return $this->isWatchlist() && $this->unblocked_until?->isFuture() === true;
    }
}
