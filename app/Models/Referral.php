<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referee_id',
        'referee_registered_at', 'referee_first_paid_at', 'referee_first_order_completed_at',
        'referee_coin_given', 'referrer_coin_given',
    ];

    protected $casts = [
        'referee_registered_at' => 'datetime',
        'referee_first_paid_at' => 'datetime',
        'referee_first_order_completed_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    { return $this->belongsTo(User::class, 'referrer_id'); }

    public function referee(): BelongsTo
    { return $this->belongsTo(User::class, 'referee_id'); }
}
