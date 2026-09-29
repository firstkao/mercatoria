<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'referrer_id',
    'referee_id',
    'referral_code',
    'status',
    'referrer_reward',
    'referee_reward',
    'rewarded_at',
])]
class Referral extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REWARDED = 'rewarded';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'rewarded_at' => 'datetime',
            'referrer_reward' => 'integer',
            'referee_reward' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}