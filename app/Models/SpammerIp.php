<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpammerIp extends Model
{
    protected $fillable = [
        'spammer_id', 'ip_address', 'device_fingerprint', 'attempt_count', 'last_attempt_at', 'expires_at',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function spammer(): BelongsTo
    {
        return $this->belongsTo(Spammer::class);
    }

    public function scopeActive($query)
    {
        return $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
