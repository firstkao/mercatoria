<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spammer extends Model
{
    use HasFactory;

    public const REASON_NO_PURCHASE_3X = 'no_purchase_3x';

    public const REASON_MANUAL = 'manual';

    public const REASON_AUTO_BAN = 'auto_ban';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_WATCHLIST = 'watchlist';

    protected $fillable = [
        'email',
        'no_hp',
        'reason',
        'status',
        'unblock_count',
        'banned_at',
    ];

    protected function casts(): array
    {
        return [
            'unblock_count' => 'integer',
            'banned_at' => 'datetime',
        ];
    }

    /**
     * Identitas (email / no HP) yang diblokir permanen dari register & login.
     */
    public static function blocksIdentity(?string $email, ?string $noHp): bool
    {
        return static::query()
            ->where('status', self::STATUS_BLOCKED)
            ->where(function ($query) use ($email, $noHp) {
                $query->where('email', $email);

                if ($noHp !== null) {
                    $query->orWhere('no_hp', $noHp);
                }
            })
            ->exists();
    }
}
