<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGift extends Model
{
    public const TYPE_COIN = 'koin';

    public const TYPE_VOUCHER = 'voucher';

    public const TYPE_GIFT_BOX = 'gift_box';

    protected $fillable = ['user_id', 'admin_id', 'type', 'coin_amount', 'voucher_id', 'note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
