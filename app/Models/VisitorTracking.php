<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorTracking extends Model
{
    protected $fillable = [
        'session_id', 'referral_source', 'landing_page',
        'ip_address', 'user_agent', 'user_id', 'first_visit_at',
    ];

    protected $casts = ['first_visit_at' => 'datetime'];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public static function track(
        string $sessionId,
        ?string $referralSource,
        ?string $landingPage,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'referral_source' => $referralSource,
                'landing_page' => $landingPage,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'first_visit_at' => now(),
            ]
        );
    }
}
