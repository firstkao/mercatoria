<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpammerIp extends Model
{
    protected $fillable = [
        'spammer_id', 'ip_address', 'attempt_count', 'last_attempt_at',
    ];

    protected $casts = ['last_attempt_at' => 'datetime'];

    public function spammer(): BelongsTo
    { return $this->belongsTo(Spammer::class); }
}
