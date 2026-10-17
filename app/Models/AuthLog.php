<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    protected $fillable = [
        'user_id', 'email_attempt', 'action', 'reason',
        'ip_address', 'user_agent',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public static function record(
        string $action,
        ?string $emailAttempt = null,
        ?string $reason = null,
        ?int $userId = null
    ): self {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'email_attempt' => $emailAttempt,
            'action' => $action,
            'reason' => $reason,
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
