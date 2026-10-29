<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpammerIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'device_fingerprint',
        'attempt_count',
        'last_attempt_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_count' => 'integer',
            'last_attempt_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Catat percobaan dari identitas ter-block (PRD 10.5): attempt_count +1, TTL 30 hari.
     */
    public static function recordAttempt(string $ip, ?string $userAgent = null): void
    {
        $row = static::firstOrCreate(
            ['ip_address' => $ip],
            ['device_fingerprint' => $userAgent ? substr(md5($userAgent), 0, 64) : null]
        );

        $row->increment('attempt_count');
        $row->update([
            'last_attempt_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }
}
