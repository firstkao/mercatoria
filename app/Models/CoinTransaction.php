<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    use HasFactory;

    /**
     * Tipe transaksi koin. `hangus_payment_failed` & `expired` dipakai di bagian (e).
     */
    public const TYPE_USED_AT_CHECKOUT = 'used_at_checkout';

    public const TYPE_HANGUS_PAYMENT_FAILED = 'hangus_payment_failed';

    public const TYPE_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
        'expired_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'expired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
