<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuthLog extends Model
{
    use HasFactory;

    public const ACTION_LOGIN_SUCCESS = 'login_success';

    public const ACTION_LOGIN_FAILED = 'login_failed';

    public const ACTION_LOGOUT = 'logout';

    protected $fillable = [
        'user_id',
        'email_attempt',
        'action',
        'reason',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        string $emailAttempt,
        ?int $userId = null,
        ?string $reason = null,
        ?Request $request = null,
    ): self {
        $request ??= request();

        return static::create([
            'user_id' => $userId,
            'email_attempt' => $emailAttempt,
            'action' => $action,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
