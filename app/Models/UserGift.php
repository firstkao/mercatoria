<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGift extends Model
{
    protected $fillable = [
        'user_id', 'admin_id', 'type', 'coin_amount', 'voucher_id', 'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function isCoin(): bool
    {
        return $this->type === 'coin';
    }

    public function isVoucher(): bool
    {
        return $this->type === 'voucher';
    }

    public function isGiftBox(): bool
    {
        return $this->type === 'gift_box';
    }
}
