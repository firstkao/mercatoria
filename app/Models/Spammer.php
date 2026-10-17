<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SpammerReason;

class Spammer extends Model
{
    protected $fillable = [
        'email', 'no_hp', 'reason', 'status', 'unblock_count', 'admin_note',
    ];

    public function ips(): HasMany
    { return $this->hasMany(SpammerIp::class); }

    public function logs(): HasMany
    { return $this->hasMany(SpammerLog::class); }

    public function getReasonEnumAttribute(): SpammerReason
    { return SpammerReason::from($this->reason); }

    public function isBlocked(): bool
    { return $this->status === 'blocked'; }

    public function isWatchlist(): bool
    { return $this->status === 'watchlist'; }
}
